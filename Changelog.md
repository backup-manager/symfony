# Changelog

## 2.1.3

### Fixed

- Fixed bug that made DropboxV2 config unavailable.

## 2.1.2

### Fixed

- Support for environment variables in the config. 

## 2.1.1

### Fixed

- Allow to only use "dsn" without configure "type". 
- Add better error message when both "dsn" and "type" is missing. 

## 2.1.0

### Added

- Support for providing a DSN string.
- Support for configure MySQL database with "singleTransaction" and "ssl"

### Fixed

- Issue with Symfony 3.2 where commands were private. 
- Issue with Symfony 3.2 because `scalarPrototype` was not defined. 

## 2.0.0

The 2.0.0 release is just a technical BC break. We removed all adapters from the
composer.json. So you need to re-add the adapters you were using.   

### Added

- Added support for Symfony 4. 
- Added tests
- Support for many storage names with the same type.
- Added commands for backup and restore
- Support for `ignoreTables` on MySQL databases.
- Added config `output_file_prefix`. 
- Support for DropboxV2

### Changes

- You have to `composer require` for the adapter you want to use. Nothing is included by default.
- The storage and database type is case-sensitive. 

### Removed

- Support for Symfony < 2.7. 

## 1.1.0

Support for Symfony 3.1.

## 1.0.0

First release.
