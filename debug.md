## Debugging

This section will show you how to write unit-tests for the router, view useful debugging information and answer some of the frequently asked questions. 

It will also covers how to report any issue you might encounter. 

### Creating unit-tests

The easiest and fastest way to debug any issues with the router, is to create a unit-test that represents the issue you are experiencing.

Unit-tests use a special `TestRouter` class, which simulates a request-method and requested url of a browser.

The `TestRouter` class can return the output directly or render a route silently.

```php
public function testUnicodeCharacters()
{
    // Add route containing two optional paramters with special spanish characters like "í".
    TestRouter::get('/cursos/listado/{listado?}/{category?}', 'DummyController@method1', ['defaultParameterRegex' => '[\w\p{L}\s-]+']);
    
    // Start the routing and simulate the url "/cursos/listado/especialidad/cirugía local".
    TestRouter::debugNoReset('/cursos/listado/especialidad/cirugía local', 'GET');
    
    // Verify that the url for the loaded route matches the expected route.
    $this->assertEquals('/cursos/listado/{listado?}/{category?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());
    
    // Start the routing and simulate the url "/test/Dermatología" using "GET" as request-method.
    TestRouter::debugNoReset('/test/Dermatología', 'GET');

    // Another route containing one parameter with special spanish characters like "í".
    TestRouter::get('/test/{param}', 'DummyController@method1', ['defaultParameterRegex' => '[\w\p{L}\s-\í]+']);

    // Get all parameters parsed by the loaded route.
    $parameters = TestRouter::request()->getLoadedRoute()->getParameters();

    // Check that the parameter named "param" matches the exspected value.
    $this->assertEquals('Dermatología', $parameters['param']);

    // Add route testing danish special characters like "ø".
    TestRouter::get('/category/økse', 'DummyController@method1', ['defaultParameterRegex' => '[\w\ø]+']);
    
    // Start the routing and simulate the url "/kategory/økse" using "GET" as request-method.
    TestRouter::debugNoReset('/category/økse', 'GET');
    
    // Validate that the URL of the loaded-route matches the expected url.
    $this->assertEquals('/category/økse/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());

    // Reset the router, so other tests wont inherit settings or the routes we've added.
    TestRouter::router()->reset();
}
```

#### Using the TestRouter helper

Depending on your test, you can use the methods below when rendering routes in your unit-tests.


| Method        | Description  |
| ------------- |-------------|
| ```TestRouter::debug($url, $method)``` | Will render the route without returning anything. Exceptions will be thrown and the router will be reset automatically. |
| ```TestRouter::debugOutput($url, $method)``` | Will render the route and return any value that the route might output. Manual reset required by calling `TestRouter::router()->reset()`. |
| ```TestRouter::debugNoReset($url, $method);```  | Will render the route without resetting the router. Useful if you need to get loaded route, parameters etc. from the router. Manual reset required by calling `TestRouter::router()->reset()`. |

### Debug information

The library can output debug-information, which contains information like loaded routes, the parsed request-url etc. It also contains info which are important when reporting a new issue like PHP-version, library version, server-variables, router debug log etc.

You can activate the debug-information by calling the alternative start-method. 

The example below will start the routing an return array with debugging-information

**Example:**

```php
$debugInfo = SimpleRouter::startDebug();
echo sprintf('<pre>%s</pre>', var_export($debugInfo));
exit;
```

**The example above will provide you with an output containing:**

| Key               | Description  |
| -------------     |------------- |
| `url`             | The parsed request-uri. This url should match the url in the browser.|
| `method`          | The browsers request method (example: `GET`, `POST`, `PUT`, `PATCH`, `DELETE` etc).|
| `host`            | The website host (example: `domain.com`).|
| `loaded_routes`   | List of all the routes that matched the `url` and that has been rendered/loaded. |
| `all_routes`      | All available routes |
| `boot_managers`   | All available BootManagers |
| `csrf_verifier`   | CsrfVerifier class |
| `log`             | List of debug messages/log from the router. |
| `router_output`   | The rendered callback output from the router. |
| `library_version` | The version of simple-php-router you are using. |
| `php_version`     | The version of PHP you are using. |
| `server_params`   | List of all `$_SERVER` variables/headers. |