<?php

namespace BM\BackupManagerBundle\Tests\Unit\DependencyInjection;

use BM\BackupManagerBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function testIgnoreTablesGeneratesErrorWhenNotUsingMySQL(): void
    {
        $this->assertConfigurationIsInvalid(
            array(
                [
                    'database' => [
                        'dev' => [
                            'type' => 'foo',
                            'ignoreTables' => ['xx', 'yy']
                        ],
                        'prod' => [
                            'type' => 'mysql',
                        ],
                    ],
                ]
            ),
            'only valid on MySQL databases; database "dev" has "ignoreTables"'
        );
    }

    public function testIgnoreTablesDoesNothingWhenOmitted(): void
    {
        $this->assertConfigurationIsValid(
            array(
                [
                    'database' => [
                        'dev' => [
                            'type' => 'foo',
                        ],
                        'prod' => [
                            'type' => 'mysql',
                        ],
                    ],
                ]
            )
        );
    }

    public function testSslGeneratesErrorWhenNotUsingMySQL(): void
    {
        $this->assertConfigurationIsInvalid(
            array(
                [
                    'database' => [
                        'dev' => [
                            'type' => 'foo',
                            'ssl' => true,
                        ],
                        'prod' => [
                            'type' => 'mysql',
                        ],
                    ],
                ]
            ),
            'only valid on MySQL databases; database "dev" has "ssl"'
        );
    }

    public function testSslOnValid(): void
    {
        $this->assertConfigurationIsValid(
            array(
                [
                    'database' => [
                        'dev' => [
                            'type' => 'foo',
                        ],
                        'test' => [
                            'type' => 'bar',
                            'ssl' => false,
                        ],
                        'prod' => [
                            'type' => 'mysql',
                            'ssl' => true,
                        ],
                    ],
                ]
            )
        );
    }

    public function testSingleTransactionGeneratesErrorWhenNotUsingMySQL(): void
    {
        $this->assertConfigurationIsInvalid(
            array(
                [
                    'database' => [
                        'dev' => [
                            'type' => 'foo',
                            'singleTransaction' => true,
                        ],
                        'prod' => [
                            'type' => 'mysql',
                        ],
                    ],
                ]
            ),
            'only valid on MySQL databases; database "dev" has "singleTransaction"'
        );
    }

    public function testSingleTransactionOnValid(): void
    {
        $this->assertConfigurationIsValid(
            array(
                [
                    'database' => [
                        'dev' => [
                            'type' => 'foo',
                        ],
                        'test' => [
                            'type' => 'bar',
                            'singleTransaction' => false,
                        ],
                        'prod' => [
                            'type' => 'mysql',
                        ],
                    ],
                ]
            )
        );
    }

    public function testNoType(): void
    {
        $this->assertConfigurationIsInvalid(
            array(
                [
                    'database' => [
                        'dev' => [

                        ],
                    ],
                ]
            ),
            'You must define a "type" or "dsn" for database "dev"'
        );
    }

    /**
     *
     * @dataProvider validStorageTypes
     */
    #[DataProvider('validStorageTypes')]
    public function testStorageTypes(string $type): void
    {
        $this->assertConfigurationIsValid(
            array(
                [
                    'database' => [
                        'dev' => [
                            'type' => 'mysql'
                        ],
                    ],
                    'storage' => [
                        'foo' => [
                            'type' => $type,
                        ]
                    ]
                ]
            )
        );
    }

    public static function validStorageTypes(): array
    {
        return [
            ['Local'], ['AwsS3'], ['AsyncAwsS3'], ['Dropbox'], ['Ftp'], ['Sftp'], ['Gcs'], ['Webdav'],
        ];
    }

    /**
     * @dataProvider fullStorageConfigs
     */
    #[DataProvider('fullStorageConfigs')]
    public function testEveryKeyTheCoreLibraryReadsIsAccepted(array $storage): void
    {
        $this->assertConfigurationIsValid([['storage' => ['s' => $storage]]]);
    }

    public static function fullStorageConfigs(): array
    {
        return [
            'Local' => [['type' => 'Local', 'root' => '/backups']],
            'AwsS3' => [['type' => 'AwsS3', 'key' => 'k', 'secret' => 's', 'region' => 'r', 'version' => 'latest', 'bucket' => 'b', 'root' => '', 'endpoint' => 'https://s3.example', 'use_path_style_endpoint' => true]],
            'AsyncAwsS3' => [['type' => 'AsyncAwsS3', 'key' => 'k', 'secret' => 's', 'region' => 'r', 'bucket' => 'b', 'root' => '', 'endpoint' => 'https://s3.example', 'use_path_style_endpoint' => true]],
            'Dropbox' => [['type' => 'Dropbox', 'token' => 't', 'root' => '']],
            'Ftp' => [['type' => 'Ftp', 'host' => 'h', 'username' => 'u', 'password' => 'p', 'root' => '', 'port' => 21, 'passive' => true, 'ssl' => true, 'timeout' => 30, 'utf8' => true, 'transferMode' => 2, 'systemType' => 'unix', 'ignorePassiveAddress' => true, 'timestampsOnUnixListingsEnabled' => true, 'recurseManually' => true]],
            'Sftp' => [['type' => 'Sftp', 'host' => 'h', 'username' => 'u', 'password' => 'p', 'root' => '', 'port' => 22, 'timeout' => 10, 'privateKey' => 'k', 'passphrase' => 'x', 'useAgent' => true, 'maxTries' => 3, 'hostFingerprint' => 'f']],
            'Gcs (core sample)' => [['type' => 'Gcs', 'keyFilePath' => '', 'projectId' => '', 'bucket' => '', 'root' => '']],
            'Gcs' => [['type' => 'Gcs', 'project' => 'p', 'keyFilePath' => '', 'bucket' => 'b', 'prefix' => 'x']],
            'Webdav' => [['type' => 'Webdav', 'baseUri' => 'https://dav.example', 'userName' => 'u', 'password' => 'p', 'prefix' => '', 'authType' => 1, 'proxy' => 'http://proxy', 'encoding' => 1]],
        ];
    }

    public function testPdoMysqlMayUseTheMysqlOnlyOptions(): void
    {
        $this->assertConfigurationIsValid([['database' => ['dev' => ['type' => 'pdo_mysql', 'singleTransaction' => true, 'socket' => '/s']]]]);
    }

    public function testTypesRemovedFromTheCoreLibraryAreRejected(): void
    {
        foreach (['B2', 'Rackspace', 'DropboxV2'] as $type) {
            $this->assertConfigurationIsInvalid([['storage' => ['old' => ['type' => $type]]]], 'Type must be one of');
        }
    }

    public function testAnUnknownStorageKeyIsNamedInTheError(): void
    {
        $this->assertConfigurationIsInvalid(
            [['storage' => ['box' => ['type' => 'Dropbox', 'token' => 't', 'app' => 'x']]]],
            'Storage type "Dropbox" received invalid key "app"'
        );
    }

    public function testMysqlOnlyOptionsAreValidOnMysql(): void
    {
        $this->assertConfigurationIsValid([[
            'database' => ['dev' => [
                'type' => 'mysql',
                'onlyTables' => ['users'],
                'columnStatistics' => false,
                'socket' => '/run/mysqld/mysqld.sock',
            ]],
        ]]);
    }

    public function testMysqlOnlyOptionsGenerateAnErrorOnPostgresql(): void
    {
        foreach ([['onlyTables' => ['users']], ['columnStatistics' => false], ['socket' => '/tmp/s']] as $option) {
            $this->assertConfigurationIsInvalid(
                [['database' => ['dev' => ['type' => 'postgresql'] + $option]]],
                'are only valid on MySQL databases; database "dev" has "' . array_key_first($option) . '"'
            );
        }
    }

    public function testAwsS3AcceptsPathStyleEndpoints(): void
    {
        $this->assertConfigurationIsValid([[
            'storage' => ['spaces' => [
                'type' => 'AwsS3',
                'endpoint' => 'https://ams3.digitaloceanspaces.com',
                'use_path_style_endpoint' => true,
            ]],
        ]]);
    }
}
