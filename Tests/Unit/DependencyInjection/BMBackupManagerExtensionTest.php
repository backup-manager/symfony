<?php

namespace BM\BackupManagerBundle\Tests\Unit\DependencyInjection;

use BM\BackupManagerBundle\DependencyInjection\BMBackupManagerExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * Make sure values are copied properly
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BMBackupManagerExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new BMBackupManagerExtension()
        );
    }

    public function testReplacementOfConfig()
    {
        $storageConfig = ['local'=>['type'=>'Local', 'root'=>'/foo']];
        $dbConfig = ['dev'=>['type'=>'mysql']];

        $this->load([
            'storage' => $storageConfig,
            'database' => $dbConfig,
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_storage', 0, $storageConfig);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_database', 0, $dbConfig);
    }


    public function testDsn()
    {
        $storageConfig = ['local'=>['type'=>'Local', 'root'=>'/foo']];
        $dbConfig = ['dev'=>[
            'type'=>'mysql',
            'host'=>'host.com',
            'port'=>'3306',
            'user'=>'user',
            'pass'=>'pass',
            'database'=>'db',
            // The DSN should override them all.
            'dsn'=>'pgsql://root:root_pass@127.0.0.1:5432/test_db',
        ]];

        $this->load([
            'storage' => $storageConfig,
            'database' => $dbConfig,
        ]);

        $parsedConfig = ['dev'=>[
            'type'=>'pgsql',
            'host'=>'127.0.0.1',
            'port'=>'5432',
            'user'=>'root',
            'pass'=>'root_pass',
            'database'=>'test_db',
        ]];

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_storage', 0, $storageConfig);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_database', 0, $parsedConfig);
    }

    /**
     * Make sure we can have multiple storage names with the same type
     */
    public function testCustomConfigNames()
    {
        $storageConfig = ['foobar'=>['type'=>'Ftp', 'host' => 'foo', 'password' =>'xx', 'username'=>'x']];
        $dbConfig = ['dev'=>['type'=>'mysql']];

        $this->load([
            'storage' => $storageConfig,
            'database' => $dbConfig,
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_storage', 0, $storageConfig);
    }
}
