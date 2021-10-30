# Released Notes

## v2.3.1 - (2021-11-30)

### Changed

- Changed `loadView` method

### Fixed

- Fixed ModernPHPException in `Course` class
- Fixed `error` method
- Fixed Bootstrap CSS version
- Fixed `Colors` class
- Fixed `ob_get_clean()` function in Wolf template

--------------------------------------------------------------------------

## v2.3.0 - (2021-10-06)

### Added

- Added Modern PHP Exception component
- Added CRUD skeleton in Model template
- Added `session` helper
- Added `isWeekend` method in `Convertime` class
- Added new tests

### Changed

- Changed cache test directory
- Changed `is_json` helper
- Changed `Valid` class

### Fixed

- Fixed `all` method in `InputHandler` class
- Fixed `refresh` method in `Response` class
- Fixed directory in Wolf template

### Removed

- Removed comments

--------------------------------------------------------------------------

## v2.2.2 - (2021-06-29)

### Added

- Added test for Cache

### Changed

- Change Bootstrap CDN

### Fixed

- Fixed bug Database in `Guardian` class
- Fixed return type in `Cache` class

--------------------------------------------------------------------------

## v2.2.1 - (2021-06-28)

### Fixed

- Fixed bug in Wolf Template

--------------------------------------------------------------------------

## v2.2.0 - (2021-06-19)

### Added

- Added Added minify by Vinci
- Added method to get data from `php://input`

### Removed

- Removed `WolfMinify` class
- Removed unnecessary comments

--------------------------------------------------------------------------

## v2.1.0 - (2021-04-23)

### Added

- Added new helper: `console_log()`
- Added support for Sodium encryption
- Added `respect\validation` package
- Added `isBase64` and `identical` methods in `Valid`
- Added new tests

### Fixed

- Fixed namespace in helper output 
- Fixed PSR-14
- Fixed validation in `Valid` and `is_json`

--------------------------------------------------------------------------
## v2.0.0 - (2021-04-15)

### Added

- Added typing on attributes
- Added new commands in Vinci Console
- Added support for CSS/JS minification
- Added new features in `HandleFiles`
- Added functions to helpers
- Added cache when executing Katrina command
- Added CSS bootstrap in login, forgot and dashboard files
- Added JSON return in the `input` method
- Added database dump support 
- Added `Controller.php`
- Added support and tests to PHPUnit
- Added `katrinaVersion` command 

### Fixed

- Fixed several bugs during the alpha and beta versions
- Fixed typing on attributes
- Fixed bugs in Console
- Fixed comments and typing in the Container 

### Removed

- Removed the `clear` method in the `Message` class
- Removed `destroy` method in the `Session` class
- Removed the `cache` and `json` methods from the `Response` class

### Changed

- Changed error template
- Changed helpers to the Core
- Changed Database folder for Core
- Changed `AuthController` to `Auth`
- Changed Vinci Console in a scalable way
- Changed `Hash` class method
- Changed Wolf cache
- Changed Login and Forgot structure