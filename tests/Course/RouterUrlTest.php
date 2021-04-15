<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';

class RouterUrlTest extends \PHPUnit\Framework\TestCase
{

    public function testIssue253()
    {
        TestRouter::get('/', 'DummyController@method1');
        TestRouter::get('/page/{id?}', 'DummyController@method1');
        TestRouter::get('/test-output', function() {
            return 'return value';
        });

        TestRouter::debugNoReset('/page/22', 'get');
        $this->assertEquals('/page/{id?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/', 'get');
        $this->assertEquals('/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        $output = TestRouter::debugOutput('/test-output', 'get');
        $this->assertEquals('return value', $output);

        TestRouter::router()->reset();
    }

    public function testUnicodeCharacters()
    {
        // Test spanish characters
        TestRouter::get('/cursos/listado/{listado?}/{category?}', 'DummyController@method1', ['defaultParameterRegex' => '[\w\p{L}\s-]+']);
        TestRouter::get('/test/{param}', 'DummyController@method1', ['defaultParameterRegex' => '[\w\p{L}\s-\í]+']);
        TestRouter::debugNoReset('/cursos/listado/especialidad/cirugía local', 'get');

        $this->assertEquals('/cursos/listado/{listado?}/{category?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/test/Dermatología');
        $parameters = TestRouter::request()->getLoadedRoute()->getParameters();

        $this->assertEquals('Dermatología', $parameters['param']);

        // Test danish characters
        TestRouter::get('/kategori/økse', 'DummyController@method1', ['defaultParameterRegex' => '[\w\ø]+']);
        TestRouter::debugNoReset('/kategori/økse', 'get');

        $this->assertEquals('/kategori/økse/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::router()->reset();
    }

    public function testOptionalParameters()
    {
        TestRouter::get('/aviso/legal', 'DummyController@method1');
        TestRouter::get('/aviso/{aviso}', 'DummyController@method1');
        TestRouter::get('/pagina/{pagina}', 'DummyController@method1');
        TestRouter::get('/{pagina?}', 'DummyController@method1');

        TestRouter::debugNoReset('/aviso/optional', 'get');
        $this->assertEquals('/aviso/{aviso}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/pagina/optional', 'get');
        $this->assertEquals('/pagina/{pagina}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/optional', 'get');
        $this->assertEquals('/{pagina?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/avisolegal', 'get');
        $this->assertNotEquals('/aviso/{aviso}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::debugNoReset('/avisolegal', 'get');
        $this->assertEquals('/{pagina?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUri());

        TestRouter::router()->reset();
    }

    public function testSimilarUrls()
    {
        // Match normal route on alias
        TestRouter::resource('/url11', 'DummyController@method1');
        TestRouter::resource('/url1', 'DummyController@method1', ['as' => 'match']);

        TestRouter::debugNoReset('/url1', 'get');

        $this->assertEquals(TestRouter::getUri('match'), TestRouter::getUri());

        TestRouter::router()->reset();
    }

    public function testUrls()
    {
        // Match normal route on alias
        TestRouter::get('/', 'DummyController@method1', ['as' => 'home']);

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

        $this->assertEquals('/gadgets/iphoneinfo/', TestRouter::getUri('gadgets.iphone'));

        $this->assertEquals('/api/phones/create/', TestRouter::getUri('api.phones.create'));

        // Should match /
        $this->assertEquals('/', TestRouter::getUri('home'));

        // Should match /about/
        $this->assertEquals('/about/', TestRouter::getUri('DummyController@about'));

        // Should match /admin/
        $this->assertEquals('/admin/', TestRouter::getUri('DummyController@method2'));

        // Should match /admin/
        $this->assertEquals('/admin/', TestRouter::getUri('admin.home'));

        // Should match /admin/2/
        $this->assertEquals('/admin/2/', TestRouter::getUri('admin.home', ['id' => 2]));

        // Should match /admin/users/
        $this->assertEquals('/admin/users/', TestRouter::getUri('admin.users'));

        // Should match /admin/users/home/
        $this->assertEquals('/admin/users/home/', TestRouter::getUri('admin.users@home'));

        // Should match /cats/
        $this->assertEquals('/cats/', TestRouter::getUri('CatsController'));

        // Should match /cats/view/
        $this->assertEquals('/cats/view/', TestRouter::getUri('CatsController', 'view'));

        // Should match /cats/view/
        //$this->assertEquals('/cats/view/', TestRouter::getUri('CatsController', ['view']));

        // Should match /cats/view/666
        $this->assertEquals('/cats/view/666/', TestRouter::getUri('CatsController@getView', ['666']));

        // Should match /funny/man/
        $this->assertEquals('/funny/man/', TestRouter::getUri('/funny/man'));

        // Should match /?jackdaniels=true&cola=yeah
        $this->assertEquals('/?jackdaniels=true&cola=yeah', TestRouter::getUri('home', null, ['jackdaniels' => 'true', 'cola' => 'yeah']));

        TestRouter::router()->reset();

    }

}