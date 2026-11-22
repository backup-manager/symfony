<?php

declare(strict_types=1);

namespace BM\BackupManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class Configuration implements ConfigurationInterface
{
    /**
     * Keys each storage type accepts besides "type": the keys the core
     * filesystem factories read, including those they pass on to the adapter
     * (FtpConnectionOptions, SftpConnectionProvider, Sabre\DAV\Client).
     */
    private const STORAGE_KEYS = [
        'Local' => ['root'],
        'AwsS3' => ['key', 'secret', 'region', 'version', 'bucket', 'root', 'endpoint', 'use_path_style_endpoint'],
        'AsyncAwsS3' => ['key', 'secret', 'region', 'bucket', 'root', 'endpoint', 'use_path_style_endpoint'],
        'Dropbox' => ['token', 'root'],
        'Ftp' => [
            'host', 'username', 'password', 'root', 'port', 'passive', 'ssl', 'timeout', 'utf8', 'transferMode',
            'systemType', 'ignorePassiveAddress', 'timestampsOnUnixListingsEnabled', 'recurseManually',
        ],
        'Sftp' => [
            'host', 'username', 'password', 'root', 'port', 'timeout', 'privateKey', 'passphrase', 'useAgent',
            'maxTries', 'hostFingerprint',
        ],
        'Gcs' => ['project', 'projectId', 'keyFilePath', 'bucket', 'prefix', 'root'],
        'Webdav' => ['baseUri', 'userName', 'password', 'prefix', 'authType', 'proxy', 'encoding'],
    ];

    /**
     * Database options the core library reads for MySQL only.
     */
    private const MYSQL_ONLY_KEYS = ['ignoreTables', 'onlyTables', 'ssl', 'singleTransaction', 'columnStatistics', 'socket'];

    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('bm_backup_manager');

        $treeBuilder->getRootNode()
            ->children()
                ->variableNode('storage')
                    ->validate()
                        ->always(static function (mixed $storageConfig): mixed {
                            foreach ((array) $storageConfig as $name => $config) {
                                self::validateStorage((string) $name, (array) $config);
                            }

                            return $storageConfig;
                        })
                    ->end()
                ->end()
                ->arrayNode('database')
                    ->validate()
                        ->always(static function (array $databases): array {
                            foreach ($databases as $name => $database) {
                                self::validateDatabase((string) $name, $database);
                            }

                            return array_map(static function (array $database): array {
                                foreach (['ignoreTables', 'onlyTables'] as $key) {
                                    if (empty($database[$key])) {
                                        unset($database[$key]);
                                    }
                                }

                                return $database;
                            }, $databases);
                        })
                    ->end()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('type')->end()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('user')->end()
                            ->scalarNode('pass')->end()
                            ->scalarNode('database')->end()
                            ->scalarNode('dsn')->end()
                            ->booleanNode('singleTransaction')->end()
                            ->booleanNode('ssl')->end()
                            ->scalarNode('extraParams')->end()
                            ->arrayNode('ignoreTables')->scalarPrototype()->end()->end()
                            ->arrayNode('onlyTables')->scalarPrototype()->end()->end()
                            ->booleanNode('columnStatistics')->end()
                            ->scalarNode('socket')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('output_file_prefix')->info('Use as a prefix for default backup filename')->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @param array<mixed> $config
     */
    private static function validateStorage(string $name, array $config): void
    {
        if (!isset($config['type'])) {
            throw new InvalidConfigurationException(sprintf('You must define a "type" for storage "%s"', $name));
        }
        $type = $config['type'];
        if (!is_string($type) || !isset(self::STORAGE_KEYS[$type])) {
            throw new InvalidConfigurationException(sprintf(
                'Type must be one of "%s", got "%s"',
                implode(', ', array_keys(self::STORAGE_KEYS)),
                is_scalar($type) ? (string) $type : get_debug_type($type)
            ));
        }

        unset($config['type']);
        $unknown = array_diff(array_map('strval', array_keys($config)), self::STORAGE_KEYS[$type]);
        if ($unknown !== []) {
            throw new InvalidConfigurationException(sprintf(
                'Storage type "%s" received invalid key "%s". Please choose one of "%s".',
                $type,
                implode(', ', $unknown),
                implode(', ', self::STORAGE_KEYS[$type])
            ));
        }
    }

    /**
     * @param array<string, mixed> $database
     */
    private static function validateDatabase(string $name, array $database): void
    {
        if (isset($database['dsn'])) {
            // A DSN cannot be resolved yet; it may be an environment variable.
            return;
        }
        if (empty($database['type'])) {
            throw new InvalidConfigurationException(sprintf('You must define a "type" or "dsn" for database "%s"', $name));
        }
        if (in_array(strtolower((string) $database['type']), ['mysql', 'pdo_mysql'], true)) {
            return;
        }
        foreach (self::MYSQL_ONLY_KEYS as $key) {
            // columnStatistics is rejected when set at all; false is its only useful value.
            $isSet = $key === 'columnStatistics' ? isset($database[$key]) : !empty($database[$key]);
            if ($isSet) {
                throw new InvalidConfigurationException(sprintf(
                    'Keys "%s" are only valid on MySQL databases; database "%s" has "%s".',
                    implode('", "', self::MYSQL_ONLY_KEYS),
                    $name,
                    $key
                ));
            }
        }
    }
}
