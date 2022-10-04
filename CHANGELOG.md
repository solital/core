# Released Notes

## v3.1.0 - (2022-10-03)

### Added

- Added command to create routes
- Added annotations in templates
- Added restrict IP in Middleware
- Added `IpRestrictAccessException` exception
- Added Collection for arrays
- Added feature to manipulate strings
- Added Queue in Logger
- Added dark mode for `welcome.php`
- Added new helpers

### Fixed

- Fixed session helper
- Fixed expiration time when deleting a cookie
- Fixed Logger tests
- Fixed timezone in init

### Changed

- Changed annotations in Logger
- Changed composer.json
- Changed vinci.example file

### Removed

- Removed `gmstrftime` function in `HttpCache`
- Removed `phoole/base` package

--------------------------------------------------------------------------

## v3.0.4 - (2022-09-25)

### Fixed

- Fixed version in `composer.json`
- Fixed `exception_json_return` key of Modern PHP Exception array in `Application` class

--------------------------------------------------------------------------

## v3.0.3 - (2022-09-23)

### Changed

- Changed `Password` class for PHP Secure Password version 2.0
- Changed Logger directory
- Changed Container directory

### Fixed

- Fixed composer.json
- Fixed ApplicationException in `Application` class

--------------------------------------------------------------------------

## v3.0.2 - (2022-09-21)

### Changed

- Changed exceptions in `Router` and Wolf class for Modern PHP Exception version 2

### Fixed

- Fixed Modern PHP Exception in Console
- Fixed Modern PHP Exception version 2
- Fixed ENV variables in `Auth` class
- Fixed `unset` in `Cookie` class

--------------------------------------------------------------------------

## v3.0.1 - (2022-09-19)

### Changed

- Changed timezone parameter in `Convertime` class

### Fixed

- Fixed bug where new sessions folder wasn't created
- Fixed return in `initDB` method in `MigratorVersionProviderDB` class
- Fixed debug in `Application` class
- Fixed method in `MigrationNameDefault` template
- Fixed documentation domain
- Fixed cache template not loading

--------------------------------------------------------------------------

## v3.0.0 - (2022-05-10)

### Added

- Added PHP 8.0 support
- Added `SecurePassword` component
- Added PSR-6
- Added `getenv` function
- Added command to copy configuration files
- Added YAML support
- Added `BaseController` class
- Added `mapped_implode`, `middleware`, `cache` and `view` helpers
- Added Kernel component
- Added Migrations and Seeds
- Added logging on Wolf and routes
- Added Katrina ORM 2
- Added autoload in helpers and routers files
- Added Queue and QueueMail
- Added support for creating custom commands
- Added `Request::limit` and `Request::repeat`
- Added WolfException class
- Added pure HTML template support in Wolf Template
- Added `makeCache` method in WolfCache class
- Added new `Session`, `Cookie`, `Console`, `Container` and `Logger` components

### Changed

- Changed namespace of all classes
- Changed `Controller.php` and `Model.php`
- Changed password helpers compatible with `SecurePassword` component
- Changed constantes in `Auth` class
- Changed Bootstrap version
- Changed Wolf Template Cache
- Changed `FileSystem,`, `Mail` and `Validation` folders to root
- Changed method to check different project domain
- Changed `Dump` class

### Fixed

- Fixed methods in `Request` class
- Fixed `copy` in `HandleFiles`
- Fixed `Convertime` class
- Fixed return in helper `view`
- Fixed Exception in Logger
- Fixed messages in Console
- Fixed constant SITE_ROOT
- Fixed Wolf Template
- Fixed Exceptions
- Fixed Mailer
- Fixed Guardian login
- Fixed `htmlspecialchars` in Wolf
- Fixed filter FILTER_SANITIZE_STRING to FILTER_SANITIZE_FULL_SPECIAL_CHARS
- Fixed verify `$_GET` and `$_POST` variables

### Removed

- Removed deprecated class
- Removed `NativeMail` class
- Removed `logger` helper
- Removed debug methods
- Removed command to minify assets
--------------------------------------------------------------------------