# Released Notes

## v3.5.0 - (2023-10-23)

### Added

- Added constants in the `KernelTrait` trait
- Added minimum PHP version to 8.2

### Fixed

- Fixed creating folder in `Seeder` class
- Fixed PSR in Http folder classes

--------------------------------------------------------------------------

## v3.4.1 - (2023-04-15)

### Changed

- Changed Constants for class `Application`

--------------------------------------------------------------------------

## v3.4.0 - (2023-03-02)

### Added

- Added PHP code generator for templates
- Added `createAppFolder` and `removeAppFolder` methods

### Changed

- Changed Constants for Trait `Kerneltrait`
- Changed `getRootApp` method: the method no longer creates folders automatically

### Removed

- Removed deprecated templates
- Removed templates in classes: `Migrations`, `Seeds` and `Queue`
- Removed `getRootTest` method in `Application` class

--------------------------------------------------------------------------

## v3.3.2 - (2023-03-01)

### Fixed

- Fixed rollback in migrations
- Fixed `str_replace` function in `LoadableRoute` class

--------------------------------------------------------------------------

## v3.3.1 - (2023-02-03)

### Added 

- Added `convertime` helper

### Changed

- Changed Psr-14 namespace

--------------------------------------------------------------------------

## v3.3.0 - (2022-11-18)

### Added 

- Added new `JSON` class
- Added exception in JSON
- Added `logFile` static method in `Application` class
- Added `NotFoundHttpException` class
- Added phpstan verification

### Fixed

- Fixed comments
- Fixed Wolf Cache Time
- Fixed Command `HandleCache`
- Fixed `jsonSerialize` return type
- Fixed class inside Auth folder

### Changed

- Changed `encodeJSON` and `decodeJSON` helpers
- Changed `InputJson` class as deprecated
- Changed `WolfCache` to Trait

--------------------------------------------------------------------------

## v3.2.1 - (2022-11-06)

### Fixed

- Fixed final class
- Fixed `HandleFiles` class
- Fixed `HandleCache` command

--------------------------------------------------------------------------

## v3.2.0 - (2022-10-28)

### Added

- Added return data from database using Vinci Console
- Added new trait: `HttpControllerTrait`
- Added `removeParamsUrl`, `getRequestParams`, `redirect`, `requestLimit` and `requestRepeat` methods
- Added global method to return yaml values
- Added Solution in `ApplicationException`

### Changed

- Changed `switch` declaration by `match` function in `Auth` class

### Removed

- Removed `ContainerException` and `ContainerNotFoundException` in Exception folder

--------------------------------------------------------------------------

## v3.1.1 - (2022-10-16)

### Fixed

- Fixed minify on Wolf
- Fixed pagination conflict with tags in Wolf
- Fixed `ArrayAccess` interface in `ArrayCollection` class
- Fixed repeated commands in Vinci

### Changed

- Changed cache in Wolf
- Changed packages in composer.json

--------------------------------------------------------------------------

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