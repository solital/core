<?php

require_once 'Dummy/DummyController.php';
require_once 'Dummy/Exception/ResponseException.php';
require_once 'Dummy/Handler/ExceptionHandlerFirst.php';
require_once 'Dummy/Handler/ExceptionHandlerSecond.php';
require_once 'Dummy/Handler/ExceptionHandlerThird.php';
require_once 'Dummy/Middleware/RewriteMiddleware.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Course\Route\RouteUrl;

class RouterRewriteTest extends TestCase
{
    public function testRewriteUrlFromRoute()
    {
        TestRouter::get('/old', function () {
            TestRouter::request()->setRewriteUrl('/new0');
        });

        TestRouter::get('/new0', function () {
            echo 'ok';
        });

        TestRouter::get('/new1', function () {
            echo 'ok';
        });

        TestRouter::get('/new2', function () {
            echo 'ok';
        });

        $output = TestRouter::debugOutput('/old');
        $this->assertEquals('ok', $output);
    }

    public function testRewriteCallbackFromRoute()
    {
        TestRouter::get('/old2', function () {
            TestRouter::request()->setRewriteUrl('/new3');
        });

        TestRouter::get('/new3', function () {
            echo 'ok';
        });

        TestRouter::get('/new4', function () {
            echo 'fail';
        });

        TestRouter::get('/new5', function () {
            echo 'fail';
        });

        $output = TestRouter::debugOutput('/old2');
        TestRouter::router()->reset();
        $this->assertEquals('ok', $output);
    }

    public function testRewriteRouteFromRoute()
    {
        TestRouter::get('/match', function () {
            TestRouter::request()->setRewriteRoute(new RouteUrl('/match', function () {
                echo 'ok';
            }));
        });

        TestRouter::get('/old3', function () {
            echo 'fail';
        });

        TestRouter::get('/old4', function () {
            echo 'fail';
        });

        TestRouter::get('/new6', function () {
            echo 'fail';
        });

        $output = TestRouter::debugOutput('/match');
        TestRouter::router()->reset();
        $this->assertEquals('ok', $output);
    }

    public function testMiddlewareRewrite()
    {
        TestRouter::group(['middleware' => 'RewriteMiddleware'], function () {
            TestRouter::get('/', function () {
                echo 'ok';
            });

            TestRouter::get('no/match', function () {
                echo 'ok';
            });
        });

        $output = TestRouter::debugOutput('/');
        $this->assertEquals('middleware-ok', $output);
    }
}
