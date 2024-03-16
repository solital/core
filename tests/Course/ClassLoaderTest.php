<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/ClassLoader/CustomClassLoader.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;

class ClassLoaderTest extends TestCase
{
    public function testCustomClassLoader()
    {
        $result = false;

        TestRouter::setCustomClassLoader(new CustomClassLoader());

        TestRouter::get('/', 'NonExistingClass@classLoader');
        TestRouter::get('/test-closure', function($status) use(&$result) {
            $result = $status;
        });

        $classLoaderClass = TestRouter::debugOutput('/', 'get', false);
        TestRouter::debugOutput('/test-closure');

        $this->assertEquals('Loader', $classLoaderClass);
        $this->assertTrue($result);

        TestRouter::router()->reset();
    }
}