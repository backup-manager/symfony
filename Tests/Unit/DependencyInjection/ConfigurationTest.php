<?php

namespace BM\BackupManagerBundle\Tests\Unit\DependencyInjection;

use BM\BackupManagerBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testIgnoreTablesGeneratesErrorWhenNotUsingMySQL()
    {
        $this->assertConfigurationIsInvalid(array(
                [
                    'database'=>[
                        'dev'=>[
                            'type' => 'foo',
                            'ignoreTables'=>['xx', 'yy']
                        ],
                        'prod'=>[
                            'type' => 'mysql',
                        ],
                    ],
                ]
            ),
            'Keys "ignoreTables", "ssl" and "singleTransaction" are only valid on MySQL databases.'
        );
    }

    public function testIgnoreTablesDoesNothingWhenOmitted()
    {
        $this->assertConfigurationIsValid(array(
                [
                    'database'=>[
                        'dev'=>[
                            'type' => 'foo',
                        ],
                        'prod'=>[
                            'type' => 'mysql',
                        ],
                    ],
                ]
            )
        );
    }

    public function testSslGeneratesErrorWhenNotUsingMySQL()
    {
        $this->assertConfigurationIsInvalid(array(
                [
                    'database'=>[
                        'dev'=>[
                            'type' => 'foo',
                            'ssl' => true,
                        ],
                        'prod'=>[
                            'type' => 'mysql',
                        ],
                    ],
                ]
            ),
            'Keys "ignoreTables", "ssl" and "singleTransaction" are only valid on MySQL databases.'
        );
    }

    public function testSslOnValid()
    {
        $this->assertConfigurationIsValid(array(
                [
                    'database'=>[
                        'dev'=>[
                            'type' => 'foo',
                        ],
                        'test'=>[
                            'type' => 'bar',
                            'ssl' => false,
                        ],
                        'prod'=>[
                            'type' => 'mysql',
                            'ssl' => true,
                        ],
                    ],
                ]
            )
        );
    }

    public function testSingleTransactionGeneratesErrorWhenNotUsingMySQL()
    {
        $this->assertConfigurationIsInvalid(array(
                [
                    'database'=>[
                        'dev'=>[
                            'type' => 'foo',
                            'singleTransaction' => true,
                        ],
                        'prod'=>[
                            'type' => 'mysql',
                        ],
                    ],
                ]
            ),
            'Keys "ignoreTables", "ssl" and "singleTransaction" are only valid on MySQL databases.'
        );
    }

    public function testSingleTransactionOnValid()
    {
        $this->assertConfigurationIsValid(array(
                [
                    'database'=>[
                        'dev'=>[
                            'type' => 'foo',
                        ],
                        'test'=>[
                            'type' => 'bar',
                            'singleTransaction' => false,
                        ],
                        'prod'=>[
                            'type' => 'mysql',
                        ],
                    ],
                ]
            )
        );
    }
}
