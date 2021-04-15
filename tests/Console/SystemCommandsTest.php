<?php

use Solital\Core\Console\Command\SystemCommands;

class SystemCommandsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test version
     */
    public function testVersion()
    {
        $cmd = new SystemCommands(true);
        $this->assertTrue($cmd->version());
    }

    /**
     * Test Show
     */
    public function testShow()
    {
        $cmd = new SystemCommands(true);
        $this->assertTrue($cmd->show());
    }

    /**
     * Test Routes
     */
    public function testRoutes()
    {
        $cmd = new SystemCommands(true);
        $this->assertTrue($cmd->routes());
    }
}
