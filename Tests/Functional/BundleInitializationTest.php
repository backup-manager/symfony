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

    public function testTheBackupCommandRunsABackup(): void
    {
        $application = $this->applicationWithManager($manager = $this->createStub(Manager::class));
        $backup = $this->createMock(\BackupManager\Procedures\BackupProcedure::class);
        $manager->method('makeBackup')->willReturn($backup);
        $backup->expects($this->once())->method('run')->with(
            'development',
            $this->callback(function (array $destinations): bool {
                return count($destinations) === 1
                    && $destinations[0]->destinationFilesystem() === 'local'
                    && $destinations[0]->destinationPath() === 'nightly.sql';
            }),
            'gzip'
        );

        $tester = new CommandTester($application->find('backup-manager:backup'));
        $exitCode = $tester->execute(['database' => 'development', 'destinations' => ['local'], '--compression' => 'gzip', '--filename' => 'nightly.sql']);

        $this->assertSame(0, $exitCode);
    }

    public function testCompressionWithoutAValueMeansNone(): void
    {
        $application = $this->applicationWithManager($manager = $this->createStub(Manager::class));
        $backup = $this->createMock(\BackupManager\Procedures\BackupProcedure::class);
        $manager->method('makeBackup')->willReturn($backup);
        $backup->expects($this->once())->method('run')->with('development', $this->anything(), 'null');

        $tester = new CommandTester($application->find('backup-manager:backup'));
        $this->assertSame(0, $tester->execute(['database' => 'development', 'destinations' => ['local'], '--compression' => null]));
    }

    public function testTheRestoreCommandRunsARestore(): void
    {
        $application = $this->applicationWithManager($manager = $this->createStub(Manager::class));
        $restore = $this->createMock(\BackupManager\Procedures\RestoreProcedure::class);
        $manager->method('makeRestore')->willReturn($restore);
        $restore->expects($this->once())->method('run')->with('local', 'nightly.sql.gz', 'development', 'gzip');

        $tester = new CommandTester($application->find('backup-manager:restore'));
        $exitCode = $tester->execute(['database' => 'development', 'destination' => 'local', 'file_path' => 'nightly.sql.gz', '--compression' => 'gzip']);

        $this->assertSame(0, $exitCode);
    }

    /**
     * @param Manager $manager a test double replacing the bundle's manager
     */
    private function applicationWithManager($manager): Application
    {
        $kernel = $this->boot([__DIR__ . '/config/minimal.yml']);
        $kernel->getContainer()->set('backup_manager', $manager);

        return new Application($kernel);
    }
}
