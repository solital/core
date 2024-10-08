<?php

namespace Solital\Test\Auth;

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Auth\Password;
use Solital\Core\Kernel\Application;

class PasswordTest extends TestCase
{
    public function testPassword()
    {
        $pass = new Password();
        $hash = $pass->create('solital');
        $this->assertIsString($hash);
    }

    public function testPasswordVerify()
    {
        $pass = new Password();
        $hash = $pass->create('solital');
        $res = $pass->verify('solital', $hash);
        $this->assertTrue($res);
    }

    public function testNeedsHash()
    {
        $pass = new Password();
        $res = $pass->needsRehash('solital', '$argon2i$v=19$m=65536,t=4,p=1$S0J6dTRQLi9JNlc1MnRwVA$QIMBViFqDQnC0RAbVojO/iCxZnsEaLeBFhFIYSwrvps');
        $this->assertIsString($res);
    }

    public function testNoNeedsHash()
    {
        $pass = new Password();
        $res = $pass->needsRehash('solital', '$2y$12$/Ej0/MmVoQ97O1SV/dqq1O/SUkj3sLcx.lI7vHeBVkdunq2YCheTC');
        $this->assertFalse($res);
    }

    public function testHelpers()
    {
        Application::getInstance();
        
        $hash = pass_hash('solital');
        $res = pass_verify('solital', $hash);
        $this->assertTrue($res);
    }
}
