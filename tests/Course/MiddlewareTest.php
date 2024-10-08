<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';
require_once 'Dummy/Exception/MiddlewareLoadedException.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Test\TestRouter;

class MiddlewareTest extends TestCase
{
    public function testMiddlewareFound()
    {
        $this->expectException(MiddlewareLoadedException::class);

        TestRouter::group(['exceptionHandler' => 'ExceptionHandler'], function () {
            TestRouter::get('/my/test/url1', 'DummyController@method2', ['middleware' => 'DummyMiddleware']);
        });

        TestRouter::debug('/my/test/url1', 'get');
    }

    public function testNestedMiddlewareDontLoad()
    {
        TestRouter::group(['exceptionHandler' => 'ExceptionHandler', 'middleware' => 'DummyMiddleware'], function () {
            TestRouter::get('/middleware', 'DummyController@method2');
        });

        TestRouter::get('/my/test/url2', 'DummyController@method2');
        TestRouter::debug('/my/test/url2', 'get');
        $this->assertTrue(true);
    }
}