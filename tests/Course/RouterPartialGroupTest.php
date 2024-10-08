<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Test\TestRouter;

class RouterPartialGroupTest extends TestCase
{
    public function testParameters()
    {   
        $result1 = null;
        $result2 = null;

        TestRouter::partialGroup('/{param1}/{param2}', function ($param1 = null, $param2 = null) use (&$result1, &$result2) {
            $result1 = $param1;
            $result2 = $param2;

            TestRouter::get('/', 'DummyController@method3');
        });

        TestRouter::debug('/param1/param2', 'get');

        $this->assertEquals('param1', $result1);
        $this->assertEquals('param2', $result2);
    }
}