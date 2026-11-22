<?php

namespace BM\BackupManagerBundle\Tests\Functional;

use BackupManager\Filesystems\FilesystemAdapterMissing;
use BackupManager\Manager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class BundleInitializationTest extends TestCase
{
    /** @var TestKernel|null */
    private $kernel;

    protected function tearDown(): void
    {
        if ($this->kernel !== null) {
            $this->kernel->shutdown();
            (new Filesystem())->remove($this->kernel->getCacheDir());
        }
    }

    private function boot(array $configFiles = []): TestKernel
    {
        $this->kernel = new TestKernel($configFiles);
        $this->kernel->boot();

        return $this->kernel;
    }

    public function testInitBundle(): void
    {
        $container = $this->boot([__DIR__ . '/config/minimal.yml'])->getContainer();

        $this->assertInstanceOf(Manager::class, $container->get('backup_manager'));
        $this->assertInstanceOf(Manager::class, $container->get(Manager::class));
    }

    public function testNoConfig(): void
    {
        $this->assertInstanceOf(Manager::class, $this->boot()->getContainer()->get('backup_manager'));
    }

    public function testConfigIsLoaded(): void
    {
        $container = $this->boot([__DIR__ . '/config/minimal.yml'])->getContainer();

        $this->assertSame('/path/to/working/directory', $container->get('backup_manager.filesystems')->getConfig('local', 'root'));
        $this->assertSame('mysql', $container->get('backup_manager.config_database')->get('development', 'type'));
    }

    /**
     * Adapter packages are checked when a storage is used, by the core
     * library, for whichever Flysystem major is installed.
     */
    public function testAStorageWithoutItsAdapterPackageFailsWhenUsed(): void
    {
        $container = $this->boot([__DIR__ . '/config/webdav.yml'])->getContainer();

        $this->expectException(FilesystemAdapterMissing::class);
        $container->get('backup_manager.filesystems')->get('webdav');
    }

    public function testTheCommandsAreRegistered(): void
    {
        $application = new Application($this->boot([__DIR__ . '/config/minimal.yml']));

        $this->assertTrue($application->has('backup-manager:backup'));
        $this->assertTrue($application->has('backup-manager:restore'));
        $tester = new CommandTester($application->find('backup-manager:backup'));
        $this->assertStringContainsString('database', $application->find('backup-manager:backup')->getDefinition()->getArgument('database')->getName());
        $this->assertNotNull($tester);
    }
}
