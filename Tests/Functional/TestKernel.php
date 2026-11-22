<?php

namespace BM\BackupManagerBundle\Tests\Functional;

use BM\BackupManagerBundle\BMBackupManagerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * A minimal application kernel with this bundle, for Symfony 6.4 to 8.
 */
class TestKernel extends Kernel
{
    /** @var string[] */
    private $configFiles;

    /**
     * @param string[] $configFiles
     */
    public function __construct(array $configFiles = [])
    {
        $this->configFiles = $configFiles;
        parent::__construct('test_' . substr(md5(serialize($configFiles)), 0, 8), true);
    }

    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new BMBackupManagerBundle()];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => true,
                // Explicit on Symfony 6.4, which deprecates relying on their defaults.
                'http_method_override' => false,
                'handle_all_throwables' => true,
                'php_errors' => ['log' => true],
            ]);
        });
        foreach ($this->configFiles as $file) {
            $loader->load($file);
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new class () implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                foreach ($container->getDefinitions() as $id => $definition) {
                    if (strpos($id, 'backup_manager') === 0) {
                        $definition->setPublic(true);
                    }
                }
                foreach ($container->getAliases() as $id => $alias) {
                    if (strpos($id, 'backup_manager') === 0 || $id === 'BackupManager\\Manager') {
                        $alias->setPublic(true);
                    }
                }
            }
        });
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/bm-backup-manager-bundle/' . $this->environment . '/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/bm-backup-manager-bundle/' . $this->environment . '/log';
    }
}
