BackupManagerBundle
===================

[![Tests](https://github.com/backup-manager/symfony/actions/workflows/tests.yml/badge.svg)](https://github.com/backup-manager/symfony/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/backup-manager/symfony)](https://packagist.org/packages/backup-manager/symfony)
[![PHP Version](https://img.shields.io/packagist/dependency-v/backup-manager/symfony/php)](https://packagist.org/packages/backup-manager/symfony)
[![Symfony](https://img.shields.io/badge/Symfony-6.4%20%7C%207.4%20%7C%208-000000?logo=symfony&logoColor=white)](#requirements)
[![Downloads](https://img.shields.io/packagist/dt/backup-manager/symfony)](https://packagist.org/packages/backup-manager/symfony/stats)
[![License](https://img.shields.io/packagist/l/backup-manager/symfony)](LICENSE)

Back up and restore MySQL and PostgreSQL databases from your Symfony application to S3, Google Cloud Storage, Dropbox, FTP, SFTP, WebDAV or local disk.

This package pulls in the framework agnostic [Backup Manager](https://github.com/backup-manager/backup-manager) and provides seamless integration with **Symfony**.

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require backup-manager/symfony
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2 (with Flex): Enable the Bundle
-------------------------------------

You do not need to do anything more. The bundle is enabled automatically and
you have some nice default config in `config/packages/bm_backup_manager.yml`.

Step 2 (no Flex): Enable the Bundle
-----------------------------------

If you are not using Symfony Flex, you need to enable the bundle by adding it to
the list of registered bundles in the `app/AppKernel.php` file of your project.

```php
// config/bundles.php

return [
    BM\BackupManagerBundle\BMBackupManagerBundle::class => ['all' => true],
    // ...
];

```

Step 3: Configure your databases and filesystems
------------------------------------------------

```yaml
# config/packages/bm_backup_manager.yml

bm_backup_manager:
    database:
        development:
            type: mysql
            host: localhost
            port: 3306
            user: root
            pass: password
            database: test
            ignoreTables: ['foo', 'bar']
            # MySQL only, all optional:
            # onlyTables: ['users', 'orders']   # dump only these tables
            # singleTransaction: true           # --single-transaction (InnoDB)
            # ssl: true                         # --ssl
            # columnStatistics: false           # --column-statistics=0 (MySQL 8 mysqldump, older servers)
            # socket: /run/mysqld/mysqld.sock   # instead of host and port
            # extraParams: '--max-allowed-packet=64M'   # MySQL only; ignored for PostgreSQL

            # If DSN is specified, it will override other values. User and
            # password must be url-encoded (e.g. "p@ss" as "p%40ss").
            dsn: 'mysql://root:root_pass@127.0.0.1:3306/test_db'
        production:
            type: postgresql
            host: localhost
            port: 5432
            user: postgres
            pass: password
            database: test

            # You could also use a environment variable
            dsn: '%env(resolve:DATABASE_URL)%'
    storage:
        local:
            type: Local
            root: /path/to/working/directory
        s3:
            type: AwsS3
            key:
            secret:
            region: us-east-1
            version: latest
            bucket:
            root:
        # S3-compatible services (DigitalOcean Spaces, MinIO, ...) also set
        # endpoint: and use_path_style_endpoint:
        async_s3:
            type: AsyncAwsS3   # async-aws instead of the AWS SDK (Flysystem 2/3)
            key:
            secret:
            region: us-east-1
            bucket:
            root:
        gcs:
            type: Gcs
            project:
            keyFilePath:
            bucket:
            prefix:
        dropbox:
            type: Dropbox
            token:
            root:
        webdav:
            type: Webdav
            baseUri:
            userName:
            password:
            prefix:
        ftp:
            type: Ftp
            host:
            username:
            password:
            root:
            port: 21
            passive: true
            ssl: true
            timeout: 30
        sftp:
            type: Sftp
            host:
            username:
            password:
            root:
            port: 22
            timeout: 10
            privateKey:
```

Usage
=====

To backup from any configured database.
-------------------------------------------------

Backup the development database to `Amazon S3`. The S3 backup path will be `test/backup.sql.gz` in the end, when `gzip` is done with it.

```php
class Foo {
  private $backupManager;

  public function __construct(BackupManager $backupManager) {
      $this->backupManager = $backupManager;
  }

  public function bar() {
      $this->backupManager->makeBackup()->run('development', [new Destination('s3', 'test/backup.sql')], 'gzip');
  }
}
```

Or with a command:

```bash
php bin/console backup-manager:backup development s3 -c gzip --filename test/backup.sql
```

To restore from any configured filesystem.
---------------------------------------------------

Restore the database file `test/backup.sql.gz` from `Amazon S3` to the `development` database.

```php
class Foo {
  private $backupManager;

  public function __construct(BackupManager $backupManager) {
      $this->backupManager = $backupManager;
  }

  public function bar() {
      $this->backupManager->makeRestore()->run('s3', 'test/backup.sql.gz', 'development', 'gzip');
  }
}
```

Or with a command:

```bash
php bin/console backup-manager:restore development s3 test/backup.sql.gz -c gzip
```

> This package does not allow you to backup from one database type and restore to another. A MySQL dump is not compatible with PostgreSQL.

Requirements
============

- PHP 8.2+
- Symfony 6.4, 7.4 or 8.1+, at releases patched for known security
  advisories. CI tests each. For Symfony 5.4 or PHP 7.4–8.1 use version 3.x.
- The Flysystem adapter package for each storage type you use, for example
  `league/flysystem-aws-s3-v3` for `AwsS3`; see the
  [core adapter table](https://github.com/backup-manager/backup-manager#installation).
  A missing package is reported, with the package name, when the storage is
  used.
- Upgrading from 3.x: see [UPGRADE.md](UPGRADE.md).
- MySQL support requires `mysqldump` and `mysql` command-line binaries
- PostgreSQL support requires `pg_dump` and `psql` command-line binaries
- Gzip support requires `gzip` and `gunzip` command-line binaries

Changelog
=========

See [Changelog.md](Changelog.md).
