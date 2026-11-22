<?php

namespace BM\BackupManagerBundle\Tests\Unit\DependencyInjection;

use BM\BackupManagerBundle\Factory\ConfigFactory;
use PHPUnit\Framework\TestCase;

class ConfigFactoryTest extends TestCase
{
    public function testDsnConfig(): void
    {
        $config = ConfigFactory::createConfig(['acme'=>['dsn'=> 'mysql://user:pass@server:3306/database']]);
        $this->assertEquals('mysql', $config->get('acme', 'type'));
        $this->assertEquals('server', $config->get('acme', 'host'));
        $this->assertEquals('3306', $config->get('acme', 'port'));
        $this->assertEquals('user', $config->get('acme', 'user'));
        $this->assertEquals('pass', $config->get('acme', 'pass'));
        $this->assertEquals('database', $config->get('acme', 'database'));
    }

    /**
     * User and password in a DSN must be url-encoded (RFC 3986); they are
     * decoded before use (#78).
     */
    public function testDsnCredentialsAreUrlDecoded(): void
    {
        $config = ConfigFactory::createConfig(['acme' => ['dsn' => 'mysql://us%40er:p%40ss%25w@server:3306/database?serverVersion=5.7']]);
        $this->assertSame('us@er', $config->get('acme', 'user'));
        $this->assertSame('p@ss%w', $config->get('acme', 'pass'));
        $this->assertSame('database', $config->get('acme', 'database'));
    }
}
