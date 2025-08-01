# Released Notes

## v4.8.1 - (2025-07-04)

### Fixed

- Fixed implicit nullable in `HttpControllerTrait` trait

--------------------------------------------------------------------------

## v4.8.0 - (2025-06-18)

### Added

- Added `Number::toRoman` method
- Added support to ignore migration
- Added `--refresh` an `--reset` options to migrations

--------------------------------------------------------------------------

## v4.7.1 - (2025-04-05)

### Fixed

- Fixed and added new validation package

### Removed

- Removed `wixel/gump` package

--------------------------------------------------------------------------

## v4.7.0 - (2025-03-31)

### Added

- Added  `Str::doesntContain()`, `Str::between()` and `Str::uuid()` methods
- Added `disableCoreDebug` method in `init` method

### Deprecated

- Deprecated `getMonthInt` method in `Temporal` component
- Deprecated `time` property in `TaskSchedule` class

--------------------------------------------------------------------------

## v4.6.2 - (2025-03-04)

### Changed

- Changed message in console when not exists commands

--------------------------------------------------------------------------

## v4.6.1 - (2024-12-30)

### Fixed

- Fixed attribute in make schedule
- Fixed print in scan component
- Fixed explicit nullable in `CommandException`

--------------------------------------------------------------------------

## v4.6.0 - (2024-10-11)

### Added

- Added session handler support
- Added `session.yaml` file to configuration
- Added policy validation trait for Password
- Added render pure HTML in Wolf
- Added `cookie` helper
- Added `setMicrosecond`, `getMicrosecond`, `createFromTimeStamp`, 
`toi18nFormat`, `getTextualDay`, `getTextualShortDay`, `getTextualMonth`, 
`getTextualShortMonth`, `getLastDayOfMonth`, `getEasterDate`, 
`getEasterDateOrthodox`, `isLeapYear` methods to Temporal class

### Fixed

- Fixed `Cookie` class to PHP 8.3 cookie parse

### Deprecated

- Deprecated `getMonthName` and `getMonthShortName` methods in Temporal class

--------------------------------------------------------------------------

## v4.5.4 - (2024-09-12)

### Fixed

- Fixed PHP 8.4 implicit nullable
- Fixed types in `Route` class
- Fixed `message` helper not get message
- Fixed session start already exists

### Changed

- Changed `Password` class to use `AlgorithmEnum`
- Changed controller and model template as final
- Changed password cost to 12 in `auth.yaml`
- Changed return types in auth template
- Changed cost option in `auth.yaml` to 12

--------------------------------------------------------------------------

## v4.5.3 - (2024-08-22)

### Fixed

- Fixed `Schedule` class not found
- Fixed `JSON` class
- Fixed `take` param in `session` helper

--------------------------------------------------------------------------

## v4.5.2 - (2024-08-14)

### Improved

- Added `ignoreErrors` in `KernelTrait`
- Added `mask` in `Str` class
- Added polyfill for PHP 8.4
- Improved `Str` methods with Steroids for PHP
- Improved APCu cache adapter
- Improved `Schedule` component

### Changed

- Changed `StrMask` class

### Fixed

- Fixed migrations query builder with table
- Fixed template not found in Wolf

### Removed

- Removed old `EventDispatcher` and old `ListenerProvider` classes
- Removed `MiddlewareCallableInterface` interface

--------------------------------------------------------------------------

## v4.5.1 - (2024-07-17)

### Fixed

- Added `SensitiveParameter` attribute in lot of classes

--------------------------------------------------------------------------

## v4.5.0 - (2024-07-13)

### Added

- Added helpers `encrypt` and `decrypt`
- Added `runtime.yaml` config
- Added `crypt` option on `bootstrap.yaml`
- Added `session_regenerate_id` function on `Auth` class

### Deprecated

- Deprecated sodium methods in `Hash` and `Auth` class
- Deprecated `start` method on `Session` class

--------------------------------------------------------------------------

## v4.4.0 - (2024-06-02)

### Added

- Added helpers for `Application`, `Reflection` and server
- Added method to add values on YAML file
- Added custom flash messages
- Added multibyte to `Str` component
- Added authorization for login
- Added HTTPS for server

### Changed

- Changed helpers file names
- Changed `Str` component
- Changed files using `str_*` functions to `Str` component

### Fixed

- Fixed server link in `server` command
- Fixed `Number::clamp()` method
- Fixed `viewExists` method on Wolf class
- Fixed `extend` method in `Container` class

### Removed

- Removed `respect/validation` package
- Removed class, methods and constants deprecated:
    - `AuthDatabase` class
    - `ConnectionDatabase` class
    - Constants on `KernelTrait`
    - `MessageTrait` trait
    - `convertime` helper
    - `Convertime` and `Valid` class

