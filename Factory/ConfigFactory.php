<?php

declare(strict_types=1);

namespace BM\BackupManagerBundle\Factory;

use BackupManager\Config\Config;
use Nyholm\Dsn\DsnParser;

/**
 * Builds the database Config, resolving DSNs.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ConfigFactory
{
    /**
     * A configured DSN overrides the database's other connection values.
     * User and password in a DSN must be url-encoded (RFC 3986).
     *
     * @param array<string, array<string, mixed>> $config
     */
    public static function createConfig(array $config): Config
    {
        foreach ($config as $key => $databaseConfig) {
            if (!isset($databaseConfig['dsn']) || !is_string($databaseConfig['dsn'])) {
                continue;
            }
            $dsn = DsnParser::parseUrl($databaseConfig['dsn']);
            $path = $dsn->getPath();
            $config[$key]['type'] = $dsn->getScheme();
            $config[$key]['host'] = $dsn->getHost();
            $config[$key]['port'] = $dsn->getPort();
            $config[$key]['user'] = $dsn->getUser();
            $config[$key]['pass'] = $dsn->getPassword();
            $config[$key]['database'] = $path === null ? null : ltrim($path, '/');
            unset($config[$key]['dsn']);
        }

        return new Config($config);
    }
}
