<?php

use PHPUnit\Framework\TestCase;
use Solital\Test\Course\Dummy\UserTest;
use Solital\Core\Course\Event\EventDispatcher;
use Solital\Core\Course\Event\ListenerProvider;

class EventPsr14Test extends TestCase
{
    /**
     * @test
     */
    public function eventTest()
    {
        $low = "";
        $normal = "";
        $high = "";

        $provider = new ListenerProvider();
        $event = new EventDispatcher($provider);

        $user = new UserTest();

        $provider->addListener(function (UserTest $user) use (&$high) {
            $high = $user->testHigh();
        }, 3);

        $provider->addListener(function (UserTest $user) use (&$low) {
            $low = $user->testLow();
        }, 1);

        $provider->addListener(function (UserTest $user) use (&$normal) {
            $normal = $user->testNormal();
        }, 2);

        $event->dispatch($user);

        $this->assertEquals($high, "Running High...");
        $this->assertEquals($normal, "Running Normal...");
        $this->assertEquals($low, "Running Low...");
    }
}