--------------------------------------------------------------------------

## v4.3.0 - (2024-04-05)

### Added

- Added new method to debug routers
- Added new class to debug Core
- Added new resource: `Number` and `Temporal` components
- Added validator data on `InputHandler`
- Added generate boot manager command
- Added `MakeBoot` and `MakeValidator` command class

### Changed

- Changed database exception
- Changed `DotenvException` class
- Deprecated `Valid` and `Convertime` class
- Deprecated `AuthDatabase` and `ConnectDatabase` class
- Deprecated constants in `KernelTrait`

### Fixed

- Fixed `ResourceControllerInterface` when make a resource
- Fixed OpenSSL crypt and decrypt on `Hash` class

### Removed

- Removed `JsonSerializable` class
- Removed `codedungeon/php-cli-colors` package
- Removed `Diff` class
- Removed `cloner` helper
- Removed provider DB class

--------------------------------------------------------------------------

## v4.2.0 - (2024-03-16)

### Added

- Added `APP_HASH` environment to `Hash` and `Password` class
- Added `SensitiveParameter` attribute on `Password` and helpers
- Added `deferWithTimer` method on `EventLoop` class
- Added `Mailer`, `Message` and `EventLoop` on Default Container
- Added `Yac` cache support
- Added HTTP Client to Core
- Added helper to container
- Added support to basic and digest auth
- Added custom ClassLoader
- Added create resource command
- Added custom error handler

### Changed

- Changed key in sodium support on `Hash` class
- Changed `error` router

### Fixed

- Fixed `ListDatabase` class command

### Removed

- Removed peeper on `auth.yaml`
- Removed `symfony/var-dumper` component

--------------------------------------------------------------------------

## v4.1.3 - (2024-03-14)

### Fixed

- Fixed POST stream at `InputHandler` class
- Fixed CSRF exception
- Fixed IP restrict exception

--------------------------------------------------------------------------

## v4.1.2 - (2024-03-02)

### Fixed

- Fixed `SITE_ROOT` constant checking on `yamlParse` method
- Fixed Closure parameters on `ClassLoader` and `Route` class
- Fixed `RouteResource` class
- Fixed url clone and `getUrlCopy` method at `Request` class
- Fixed methods without `str_contains` function
- Fixed duplicate route checking
- Fixed CSRF verifier
- Fixed `CacheItem` interface
- Fixed dynamic properties on `memorize` helper

--------------------------------------------------------------------------

## v4.1.1 - (2024-02-25)

### Fixed

- Fixed Modern PHP Exception not started
- Fixed `file_exists` function, used `fileExistsWithoutCache` method
- Fixed Occurrences for Modern PHP Exceptions

--------------------------------------------------------------------------

## v4.1.0 - (2024-02-23)

### Added

- Added `uniquid_real` helper
- Added `crypt_type` option at `auth.yaml`
- Added `cache` helper to return a `SimpleCache` instance
- Added `memorize` component
- Added `getTemplate` at Wolf
- Added APCu and memcached support in cache
- Added option to disable the escape special chars at Wolf
- Added tests to `Application` class
- Added new command: schedule
- Added support to Katrina ORM 2.4

### Changed

- Changed `FileBackend` to `FileBackendAdapter`
- Changed `yamlParse` methods at all classes

### Fixed

- Fixed comments in helpers
- Fixed methods interface at `ServerRequest` and `UploadedFile` classes
- Fixed database connection on tests
- Fixed table at `ListDatabase` and `CourseList` class
- Fixed Override on all comamnd classes
- Fixed directory not found on `database.yaml`
- Fixed connection test on `Dump` class

### Removed

- Removed `symfony/var-exporter` component
- Removed `is_json`, `dumper`, `export` and `is_json` helpers
- Removed classes: `Cache`, `Crypt`, `InputJson`, 
    `MiddlewareInterface` and `IpRestrictAccessException` classes

--------------------------------------------------------------------------

## v4.0.5 - (2024-02-20)

### Added

- Added `.gitattributes` file

### Fixed

- Fixed `ModernPHPException` without config file
- Fixed type on `autoload` method

--------------------------------------------------------------------------

## v4.0.4 - (2024-02-18)

### Added

- Added `Password` and `Wolf` providers
- Added `yamlParse` method
- Added `add` method in `DotEnv` class

### Fixed

- Fixed namespace at Commands
- Fixed `MakeRouter` class command
- Fixed `ApplicationException` class

### Changed

- Changed `getYamlVariables` and `getDirConfigFiles` to deprecated

--------------------------------------------------------------------------

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