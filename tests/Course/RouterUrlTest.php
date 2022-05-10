<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';
require_once dirname(__DIR__) . '/TestRouter.php';

use PHPUnit\Framework\TestCase;

/**
 * CORRIGIR
 */
class RouterUrlTest extends TestCase
{
    /* public function testUnicodeCharacters() EEEEEEERRRRRRR
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        // Test spanish characters
        TestRouter::get('/cursos/listado/{listado?}/{category?}', 'DummyController@unicode', ['defaultParameterRegex' => '[\w\p{L}\s-]+']);
        TestRouter::get('/test/{param}', 'DummyController@param', ['defaultParameterRegex' => '[\w\p{L}\s-\í]+']);
        TestRouter::debugNoReset('/cursos/listado/especialidad/cirugíalocal', 'get');

        $this->assertEquals('/cursos/listado/{listado?}/{category?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/test/Dermatología');
        $parameters = TestRouter::request()->getLoadedRoute()->getParameters();

        $this->assertEquals('Dermatología', $parameters['param']);

        // Test danish characters
        TestRouter::get('/kategori/økse', 'DummyController@method2', ['defaultParameterRegex' => '[\w\ø]+']);
        TestRouter::debugNoReset('/kategori/økse', 'get');

        $this->assertEquals('/kategori/økse/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::router()->reset();
    } */

    /* public function testOptionalParameters() EEEERRRRR
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        TestRouter::get('/aviso/legal', 'DummyController@unicode');
        TestRouter::get('/listado/{category}', 'DummyController@unicode');
        TestRouter::get('/lang/{lang}/{name}', 'DummyController@params');
        TestRouter::get('/{param?}', 'DummyController@param');

        TestRouter::debugNoReset('/lang/especialidad/optional', 'get');
        $this->assertEquals('/lang/{lang}/{name}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/listado/optional', 'get');
        $this->assertEquals('/listado/{category}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/optional', 'get');
        $this->assertEquals('/{param?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/avisolegal', 'get');
        $this->assertNotEquals('/aviso/{aviso}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/avisolegal', 'get');
        $this->assertEquals('/{param?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::router()->reset();
    } */

    /* public function testSimilarUrls() EEERRR
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        // Match normal route on alias
        TestRouter::resource('/url11', 'DummyController@method2');
        TestRouter::resource('/url1', 'DummyController@method2', ['as' => 'match']);

        TestRouter::debugNoReset('/url1', 'get');

        $this->assertEquals(TestRouter::getUri('match'), TestRouter::getUri());

        TestRouter::router()->reset();
    } */

    public function testUrls()
    {
        #$_SERVER["REQUEST_METHOD"] = 'get';

        // Match normal route on alias
        TestRouter::get('/', 'DummyController@method2', ['as' => 'home']);

        TestRouter::get('/about', 'DummyController@about');

        TestRouter::group(['prefix' => '/admin', 'as' => 'admin'], function () {

            // Match route with prefix on alias
            TestRouter::get('/{id?}', 'DummyController@method2', ['as' => 'home']);

            // Match controller with prefix and alias
            TestRouter::controller('/users', 'DummyController', ['as' => 'users']);

            // Match controller with prefix and NO alias
            TestRouter::controller('/pages', 'DummyController');
        });

        TestRouter::group(['prefix' => 'api', 'as' => 'api'], function () {

            // Match resource controller
            TestRouter::resource('phones', 'DummyController');
        });

        TestRouter::controller('gadgets', 'DummyController', ['names' => ['getIphoneInfo' => 'iphone']]);

        // Match controller with no prefix and no alias
        TestRouter::controller('/cats', 'CatsController');

        // Pretend to load page
        TestRouter::debugNoReset('/', 'get');

        $this->assertEquals('/gadgets/iphoneinfo/', TestRouter::getUri('gadgets.iphone')->getPath());

        // Should match /
        $this->assertEquals('/', TestRouter::getUri('home')->getPath());

        // Should match /about/
        $this->assertEquals('/about/', TestRouter::getUri('DummyController@about')->getPath());

        // Should match /admin/
        $this->assertEquals('/admin/', TestRouter::getUri('admin')->getPath());

        // Should match /admin/
        $this->assertEquals('/admin/', TestRouter::getUri('admin.home')->getPath());

        // Should match /admin/2/
        $this->assertEquals('/admin/2/', TestRouter::getUri('admin.home', ['id' => 2])->getPath());

        // Should match /admin/users/
        $this->assertEquals('/admin/users/', TestRouter::getUri('admin.users')->getPath());

        // Should match /admin/users/home/
        $this->assertEquals('/admin/users/home/', TestRouter::getUri('admin.users@home')->getPath());

        // Should match /cats/
        $this->assertEquals('/cats/', TestRouter::getUri('CatsController')->getPath());

        // Should match /cats/view/
        $this->assertEquals('/cats/view/', TestRouter::getUri('CatsController', 'view')->getPath());

        // Should match /cats/view/123
        $this->assertEquals('/cats/view/123/', TestRouter::getUri('CatsController@getView', ['123'])->getPath());

        // Should match /funny/man/
        $this->assertEquals('/funny/man/', TestRouter::getUri('/funny/man')->getPath());

        // Should match /?jackdaniels=true&cola=yeah
        $params = TestRouter::getUri('home', null, ['jackdaniels' => 'true', 'cola' => 'yeah'])->getParams();
        $params = $this->mapped_implode('&', $params, '=');

        $this->assertEquals('/?jackdaniels=true&cola=yeah', '/?'.$params);

        TestRouter::router()->reset();
    }

    private function mapped_implode($glue, $array, $symbol = '=')
    {
        return implode(
            $glue,
            array_map(
                function ($k, $v) use ($symbol) {
                    return $k . $symbol . $v;
                },
                array_keys($array),
                array_values($array)
            )
        );
    }
}
