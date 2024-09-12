<?php

use Solital\Core\Exceptions\RuntimeException;
use Solital\Core\Http\Exception\NotFoundHttpException;
use Solital\Core\Http\Request;
use Solital\Test\TestRouter;

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Exception/ExceptionHandlerException.php';
require_once dirname(__DIR__) . '/bootstrap.php';

class RouterCallbackExceptionHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testCallbackExceptionHandler()
    {
        $this->expectException(RuntimeException::class);

        // Match normal route on alias
        TestRouter::get('/my-new-url', 'DummyController@method2');
        TestRouter::get('/my-url', 'DummyController@about');

        TestRouter::error(function (Request $request, \Exception $exception) {
            throw new RuntimeException();
        });

        TestRouter::debug('/404-url');
    }

    public function testExceptionHandlerCallback() {

        TestRouter::group(['prefix' => null], function() {
            TestRouter::get('/', function() {
                return 'Hello world';
            });

            TestRouter::get('/not-found', 'DummyController@method1');
            TestRouter::error(function(Request $request, \Exception $exception) {
                if($exception instanceof NotFoundHttpException && $exception->getCode() === 404) {
                    return $request->setRewriteCallback(static function() {
                        return 'success';
                    });
                }
            });
        });

        $result = TestRouter::debugOutput('/thisdoes-not/existssss', 'get');
        $this->assertEquals('success', $result);
    }
}