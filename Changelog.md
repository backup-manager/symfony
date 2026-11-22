# Changelog

## UNRELEASED

## 3.3.0

### Added

- Support for Symfony 7 and 8, and Symfony 6 and PHP 8.1+ in a release
  (#87, merged earlier; #89, #90).
- Flysystem 2 and 3 support through backup-manager 3.3 (#91).
- `AsyncAwsS3` storage type, using async-aws instead of the AWS SDK (#84),
  and `Webdav` storage type.
- MySQL options `onlyTables`, `columnStatistics` and `socket`; AwsS3 option
  `use_path_style_endpoint`; Gcs option `root`.
- Option `extraParams` for database configs (#82).
- Tests on GitHub Actions for Symfony 5.4 to 8 and PHP 7.4 to 8.5.

### Changed

- Requires `backup-manager/backup-manager` ^3.3 and PHP 7.4+.
- Symfony components require releases patched for their known advisories:
  5.4.56+, 6.4.50+, 7.4.23+ or 8.1.11+ (`framework-bundle` 5.4.54+, 6.4.49+;
  CVE-2026-123824, CVE-2026-123339, CVE-2026-123792, CVE-2026-121619 and
  others), and `symfony/process` releases patched for CVE-2024-51736 and
  CVE-2026-24739. Symfony 3.4 and 4.4 have no patched releases and are no
  longer accepted; stay on 3.2.1 or upgrade Symfony.
- A storage type whose adapter package is missing is reported by the core
  library when the storage is used (naming the package for the installed
  Flysystem version), instead of by this bundle when the container is built.
  The old check only knew Flysystem 1 class names and rejected every storage
  on Flysystem 2 and 3.
- Commands are registered lazily (`#[AsCommand]`).
- Native return types, required by Symfony 6+ where the parent methods
  declare them: `BackupCommand::execute(): int` and `configure(): void`
  (same for `RestoreCommand`), `BMBackupManagerExtension::load(): void`,
  `Configuration::getConfigTreeBuilder(): TreeBuilder`. A subclass that
  overrides one of these methods must declare the same return type.

### Fixed

- Configuration in `config/packages/bm_backup_manager.yml` is loaded with
  Flex (#88; fixed on master after 3.2.1).

## 3.2.0

### Added

- Support for auto wiring `BackupManager`

## 3.1.1

### Fixed

- Removed extra forward slash on DSN path.

## 3.1.0

### Added

- "endpoint" option to AwsS3

### Fixed

- RestoreCommand must return int

### Changed

- Updated to version 2 of nyholm/dsn

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
