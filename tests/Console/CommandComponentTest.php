<?php

use Solital\Core\Console\Command\Commands;

class CommandComponentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Create a CSS file test
     */
    public function testAddCss()
    {
        $cmd = (new Commands(true))->css("style");
        $this->assertTrue($cmd->createComponent());
    }

    /**
     * Create a JS file test
     */
    public function testAddJs()
    {
        $cmd = (new Commands(true))->js("script");
        $this->assertTrue($cmd->createComponent());
    }

    /**
     * Remove a CSS file test
     */
    public function testRemoveCss()
    {
        $cmd = (new Commands(true))->css("style");
        $this->assertTrue($cmd->removeComponent());
    }

    /**
     * Remove a JS file test
     */
    public function testRemoveJs()
    {
        $cmd = (new Commands(true))->js("script");
        $this->assertTrue($cmd->removeComponent());
    }
}
