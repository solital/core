<?php

require_once 'Dummy/DummyMiddleware.php';
require_once 'Dummy/DummyController.php';
require_once 'Dummy/Handler/ExceptionHandler.php';
require_once 'Dummy/Security/SilentTokenProvider.php';
require_once 'Dummy/Managers/TestBootManager.php';
require_once dirname(__DIR__) . '/TestRouter.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Course\Event\EventArgument;
use Solital\Core\Course\Route\LoadableRoute;
use Solital\Core\Course\Handlers\EventHandler;
use Solital\Core\Http\Middleware\BaseCsrfVerifier;

class EventHandlerTest extends TestCase
{
    public function testAllEventTriggered()
    {
        $_SERVER["REQUEST_METHOD"] = 'get';
        $_SERVER["REQUEST_URI"] = '/';

        $events = EventHandler::$events;

        // Remove the all event
        unset($events[\array_search(EventHandler::EVENT_ALL, $events, true)]);

        $eventHandler = new EventHandler();
        $eventHandler->register(EventHandler::EVENT_ALL, function (EventArgument $arg) use (&$events) {
            $key = \array_search($arg->getEventName(), $events, true);
            unset($events[$key]);
        });

        TestRouter::addEventHandler($eventHandler);
        TestRouter::get('/', 'DummyController@method2')->name('home');

        // Trigger findRoute
        TestRouter::router()->findRoute('home');

        // Trigger getUrl
        TestRouter::router()->getUri('home');

        // Add csrf-verifier
        $csrfVerifier = new BaseCsrfVerifier();
        $csrfVerifier->setTokenProvider(new SilentTokenProvider());
        TestRouter::csrfVerifier($csrfVerifier);

        // Add boot-manager
        TestRouter::addBootManager(new TestBootManager(['/test',], '/'));

        // Start router
        TestRouter::debug('/');

        $this->assertEquals($events, $events);
    }

    public function testAllEvent()
    {
        $status = false;

        $eventHandler = new EventHandler();
        $eventHandler->register(EventHandler::EVENT_ALL, function (EventArgument $arg) use (&$status) {
            $status = true;
        });

        TestRouter::addEventHandler($eventHandler);
        TestRouter::get('/2', 'DummyController@method2');
        TestRouter::debug('/2');

        // All event should fire for each other event
        $this->assertEquals(true, $status);
    }

    public function testPrefixEvent()
    {
        $status = false;

        TestRouter::get('/local-path', function () use (&$status) {
            $status = true;
        });

        TestRouter::debug('/local-path');
        
        $eventHandler = new EventHandler();
        $eventHandler->register(EventHandler::EVENT_ADD_ROUTE, function (EventArgument $arg) use (&$status) {
            if ($arg->route instanceof LoadableRoute) {
                $arg->route->prependUrl('/local-path');
            }
        });

        TestRouter::addEventHandler($eventHandler);
        $this->assertTrue($status);
    }
}
