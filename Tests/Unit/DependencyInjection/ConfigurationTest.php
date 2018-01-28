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
            'Key "ignoreTables" is only valid on MySQL databases.'
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
}
