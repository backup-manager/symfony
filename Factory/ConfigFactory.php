<?php

namespace BM\BackupManagerBundle\Factory;

use BackupManager\Config\Config;
use Nyholm\DSN;

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
                $dsn = new DSN($databaseConfig['dsn']);
                $config[$key]['type'] = $dsn->getProtocol();
                $config[$key]['host'] = $dsn->getFirstHost();
                $config[$key]['port'] = $dsn->getFirstPort();
                $config[$key]['user'] = $dsn->getUsername();
                $config[$key]['pass'] = $dsn->getPassword();
                $config[$key]['database'] = $dsn->getDatabase();
                unset($config[$key]['dsn']);
            }
        }

        return new Config($config);
    }
}
