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
    protected function getContainerExtensions(): array
    {
        return array(
            new BMBackupManagerExtension()
        );
    }

    public function testReplacementOfConfig(): void
    {
        $storageConfig = ['local' => ['type' => 'Local', 'root' => '/foo']];
        $dbConfig = ['dev' => ['type' => 'mysql']];

        $this->load([
            'storage' => $storageConfig,
            'database' => $dbConfig,
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_storage', 0, $storageConfig);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_database', 0, $dbConfig);
    }

    /**
     * Make sure we can have multiple storage names with the same type
     */
    public function testCustomConfigNames(): void
    {
        $storageConfig = ['foobar' => ['type' => 'Ftp', 'host' => 'foo', 'password' => 'xx', 'username' => 'x']];
        $dbConfig = ['dev' => ['type' => 'mysql']];

        $this->load([
            'storage' => $storageConfig,
            'database' => $dbConfig,
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('backup_manager.config_storage', 0, $storageConfig);
    }

    public function testNewStorageTypesAreRegistered(): void
    {
        $this->load([
            'storage' => [
                's3' => ['type' => 'AsyncAwsS3', 'bucket' => 'b'],
                'dav' => ['type' => 'Webdav', 'baseUri' => 'https://dav.example'],
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('backup_manager.filesystems', 'add', [new \Symfony\Component\DependencyInjection\Reference('backup_manager.filesystems.async_awss3_filesystem')]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('backup_manager.filesystems', 'add', [new \Symfony\Component\DependencyInjection\Reference('backup_manager.filesystems.webdav_filesystem')]);
    }
}
