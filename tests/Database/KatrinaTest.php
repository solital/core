<?php

namespace Solital\Test\Database;

require_once dirname(__DIR__) . '/bootstrap.php';

use Katrina\Exceptions\ConnectionException;
use PHPUnit\Framework\TestCase;
use Solital\Core\Kernel\Application;
use Solital\Test\Database\Model\User;

class KatrinaTest extends TestCase
{
    public function testConn()
    {
        Application::connectionDatabase();
        $res = User::connection('mysql')::select()->get();
        $this->assertIsArray($res);
    }

    public function testConnNotExists()
    {
        $this->expectException(ConnectionException::class);
        
        Application::connectionDatabase();
        User::connection('pgsql')::select()->get();
    }
}
