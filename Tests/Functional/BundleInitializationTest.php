<?php

namespace BM\BackupManagerBundle\Tests\Functional;

use BackupManager\Manager;
use BM\BackupManagerBundle\BMBackupManagerBundle;
use Nyholm\BundleTest\BaseBundleTestCase;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return BMBackupManagerBundle::class;
    }

    public function testInitBundle()
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config/minimal.yml');
        $kernel->addConfigFile(__DIR__.'/config/public_services.yml');

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('test.backup_manager'));
        $service = $container->get('test.backup_manager');
        $this->assertInstanceOf(Manager::class, $service);
    }

    public function testNoDependencies()
    {
        $this->expectException(\LogicException::class);

        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config/rackspace.yml');
        $kernel->addConfigFile(__DIR__.'/config/public_services.yml');

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();
    }
}