<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use Solital\Test\Course\Dummy\Managers\FindUrlBootManager;
use Solital\Test\Course\Dummy\Managers\TestBootManager;
use Solital\Test\TestRouter;
use PHPUnit\Framework\TestCase;

class BootManagerTest extends TestCase
{
    public function testBootManagerRoutes()
    {   
        $result = false;

        TestRouter::get('/', function () use (&$result) {
            $result = true;
        });
        TestRouter::get('/about', 'DummyController@method2');
        TestRouter::get('/contact', 'DummyController@method3');

        // Add boot-manager
        TestRouter::addBootManager(new TestBootManager([
            '/con'     => '/about',
            '/contact' => '/',
        ]));

        TestRouter::debug('/contact');

        $this->assertTrue($result);
    }

    public function testFindUrlFromBootManager()
    {
        TestRouter::get('/', 'DummyController@method2');
        TestRouter::get('/about', 'DummyController@about')->name('about');
        TestRouter::get('/contact', 'DummyController@contact')->name('contact');

        $result = false;
        $boot_manager = new FindUrlBootManager($result);

        // Add boot-manager
        TestRouter::addBootManager($boot_manager);
        TestRouter::debugNoReset('/');
        $this->assertTrue($boot_manager->getResult());
    }
}