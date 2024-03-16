<?php

require_once 'Dummy/DummyController.php';
require_once 'Dummy/Middleware/IpRestrictMiddleware.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use Monolog\Test\TestCase;
use Solital\Core\Http\Exception\HttpException;

class CustomMiddlewareTest extends TestCase
{
    public function testIpBlock()
    {
        $this->expectException(HttpException::class);

        global $_SERVER;

        // Test exact ip

        $_SERVER['remote-addr'] = '5.5.5.5';

        TestRouter::group(['middleware' => IpRestrictMiddleware::class], function () {
            TestRouter::get('/fail', 'DummyController@method2');
        });

        TestRouter::debug('/fail');

        // Test ip-range

        $_SERVER['remote-addr'] = '8.8.4.4';

        TestRouter::router()->reset();

        TestRouter::group(['middleware' => IpRestrictMiddleware::class], function () {
            TestRouter::get('/fail', 'DummyController@method2');
        });

        TestRouter::debug('/fail');
    }

    public function testIpSuccess()
    {
        global $_SERVER;

        // Test ip that is not blocked

        $_SERVER['remote-addr'] = '6.6.6.6';

        TestRouter::router()->reset();

        TestRouter::group(['middleware' => IpRestrictMiddleware::class], function () {
            TestRouter::get('/success', 'DummyController@method2');
        });

        TestRouter::debug('/success');

        // Test ip in whitelist

        $_SERVER['remote-addr'] = '8.8.2.2';

        TestRouter::router()->reset();

        TestRouter::group(['middleware' => IpRestrictMiddleware::class], function () {
            TestRouter::get('/success', 'DummyController@method2');
        });

        TestRouter::debug('/success');

        $this->assertTrue(true);
    }
}
