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

    /**
     * @test
     */
    public function testReplacementOfConfig()
    {
        $storageConfig = ['local'=>['type'=>'local']];
        $dbConfig = ['dev'=>['type'=>'mysql']];

        $this->load(
            [
                'storage' => $storageConfig,
                'database' => $dbConfig,
            ]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_storage', 0, $storageConfig);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_database', 0, $dbConfig);
    }
}
