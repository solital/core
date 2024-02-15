# Released Notes

## v4.0.3 - (2024-02-15)

### Fixed

- Fixed `Password` class and tests

### Changed

- Changed `SecurePassword` to version 3
- Changed `secure.php` helper
- Changed project with `Rector`

--------------------------------------------------------------------------

## v4.0.2 - (2024-02-13)

### Fixed

- Fixed get empty data in DB with `db:list` command
- Fixed separator ("." or "/") in Wolf template
- Fixed cache in Wolf template if file exists

### Changed

- Changed `Crypt` class as deprecated

### Removed

- Removed `vinci` file in `composer.json`

--------------------------------------------------------------------------

## v4.0.1 - (2024-01-28)

### Changed

- Changed `Application` class to abstract
- Changed `getInstance` method in `Application` class to public
- Changed `MiddlewareInterface` and `IpRestrictAccessException` as deprecated
- Changed `dumper` and `export` as deprecated

### Fixed

- Fixed `BrowserStorage` class
- Fixed date at Malware Scanner component
- Fixed `IpRestrictAccess` class not working
- Fixed Reflection at `http` and `others` helpers
- Fixed Reflection at `HttpControllerTrait` and `Controller` class

--------------------------------------------------------------------------

## v4.0.0 - (2024-01-21)

### Added

- Added PHP 8.3 minimum version
- Added `mail.yaml` config file
- Added Symfony Finder and Filesystem components in `HandleFiles` class
- Added monolog support and YAML config file
- Added `Dotenv` class
- Added timezone in `Application` class
- Added PSR-13
- Added Malware Scanner
- Added Task Scheduler
- Added option to create Controller, Model, Seeder and migration with one command
- Added `json_validate` in `JSON` class
- Added conditional if true for Wolf Template
- Added recovery password template
- Added `ServiceProvider` class

### Changed

- Changed `HandleFiles` class
- Changed `HandleFilesTrait` to `HandlePermissionTrait`
- Changed `Dump` and `DumpException` directory
- Changed `StrException` directory
- Changed `Convertime` construct
- Changed `MakeAuth` class
- Changed command `cache:clear` to `storage:clear`
- Changed authenticate and recovery password classes
- Changed `request_limit` and `request_repeat`
- Changed `view` helper
- Changed template in verify domain
- Changed user command class

### Fixed

- Fixed construct bug in `MakeMigration` and `Migration` class
- Fixed CSRF custom verifier
- Fixed migrations and seeders namespace
- Fixed Queue not sleeping
- Fixed bug connection in `ListDatabase` command

### Removed

- Removed mail options in `bootstrap.yaml`
- Removed method `file` in `HandleFiles` class
- Removed `exception_json_return` option in `bootstrap.yaml`
- Removed old `Logger` component
- Removed `vlucas/phpdotenv` component

--------------------------------------------------------------------------