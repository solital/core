<?php

require_once 'Dummy/ResourceController.php';
require_once dirname(__DIR__) . '/TestRouter.php';

use PHPUnit\Framework\TestCase;

class RouterResourceTest extends TestCase
{
    public function testResourceIndex()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        TestRouter::resource('/resource', 'ResourceController');
        $response = TestRouter::debugOutput('/resource', 'get');

        $this->assertEquals('index', $response);
    }

    public function testResourceGet()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        TestRouter::resource('/resource', 'ResourceController');
        $response = TestRouter::debugOutput('/resource/38', 'get');

        $this->assertEquals('show 38', $response);
    }
    
    public function testResourceStore()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        TestRouter::resource('/resource', ResourceController::class);
        $response = TestRouter::debugOutput('/resource', 'post');

        $this->assertEquals('store', $response);
    }

    public function testResourceCreate()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        TestRouter::resource('/resource', 'ResourceController');
        $response = TestRouter::debugOutput('/resource/create', 'get');

        $this->assertEquals('create', $response);
    }

    public function testResourceDestroy()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        TestRouter::resource('/resource', 'ResourceController');
        $response = TestRouter::debugOutput('/resource/38', 'delete');

        $this->assertEquals('destroy 38', $response);
    }

    public function testResourceEdit()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        TestRouter::resource('/resource', 'ResourceController');
        $response = TestRouter::debugOutput('/resource/38/edit', 'get');

        $this->assertEquals('edit 38', $response);
    }

    public function testResourceUpdate()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        TestRouter::resource('/resource', 'ResourceController');
        $response = TestRouter::debugOutput('/resource/38', 'put');

        $this->assertEquals('update 38', $response);
    }
}
