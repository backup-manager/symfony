<?php

namespace BM\BackupManagerBundle\Tests\Unit\DependencyInjection;

use BM\BackupManagerBundle\Factory\ConfigFactory;
use PHPUnit\Framework\TestCase;

class ConfigFactoryTest extends TestCase
{
    public function testDsnConfig()
    {
        $config = ConfigFactory::createConfig(['acme'=>['dsn'=> 'mysql://user:pass@server:3306/database']]);
        $this->assertEquals('mysql', $config->get('acme', 'type'));
        $this->assertEquals('server', $config->get('acme', 'host'));
        $this->assertEquals('3306', $config->get('acme', 'port'));
        $this->assertEquals('user', $config->get('acme', 'user'));
        $this->assertEquals('pass', $config->get('acme', 'pass'));
        $this->assertEquals('database', $config->get('acme', 'database'));
    }
}
