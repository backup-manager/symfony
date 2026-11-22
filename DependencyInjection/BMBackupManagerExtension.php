<?php

namespace BM\BackupManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class BMBackupManagerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $config['storage'] = isset($config['storage']) ? $config['storage'] : [];
        $config['database'] = isset($config['database']) ? $config['database'] : [];

        $managerIdMap = [
            'Local' => 'backup_manager.filesystems.local_filesystem',
            'AwsS3' => 'backup_manager.filesystems.awss3_filesystem',
            'AsyncAwsS3' => 'backup_manager.filesystems.async_awss3_filesystem',
            'B2' => 'backup_manager.filesystems.b2_filesystem',
            'Rackspace' => 'backup_manager.filesystems.rackspace_filesystem',
            'Dropbox' => 'backup_manager.filesystems.dropbox_filesystem',
            'DropboxV2' => 'backup_manager.filesystems.dropbox_v2_filesystem',
            'Ftp' => 'backup_manager.filesystems.ftp_filesystem',
            'Sftp' => 'backup_manager.filesystems.sftp_filesystem',
            'Gcs' => 'backup_manager.filesystems.gcs_filesystem',
            'Webdav' => 'backup_manager.filesystems.webdav_filesystem',
        ];

        $filesystemDef = $container->getDefinition('backup_manager.filesystems');
        foreach ($config['storage'] as $storageKey => $storageConfig) {
            $filesystemDef->addMethodCall('add', [new Reference($managerIdMap[$storageConfig['type']])]);
        }

        $container->getDefinition('backup_manager.config_storage')
            ->replaceArgument(0, $config['storage']);

        $container->getDefinition('backup_manager.config_database')
            ->replaceArgument(0, $config['database']);

        if (isset($config['output_file_prefix'])) {
            $container->getDefinition('backup_manager.command.backup')
                ->replaceArgument(1, $config['output_file_prefix']);
        }
    }
}
