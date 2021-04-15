<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Exception/ExceptionHandlerException.php';

use Solital\Core\Http\Request;

class RouterCallbackExceptionHandlerTest extends \PHPUnit\Framework\TestCase
{

    public function testCallbackExceptionHandler()
    {
        $this->expectException(ExceptionHandlerException::class);

        // Match normal route on alias
        TestRouter::get('/my-new-url', 'DummyController@method2');
        TestRouter::get('/my-url', 'DummyController@method1');

        TestRouter::error(function (Request $request, \Exception $exception) {
            throw new ExceptionHandlerException();
        });

        TestRouter::debugNoReset('/404-url', 'get');
        TestRouter::router()->reset();

        $this->assertTrue(true);
    }

}