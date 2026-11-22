<?php

declare(strict_types=1);

namespace BM\BackupManagerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class BMBackupManagerExtension extends Extension
{
    /**
     * Service id of the core filesystem factory for each storage type.
     */
    private const FILESYSTEMS = [
        'Local' => 'backup_manager.filesystems.local_filesystem',
        'AwsS3' => 'backup_manager.filesystems.awss3_filesystem',
        'AsyncAwsS3' => 'backup_manager.filesystems.async_awss3_filesystem',
        'Dropbox' => 'backup_manager.filesystems.dropbox_filesystem',
        'Ftp' => 'backup_manager.filesystems.ftp_filesystem',
        'Sftp' => 'backup_manager.filesystems.sftp_filesystem',
        'Gcs' => 'backup_manager.filesystems.gcs_filesystem',
        'Webdav' => 'backup_manager.filesystems.webdav_filesystem',
    ];

    /**
     * @param array<array<mixed>> $configs
     */
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var array{storage?: array<string, array<string, mixed>>, database?: array<string, array<string, mixed>>, output_file_prefix?: string} $config */
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $storage = $config['storage'] ?? [];
        $database = $config['database'] ?? [];

        $filesystems = $container->getDefinition('backup_manager.filesystems');
        $types = array_unique(array_map(static fn (array $storageConfig): string => (string) $storageConfig['type'], $storage));
        foreach ($types as $type) {
            $filesystems->addMethodCall('add', [new Reference(self::FILESYSTEMS[$type])]);
        }

        $container->getDefinition('backup_manager.config_storage')->replaceArgument(0, $storage);
        $container->getDefinition('backup_manager.config_database')->replaceArgument(0, $database);

        if (isset($config['output_file_prefix'])) {
            $container->getDefinition('backup_manager.command.backup')->replaceArgument(1, $config['output_file_prefix']);
        }
    }
}
