<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyModel.php';
require_once 'Dummy/DummyModelArg.php';
require_once 'Dummy/DummyContact.php';

use Solital\Core\Course\Container\Container;

class DependencyInjectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * testDependencyInjection
     */
    public function testDependencyInjection()
    {
        $container = new Container();
        $container->set('model', function() {
            return new DummyModel();
        });

        $dep = $container->get('model');
        $res = $dep->run();

        $this->assertEquals('Running...', $res);
    }

    /**
     * testDependencyInjectionArg
     */
    public function testDependencyInjectionArg()
    {
        $container = new Container();
        $container->set('contact', function($args) {
            return new DummyContact($args);
        }, 'ContactArgConstruct');

        $dep = $container->get('contact');
        $res = $dep->call();

        $this->assertEquals('Contact', $res);
    }

    /**
     * testDependencyInjectionMultiArg
     */
    public function testDependencyInjectionMultiArg()
    {
        $multi = ['ContactArgConstruct1', 'ContactArgConstruct2', 'ContactArgConstruct3'];

        $container = new Container();
        $container->set('modelArg', function($args) {
            return new DummyModelArg($args);
        }, $multi);

        $dep = $container->get('modelArg');
        $res = $dep->execute();

        $this->assertIsArray($res);
    }

    /**
     * testHasDependency
     */
    public function testHasDependency()
    {
        $container = new Container();
        $container->set('user', function() {
            return new DummyModel();
        });

        $container->get('user');
        $dep = $container->has('user');

        $this->assertTrue($dep);
    }
}