<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';
require_once 'Dummy/Security/SilentTokenProvider.php';
require_once 'Dummy/Managers/TestBootManager.php';

use Solital\Core\Course\Event\EventArgument;
use Solital\Core\Course\Route\LoadableRoute;
use Solital\Core\Course\Handlers\EventHandler;
use Solital\Core\Http\Middleware\BaseCsrfVerifier;

class EventHandlerTest extends \PHPUnit\Framework\TestCase
{

    public function testAllEventTriggered()
    {
        $events = EventHandler::$events;

        // Remove the all event
        unset($events[\array_search(EventHandler::EVENT_ALL, $events, true)]);

        $eventHandler = new EventHandler();
        $eventHandler->register(EventHandler::EVENT_ALL, function (EventArgument $arg) use (&$events) {
            $key = \array_search($arg->getEventName(), $events, true);
            unset($events[$key]);
        });

        TestRouter::addEventHandler($eventHandler);

        // Add rewrite
        /* TestRouter::error(function (Request $request, \Exception $error) {

            // Trigger rewrite
            $request->setRewriteUrl('/');

        }); */

        TestRouter::get('/', 'DummyController@method1')->name('home');

        // Trigger findRoute
        TestRouter::router()->findRoute('home');

        // Trigger getUrl
        TestRouter::router()->getUri('home');

        // Add csrf-verifier
        $csrfVerifier = new BaseCsrfVerifier();
        $csrfVerifier->setTokenProvider(new SilentTokenProvider());
        TestRouter::csrfVerifier($csrfVerifier);

        // Add boot-manager
        TestRouter::addBootManager(new TestBootManager([
            '/test',
        ], '/'));

        // Start router
        TestRouter::debug('/non-existing');

        $this->assertEquals($events, []);
    }

    public function testAllEvent()
    {

        $status = false;

        $eventHandler = new EventHandler();
        $eventHandler->register(EventHandler::EVENT_ALL, function (EventArgument $arg) use (&$status) {
            $status = true;
        });

        TestRouter::addEventHandler($eventHandler);

        TestRouter::get('/', 'DummyController@method1');
        TestRouter::debug('/');

        // All event should fire for each other event
        $this->assertEquals(true, $status);
    }

    public function testPrefixEvent()
    {

        $eventHandler = new EventHandler();
        $eventHandler->register(EventHandler::EVENT_ADD_ROUTE, function (EventArgument $arg) use (&$status) {

            if ($arg->route instanceof LoadableRoute) {
                $arg->route->prependUrl('/local-path');
            }

        });

        TestRouter::addEventHandler($eventHandler);

        $status = false;

        TestRouter::get('/', function () use (&$status) {
            $status = true;
        });

        TestRouter::debug('/local-path');

        $this->assertTrue($status);

    }

}