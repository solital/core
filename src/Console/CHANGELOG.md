# Released Notes

## v4.5.3 - (2024-12-30)

### Fixed

- Fixed implicit nullable in `CommandException`

----------------------------------------------------------------------

## v4.5.2 - (2024-09-12)

### Fixed

- Fixed PHP 8.4 implicit nullable

----------------------------------------------------------------------

## v4.5.1 - (2024-09-10)

### Changed

- Changed output in some default commands to banner

### Fixed

- Fixed `toString` method in `CommandException` class
- Fixed `clearstatecache` in default commands

----------------------------------------------------------------------

## v4.5.0 - (2024-08-14)

### Added

- Added colors in `status` method

### Fixed

- Fixed colors in `banner` method

----------------------------------------------------------------------

## v4.4.0 - (2024-06-02)

### Added

- Added history command
- Added input for passwords

### Changed

- Changed `InputOutput` colors

### Removed

- Removed `MessageTrait` trait

----------------------------------------------------------------------

## v4.3.0 - (2024-04-05)

### Added

- Added new component to output
- Added `call` method

### Changed

- Changed `MessageTrait` to deprecated

### Fixed

- Fixed option with value
- Fixed when command class not extends `Command` class

### Removed

- Removed `codedungeon/php-cli-colors` component

----------------------------------------------------------------------

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