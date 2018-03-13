<?php

namespace BM\BackupManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bm_backup_manager');

        $rootNode
            ->children()
                ->variableNode('storage')
                    ->validate()
                        ->always(function ($storageConfig) {
                            foreach ($storageConfig as $name => $config) {
                                if (!isset($config['type'])) {
                                    throw new InvalidConfigurationException(sprintf('You must define a "type" for storage "%s"', $name));
                                }

                                switch ($config['type']) {
                                    case 'Local':
                                        $this->validateAuthenticationType(['root'], $config, 'Local');
                                        break;
                                    case 'AwsS3':
                                        $this->validateAuthenticationType(['key', 'secret', 'region', 'version', 'bucket', 'root'], $config, 'AwsS3');
                                        break;
                                    case 'Rackspace':
                                        $this->validateAuthenticationType(['username', 'password', 'container'], $config, 'Rackspace');
                                        break;
                                    case 'Dropbox':
                                        $this->validateAuthenticationType(['token', 'key', 'secret', 'app', 'root'], $config, 'Dropbox');
                                        break;
                                    case 'DropboxV2':
                                        $this->validateAuthenticationType(['token', 'root'], $config, 'DropboxV2');
                                        break;
                                    case 'Ftp':
                                        $this->validateAuthenticationType(['host', 'username', 'password', 'root', 'port', 'passive', 'ssl', 'timeout'], $config, 'Ftp');
                                        break;
                                    case 'Sftp':
                                        $this->validateAuthenticationType(['host', 'username', 'password', 'root', 'port', 'timeout', 'privateKey'], $config, 'Sftp');
                                        break;
                                    default:
                                        $validTypes = ['Local', 'AwsS3', 'Rackspace', 'Dropbox', 'DropboxV2', 'Ftp', 'Sftp'];
                                        throw new InvalidConfigurationException(sprintf('Type must be one of "%s", got "%s"', implode(', ', $validTypes), $config['type']));
                                }
                            }

                            return $storageConfig;
                        })
                    ->end()

                ->end() // End storage

                ->arrayNode('database')
                    ->validate()
                        ->ifTrue(function ($databases) {
                            $valid = true;
                            foreach ($databases as $name => $d) {
                                if (isset($d['dsn'])) {
                                    // We cannot resolve the DSN now. It might be a environment variable.
                                    continue;
                                }
                                if (empty($d['type'])) {
                                    throw new InvalidConfigurationException(sprintf('You must define a "type" or "dsn" for database "%s"', $name));
                                }
                                if ($d['type'] !== 'mysql') {
                                    // If not "mysql" we have to make sure these parameter are set to default
                                    $valid = $valid && empty($d['ignoreTables']) && empty($d['ssl']) && empty($d['singleTransaction']);
                                }
                            }

                            return !$valid;
                        })
                        ->thenInvalid('Keys "ignoreTables", "ssl" and "singleTransaction" are only valid on MySQL databases.')
                    ->end()
                    ->validate()
                        ->always(function ($databases) {
                            foreach ($databases as &$database) {
                                if (empty($database['ignoreTables'])) {
                                    unset($database['ignoreTables']);
                                }
                            }
                            return $databases;
                        })
                    ->end()
                    ->prototype('array')
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
                            ->arrayNode('ignoreTables')
                                 ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('output_file_prefix')->info('Use as a prefix for default backup filename')->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Validate that the configuration fragment has the specified keys and none other.
     *
     * @param array  $expected Fields that must exist
     * @param array  $actual   Actual configuration hashmap
     * @param string $typeName Name of storage type for error messages
     *
     * @throws InvalidConfigurationException If $actual does not have exactly the keys specified in $expected (plus 'type')
     */
    private function validateAuthenticationType(array $expected, array $actual, $typeName)
    {
        unset($actual['type']);
        $actual = array_keys($actual);

        if (empty(array_diff($actual, $expected))) {
            return;
        }

        throw new InvalidConfigurationException(sprintf(
            'Storage type "%s" has valid keys "%s" but got "%s"',
            $typeName,
            implode(', ', $expected),
            implode(', ', $actual)
        ));
    }
}
