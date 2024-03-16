# Released Notes

## v4.2.0 - (2024-03-15)

### Added

- Added argument and option validade in class
- Added validate attribute `Override` on `handle` method
- Added spell check command

### Fixed

- Fixed arguments numbers on class and I/O
- Fixed command without arguments and/or without options

### Changed

- Changed `read` method
- Changed tests

### Removed

- Removed `TableBuilder` class

----------------------------------------------------------------------

## v4.1.0 - (2024-02-18)

### Added

- Added `Table` class
- Added `Override` attribute in `CommandException` class

### Fixed

- Fixed `DefaultCommandsTrait` trait

### Changed

- Changed `TableBuilder` class to deprecated
- Changed `InputOutput` and `ProgressBar` class

----------------------------------------------------------------------

## v4.0.1 - (2024-02-06)

### Removed

- Removed `vinci` file in `composer.json`

----------------------------------------------------------------------

## v4.0.0 - (2024-01-21)

### Added

- Added PHP 8.3 minimum version
- Added array and string in `read` construct
- Added Modern PHP Exception 3

### Changed

- Changed `about` default method
- Changed colors message
- Changed construct default value

### Fixed

- Fixed when comand not exists in `help`
- Fixed construct bug in `read`, `list` and `help` method