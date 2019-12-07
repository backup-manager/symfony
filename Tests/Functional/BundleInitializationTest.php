<?php

namespace BM\BackupManagerBundle\Tests\Functional;

use BackupManager\Manager;
use BM\BackupManagerBundle\BMBackupManagerBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return BMBackupManagerBundle::class;
    }

    protected function setUp(): void
    {
        $this->addCompilerPass(new PublicServicePass('|backup_manager.*|'));
        $this->addCompilerPass(new PublicServicePass('|backup_manager|'));
    }


    public function testInitBundle()
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config/minimal.yml');

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('backup_manager'));
        $service = $container->get('backup_manager');
        $this->assertInstanceOf(Manager::class, $service);
    }

    public function testNoConfig()
    {
        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('backup_manager'));
        $service = $container->get('backup_manager');
        $this->assertInstanceOf(Manager::class, $service);
    }

    public function testNoDependencies()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException(\LogicException::class);
        } else {
            // Legacy
            $this->setExpectedException(\LogicException::class);
        }

        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config/rackspace.yml');

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();
    }
}
