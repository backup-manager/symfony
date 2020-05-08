# Changelog

## 3.0.0

### Fixed

- Support for Symfony 5

### Changed

- Removed support for PHP < 7.3

## 2.3.0

### Added

- Support for Google cloud storage
- Support for Symfony 5

### Changed

- Removed support for PHP < 7.2
- Removed support Symfony < 3.4

## 2.2.0

### Fixed

- Fixed root node deprecation in symfony/config > 4.1
- Adds support for the Backblaze B2 Cloud Storage

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
