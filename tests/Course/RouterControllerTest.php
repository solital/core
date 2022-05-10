<?php

require_once 'Dummy/DummyController.php';
require_once dirname(__DIR__) . '/TestRouter.php';

use PHPUnit\Framework\TestCase;

class RouterControllerTest extends TestCase
{
    public function testGet()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        // Match normal route on alias
        TestRouter::controller('/url', 'DummyController');

        $response = TestRouter::debugOutput('/url/test', 'get');

        $this->assertEquals('getTest', $response);
    }

    public function testPost()
    {
        // Match normal route on alias
        TestRouter::controller('/url2', 'DummyController');

        $response = TestRouter::debugOutput('/url2/test', 'post');

        $this->assertEquals('postTest', $response);
    }

    public function testPut()
    {
        // Match normal route on alias
        TestRouter::controller('/url3', 'DummyController');

        $response = TestRouter::debugOutput('/url3/test', 'put');

        $this->assertEquals('putTest', $response);
    }
}
