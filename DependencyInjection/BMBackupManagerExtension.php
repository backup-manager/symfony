<?php

namespace BM\BackupManagerBundle\DependencyInjection;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Dropbox\DropboxAdapter;
use Srmklive\Dropbox\Adapter\DropboxAdapter as Dropbox2Adapter;
use League\Flysystem\Rackspace\RackspaceAdapter;
use League\Flysystem\Sftp\SftpAdapter;
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
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $config['storage'] = isset($config['storage']) ? $config['storage'] : [];
        $config['database'] = isset($config['database']) ? $config['database'] : [];
        $this->validateStorage($config['storage']);

        $managerIdMap = [
            'Local' => 'backup_manager.filesystems.local_filesystem',
            'AwsS3' => 'backup_manager.filesystems.awss3_filesystem',
            'Rackspace' => 'backup_manager.filesystems.rackspace_filesystem',
            'Dropbox' => 'backup_manager.filesystems.dropbox_filesystem',
            'DropboxV2' => 'backup_manager.filesystems.dropbox_v2_filesystem',
            'Ftp' => 'backup_manager.filesystems.ftp_filesystem',
            'Sftp' => 'backup_manager.filesystems.sftp_filesystem',
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

    /**
     * We want to make sure the correct dependencies are installed for a storage.
     * @param array $config
     */
    private function validateStorage(array $config)
    {
        $requirements = [
            'Local' => ['package'=>'league/flysystem:^1.0', 'test'=>Local::class],
            'AwsS3' => ['package'=>'league/flysystem-aws-s3-v3:^1.0', 'test'=>AwsS3Adapter::class],
            'Rackspace' => ['package'=>'league/flysystem-rackspace:^1.0', 'test'=>RackspaceAdapter::class],
            'Dropbox' => ['package'=>'league/flysystem-dropbox:^1.0', 'test'=>DropboxAdapter::class],
            'DropboxV2' => ['package'=>'srmklive/flysystem-dropbox-v2:^1.0', 'test'=>Dropbox2Adapter::class],
            'Ftp' => ['package'=>'league/flysystem:^1.0', 'test'=>Ftp::class],
            'Sftp' => ['package'=>'league/flysystem-sftp:^1.0', 'test'=>SftpAdapter::class],
        ];

        foreach ($config as $key => $storageConfig) {
            $type = $storageConfig['type'];
            if (!class_exists($requirements[$type]['test'])) {
                throw new \LogicException(sprintf('To use the configuration key "%s" in "bm_backup_manager.stroage.%s.type" you need to install "%s"', $type, $key, $requirements[$type]['package']));
            }
        }
    }
}
