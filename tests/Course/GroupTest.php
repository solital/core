<?php

use PHPUnit\Framework\TestCase;
use Solital\Test\TestRouter;

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once dirname(__DIR__) . '/bootstrap.php';

class GroupTest extends TestCase
{
    public function testGroupLoad()
    {
        $result = false;

        TestRouter::group(['prefix' => '/group'], function () use(&$result) {
            $result = true;
        });

        try {
            TestRouter::debug('/', 'get');
        } catch(\Exception $e) {

        }

        $this->assertTrue($result);
    }

    public function testNestedGroup()
    {
        TestRouter::group(['prefix' => '/api'], function () {
            TestRouter::group(['prefix' => '/v1'], function () {
                TestRouter::get('/test', 'DummyController@method2');
            });
        });

        TestRouter::debug('/api/v1/test', 'get');
        $this->assertTrue(true);
    }

    public function testMultipleRoutes()
    {
        TestRouter::group(['prefix' => '/api'], function () {
            TestRouter::group(['prefix' => '/v2'], function () {
                TestRouter::get('/test', 'DummyController@method2');
            });
        });

        TestRouter::get('/my/match', 'DummyController@method2');
        TestRouter::group(['prefix' => '/service'], function () {

            TestRouter::group(['prefix' => '/v2'], function () {
                TestRouter::get('/no-match', 'DummyController@method2');
            });

        });

        TestRouter::debug('/my/match', 'get');
        $this->assertTrue(true);
    }

    public function testUrls()
    {
        // Test array name
        TestRouter::get('/my/fancy/url/1', 'DummyController@method2', ['as' => 'fancy1']);

        // Test method name
        TestRouter::get('/my/fancy/url/2', 'DummyController@method2')->setName('fancy2');

        TestRouter::debugNoReset('/my/fancy/url/1');

        $this->assertEquals('/my/fancy/url/1/', TestRouter::getUri('fancy1')->getPath());
        $this->assertEquals('/my/fancy/url/2/', TestRouter::getUri('fancy2')->getPath());

        TestRouter::router()->reset();
    }
}