# Released Notes

## v1.3.0 - (2021-03-18)

### Added

- Added exception if there is no parameter in the anonymous function 
- Added HttpCache
- Added most support for JSON
- Added validation date and values
- Added Katrina 1.2.0

--------------------------------------------------------------------

## v1.2.1 - (2021-03-15)

### Fixed

- Fixed directory bar by constant `DIRECTORY_SEPARATOR`
- Fixed Wolf Template

--------------------------------------------------------------------

## v1.2.0 - (2021-03-01)

### Added

- Added PSR-3 and PSR-14

### Fixed

- Fix bug in `HandleFiles` class

--------------------------------------------------------------------

## v1.1.1 - (2021-01-24)

### Fixed

- Fix namespace
- Fix PHP 8 error in Composer

--------------------------------------------------------------------

## v1.1.0 - (2020-12-24)

### Added

- Added default path

### Fixed

- Fix double slashes issue in `Uri.php`

--------------------------------------------------------------------

## v1.0.4 - (2020-11-26)

### Fixed

- Fixed HandleFiles

--------------------------------------------------------------------

## v1.0.3 - (2020-11-01)

### Fixed

- Fixed router not found custom
- Fixed PHPDoc

--------------------------------------------------------------------

## v1.0.2 - (2020-10-25)

### Fixed

- Fixed Connection in Vince Console
- Fixed Katrina version in Vince Console
- Fixed callback not found
- Fixed HTTP code 404
- Fixed duplicate route
- Fixed Cookie delete path

--------------------------------------------------------------------

## v1.0.1 - (2020-07-31)

### Fixed

- Fixed Vinci Console
- Fixed Vinci bug replacing existing file
- Fixed duplicate route
- Fixed title of exception templates
- Fixed CSRF token for Session
- Fixed key in the `Session` class

--------------------------------------------------------------------

## v1.0.0 - (2020-07-17)

### Fixed

- Fixed `ForgotController` class when creating in Vinci
- Fixed bug in class `Router`
- Fixed import in the `LoadableRoute` class
- Fixed importing of templates in Exceptions
- Fixed wrong class in `Request`
- Fixed bug in the `InputHandler` class
- Fixed return type in the `Message` class
- Fixed return type in the `Guardian` class
- Fixed return type in the `Hash` class
- Fixed return type in the `Reset` class
- Fixed hash bug when passing in url
- Fixed bug in Wolf when creating the cache

### Removed

- Removed `loadComponents` class

### Changed

- Changed `$_GET` and` $_POST` by `filter_input_array` in the `InputHandle` class
