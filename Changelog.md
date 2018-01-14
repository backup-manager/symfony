# Changelog

## 2.0.0

The 2.0.0 release is just a technical BC break. We removed all adapters from the
composer.json. So you need to re-add the adapters you were using.   

### Added

- Added support for Symfony 4. 
- Added tests
- Support for many storage names with the same type. 

### Changes

- You have to `composer require` for the adapter you want to use. Nothing is included by default.

### Removed

- Support for Symfony < 2.7.  

## 1.1.0

Support for Symfony 3.1.

## 1.0.0

First release.