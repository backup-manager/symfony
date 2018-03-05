BackupManagerBundle
===================

[![Latest Stable Version](https://poser.pugx.org/backup-manager/symfony/version.png)](https://packagist.org/packages/backup-manager/symfony)
[![License](https://poser.pugx.org/backup-manager/symfony/license.png)](https://packagist.org/packages/backup-manager/symfony)
[![Build Status](https://travis-ci.org/backup-manager/symfony.svg?branch=master)](https://travis-ci.org/backup-manager/symfony)
[![Total Downloads](https://poser.pugx.org/backup-manager/symfony/downloads.png)](https://packagist.org/packages/backup-manager/symfony)

A simple database backup manager for Symfony2 with support for S3, Rackspace, Dropbox, FTP, SFTP.

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

Step 2: Enable the Bundle (Symfony 2 & 3)
-----------------------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new BM\BackupManagerBundle\BMBackupManagerBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Configure your databases and filesystems
------------------------------------------------

```yaml
# app/config.yml

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
            
            # If DSN is specified, it will override other values
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
        rackspace:
            type: Rackspace
            username:
            password:
            container:
        dropbox:
            type: DropboxV2
            token:
            key:
            secret:
            app:
            root:
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
            port: 21
            timeout: 10
            privateKey:
```

Usage
=====

Backup to / restore from any configured database.
-------------------------------------------------

Backup the development database to `Amazon S3`. The S3 backup path will be `test/backup.sql.gz` in the end, when `gzip` is done with it.

```php
$this->container->get('backup_manager')->makeBackup()->run('development', [new Destination('s3', 'test/backup.sql')], 'gzip');
```

```bash
php bin/console backup-manager:backup development s3 -c gzip --filename test/backup.sql
```

Backup to / restore from any configured filesystem.
---------------------------------------------------

Restore the database file `test/backup.sql.gz` from `Amazon S3` to the `development` database.

```php
$this->container->get('backup_manager')->makeRestore()->run('s3', 'test/backup.sql.gz', 'development', 'gzip');
```

```bash
php bin/console backup-manager:restore development s3 test/backup.sql.gz -c gzip 
```

> This package does not allow you to backup from one database type and restore to another. A MySQL dump is not compatible with PostgreSQL.

Requirements
============

- PHP 5.5
- MySQL support requires `mysqldump` and `mysql` command-line binaries
- PostgreSQL support requires `pg_dump` and `psql` command-line binaries
- Gzip support requires `gzip` and `gunzip` command-line binaries
