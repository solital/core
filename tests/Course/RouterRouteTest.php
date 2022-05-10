<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Exception/ExceptionHandlerException.php';
require_once dirname(__DIR__) . '/TestRouter.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Exceptions\RuntimeException;

/**
 * CORRIGIR
 */
class RouterRouteTest extends TestCase
{
    /**
     * Issue #421: Incorrectly optional character in route
     *
     * @throws Exception
     */
    /* public function testOptionalCharacterRoute()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        $result = false;

        TestRouter::get('/api/v1/users/{userid}/projects/{id}/pages/{pageid?}', function () use (&$result) {
            $result = true;
        });

        TestRouter::debug('/api/v1/users/1/projects/8399421535/pages/43', 'get');

        $this->assertTrue($result);
    } */

    public function testMultiParam()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        $result = false;
        TestRouter::get('/test1/{param1}/{param2}', function ($param1, $param2) use (&$result) {

            if ($param1 === 'param1' && $param2 === 'param2') {
                $result = true;
            }
        });

        TestRouter::debug('/test1/param1/param2', 'get');

        $this->assertTrue($result);
    }

    public function testNotFound()
    {
        $this->expectException(RuntimeException::class);
        TestRouter::get('/non-existing-path', 'DummyController@method2');
        TestRouter::debug('/test2-param1-param2', 'post');
    }

    public function testGet()
    {
        TestRouter::get('/my/test/url', 'DummyController@method2');
        TestRouter::debug('/my/test/url', 'get');

        $this->assertTrue(true);
    }

    public function testPost()
    {
        TestRouter::post('/my/test/url2', 'DummyController@method2');
        TestRouter::debug('/my/test/url2', 'post');

        $this->assertTrue(true);
    }

    public function testPut()
    {
        TestRouter::put('/my/test/url3', 'DummyController@method2');
        TestRouter::debug('/my/test/url3', 'put');

        $this->assertTrue(true);
    }

    public function testDelete()
    {
        TestRouter::delete('/my/test/url4', 'DummyController@method2');
        TestRouter::debug('/my/test/url4', 'delete');

        $this->assertTrue(true);
    }

    public function testMethodNotAllowed()
    {
        TestRouter::get('/my/test/url5', 'DummyController@method2');

        try {
            TestRouter::debug('/my/test/url5', 'post');
        } catch (\Exception $e) {
            $this->assertEquals(403, $e->getCode());
        }
    }

    public function testSimpleParam()
    {
        TestRouter::get('/test/{param}', 'DummyController@param');
        $response = TestRouter::debugOutput('/test/param1', 'get');

        $this->assertEquals('param1', $response);
    }

    public function testPathParamRegex()
    {
        TestRouter::get('/{lang}/productscategories/{name}', 'DummyController@params', ['where' => ['lang' => '[a-z]+', 'name' => '[A-Za-z0-9\-]+']]);
        $response = TestRouter::debugOutput('/it/productscategories/system', 'get');

        $this->assertEquals('it, system', $response);
    }

    /* public function testDomainAllowedRoute() EEEEERRRRRRR
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        $result = false;
        TestRouter::request()->setHost('hello.world.com');

        /* $host = TestRouter::request()->getHost();
        var_dump($host);exit; 

        TestRouter::group(['domain' => '{subdomain}.world.com'], function () use (&$result) {
            TestRouter::get('/test1', function ($subdomain = null) use (&$result) {
                $result = ($subdomain === 'hello');
            });
        });

        var_dump($result);exit;

        TestRouter::debug('/test1', 'get');

        $this->assertTrue($result);
    } 

    public function testDomainNotAllowedRoute()
    {
        TestRouter::request()->setHost('other.world.com');

        $result = false;

        TestRouter::group(['domain' => '{subdomain}.world.com'], function () use (&$result) {
            TestRouter::get('/test2', function ($subdomain = null) use (&$result) {
                $result = ($subdomain === 'hello');
            });
        });

        TestRouter::debug('/test2', 'get');

        $this->assertFalse($result);
    }*/

    public function testRegEx()
    {
        TestRouter::get('/my/{path}', 'DummyController@method1')->where(['path' => '[a-zA-Z\-]+']);
        TestRouter::debug('/my/custom-path', 'get');

        $this->assertTrue(true);
    }

    /* public function testParameterDefaultValue() EEERRRRRRR
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        $defaultVariable = null;

        TestRouter::get('/my/{path?}', function ($path = 'working') use (&$defaultVariable) {
            $defaultVariable = $path;
        });

        var_dump($defaultVariable);exit;

        TestRouter::debug('/my/');

        $this->assertEquals('working', $defaultVariable);
    } */

    public function testDefaultParameterRegex()
    {
        TestRouter::get('/my2/{path}', 'DummyController@method1', ['defaultParameterRegex' => '[\w\-]+']);
        $output = TestRouter::debugOutput('/my2/custom-regex1', 'get');

        $this->assertEquals('custom-regex1', $output);
    }

    public function testDefaultParameterRegexGroup()
    {
        TestRouter::group(['defaultParameterRegex' => '[\w\-]+'], function () {
            TestRouter::get('/my3/{path}', 'DummyController@method1');
        });

        $output = TestRouter::debugOutput('/my3/custom-regex2', 'get');

        $this->assertEquals('custom-regex2', $output);
    }
}
