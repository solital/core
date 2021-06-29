# Released Notes

## v2.2.2 - (2021-06-29)

### Added

- Added test for Cache

### Change

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