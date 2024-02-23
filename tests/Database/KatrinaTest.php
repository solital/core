<?php

require_once 'Model/ModelTest.php';
require_once dirname(__DIR__) . '/bootstrap.php';

use Katrina\Exceptions\ConnectionException;
use PHPUnit\Framework\TestCase;
use Solital\Core\Kernel\Application;

class KatrinaTest extends TestCase
{
    public function testConn()
    {
        Application::connectionDatabase();
        $res = ModelTest::connection('mysql')::select()->get();
        $this->assertIsArray($res);
    }

    public function testConnNotExists()
    {
        $this->expectException(ConnectionException::class);
        
        Application::connectionDatabase();
        ModelTest::connection('pgsql')::select()->get();
    }
}
