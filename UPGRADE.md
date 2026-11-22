# Upgrading

## From 3.x to 4.0

### Requirements

- PHP 8.2 or later.
- Symfony 6.4, 7.4 or 8.1+, at releases patched for known security
  advisories (6.4.50+, 7.4.23+, 8.1.11+). Symfony 5.4 users stay on 3.x.
- `backup-manager/backup-manager` ^4.1, which requires Flysystem 3. Install
  the Flysystem 3 adapter package for each storage type you use (see the
  README).

### Removed storage types

The core library removed them in 4.0; their adapter packages only support
Flysystem 1.

| Type | Replacement |
|---|---|
| `DropboxV2` | `Dropbox` with `spatie/flysystem-dropbox` (`token`, `root`) |
| `B2` | `AwsS3` with B2's S3-compatible endpoint (`endpoint`, `use_path_style_endpoint`) |
| `Rackspace` | `AwsS3` with an S3-compatible service |

The services `backup_manager.filesystems.b2_filesystem`,
`backup_manager.filesystems.dropbox_v2_filesystem` and
`backup_manager.filesystems.rackspace_filesystem` are removed.

### Configuration

- `Dropbox` storage accepts only `token` and `root` (the keys the core
  library reads); `key`, `secret` and `app` are rejected.
- The "only valid on MySQL databases" error names the offending database and
  key, and `type: pdo_mysql` counts as MySQL.

### Classes

- `BackupCommand`, `RestoreCommand`, `BMBackupManagerExtension`,
  `Configuration` and `ConfigFactory` are `final`.
- `BMBackupManagerExtension` extends
  `Symfony\Component\DependencyInjection\Extension\Extension` instead of the
  HttpKernel `Extension` (deprecated in Symfony 8.1), so it no longer has
  `getAnnotatedClassesToCompile()` / `addAnnotatedClassesToCompile()`.
- Native parameter and return types throughout, and `strict_types`.
- `BackupCommand::__construct(Manager $manager, string $filePrefix = '')`:
  `$filePrefix` is a typed string.
