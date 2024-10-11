<?php

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Session;
use Solital\Core\Session\SessionConfiguration;

class SessionTest extends TestCase
{
    private string $storage = __DIR__ . DIRECTORY_SEPARATOR . "storage";

    public function setUp(): void
    {
        if (!is_dir($this->storage)) mkdir($this->storage);

        $session = new SessionConfiguration();
        $session->setSavePath($this->storage);
        $session->start();
    }
    
    public function testCreateAndReadSession()
    {
        Session::set('name', 'Solital Framework');
        $res = Session::get('name');
        $this->assertEquals($res, 'Solital Framework');

        session('name', 'Solital Framework');
        $res = session('name');
        $this->assertEquals($res, 'Solital Framework');

        $res = Session::has('name');
        $this->assertTrue($res);
    }

    public function testDeleteSession()
    {
        $res = Session::delete('name');
        $this->assertTrue($res);

        $res = session('name', delete: true);
        $this->assertTrue($res);
    }

    public function testTakeSession()
    {
        Session::set('email', 'solital@email.com');
        $res = Session::take('email', 'message test');
        $exists = Session::has('email');
        $this->assertEquals($res, 'solital@email.com');
        $this->assertFalse($exists);

        session('email', 'solital@email.com');
        $res = session('email', 'message test', take: true);
        $exists = Session::has('email');
        $this->assertEquals($res, 'solital@email.com');
        $this->assertFalse($exists);
    }

    public function __destruct()
    {
        array_map(
            'unlink',
            array_filter((array) array_merge(glob($this->storage . "/*")))
        );
    }
}