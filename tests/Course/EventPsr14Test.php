<?php

use PHPUnit\Framework\TestCase;
use Solital\Test\Course\Dummy\User;
use Solital\Core\Course\Event\Psr14\EventDispatcher;
use Solital\Core\Course\Event\Psr14\ListenerProvider;

class EventPsr14Test extends TestCase
{
    public function testPsr14()
    {
        $low = "";
        $normal = "";
        $high = "";

        $provider = new ListenerProvider();
        $event = new EventDispatcher($provider);

        $user = new User();

        $provider->addListener(function (User $user) use (&$high) {
            $high = $user->testHigh();
        }, 3);

        $provider->addListener(function (User $user) use (&$low) {
            $low = $user->testLow();
        }, 1);

        $provider->addListener(function (User $user) use (&$normal) {
            $normal = $user->testNormal();
        }, 2);

        $event->dispatch($user);

        $this->assertEquals("Running High...", $high);
        $this->assertEquals("Running Normal...", $normal);
        $this->assertEquals("Running Low...", $low);
    }
}
