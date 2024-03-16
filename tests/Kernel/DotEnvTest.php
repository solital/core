<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Kernel\Dotenv;
use Solital\Core\Kernel\Exceptions\DotenvException;

require_once dirname(__DIR__) . '/bootstrap.php';

class DotEnvTest extends TestCase
{
    public function testAddEnv()
    {
        $res = Dotenv::add('KEY_TEST', 'value');
        $this->assertTrue($res);
    }

    public function testExistsEnv()
    {
        $res1 = Dotenv::isset('KEY_TEST');
        $res2 = Dotenv::isset('KEY_NOT_EXISTS_TEST');

        $this->assertTrue($res1);
        $this->assertFalse($res2);
    }

    public function testReadEnv()
    {
        $this->assertEquals('value', getenv('KEY_TEST'));
    }

    public function testEditEnv()
    {
        $res = Dotenv::edit('MAIL_DEBUG', 2);
        $this->assertTrue($res);
    }

    public function testEditNotExistEnv()
    {
        $this->expectException(DotenvException::class);
        $res = Dotenv::edit('NOT_EXISTS', 'test');
    }
}