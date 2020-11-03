<?php

namespace BM\BackupManagerBundle\Factory;

use BackupManager\Config\Config;
use Nyholm\Dsn\DsnParser;

/**
 * A factory class to resolve DSN
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConfigFactory
{
    /**
     * If a DSN is configured, then let it override other database storages.
     * @param array $config
     * @return Config
     */
    public static function createConfig(array $config)
    {
        foreach ($config as $key => $databaseConfig) {
            if (isset($databaseConfig['dsn'])) {
                $dsn = DsnParser::parseUrl($databaseConfig['dsn']);
                $config[$key]['type'] = $dsn->getScheme();
                $config[$key]['host'] = $dsn->getHost();
                $config[$key]['port'] = $dsn->getPort();
                $config[$key]['user'] = $dsn->getUser();
                $config[$key]['pass'] = $dsn->getPassword();
                if (null !== $path = $dsn->getPath()) {
                    $path = ltrim($path, '/');
                }

                $config[$key]['database'] = $path;
                unset($config[$key]['dsn']);
            }
        }

        return new Config($config);
    }
}
