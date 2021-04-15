<?php

use Solital\Core\Console\Command\Commands;

class CommandResourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Create a new View
     */
    public function testAddView()
    {
        $cmd = (new Commands(true))->view("viewTest");
        $this->assertTrue($cmd->createResource());
    }

    /**
     * Remove a View
     */
    public function testRemoveView()
    {
        $cmd = (new Commands(true))->view("viewTest");
        $this->assertTrue($cmd->removeResource());
    }

    /**
     * Create a new Controller
     */
    public function testAddController()
    {
        $cmd = (new Commands(true))->controller("TestController");
        $this->assertTrue($cmd->createResource());
    }

    /**
     * Remove a Controller
     */
    public function testRemoveController()
    {
        $cmd = (new Commands(true))->controller("TestController");
        $this->assertTrue($cmd->removeResource());
    }

    /**
     * Create a new Model
     */
    public function testAddModel()
    {
        $cmd = (new Commands(true))->model("TestModel");
        $this->assertTrue($cmd->createResource());
    }

    /**
     * Remove a Model
     */
    public function testRemoveModel()
    {
        $cmd = (new Commands(true))->model("TestModel");
        $this->assertTrue($cmd->removeResource());
    }

    /**
     * Create a new File
     */
    public function testAddFile()
    {
        $cmd = (new Commands(true))->file("TestFile.php", "./");
        $this->assertTrue($cmd->createResource());
    }

    /**
     * Remove a File
     */
    public function testRemoveFile()
    {
        $cmd = (new Commands(true))->file("TestFile.php", "./");
        $this->assertTrue($cmd->removeResource());
    }
}
