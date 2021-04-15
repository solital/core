# Released Notes

## v2.0.0-rc4 - (2021-04-XX)

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