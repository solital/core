# Released Notes

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