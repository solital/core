<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Session;

Session::start();

class SessionTest extends TestCase
{
    public function testCreateAndReadSession()
    {
        Session::set('name', 'Solital Framework');
        $res = Session::get('name');
        $this->assertEquals($res, 'Solital Framework');
    }

    public function testHasSession()
    {
        $res = Session::has('name');
        $this->assertTrue($res);
    }

    public function testDeleteSession()
    {
        $res = Session::delete('name');
        $this->assertEmpty($res);
    }

    public function testTakeSession()
    {
        Session::set('email', 'solital@email.com');
        $res = Session::take('email', 'message test');
        $exists = Session::has('email');
        $this->assertEquals($res, 'solital@email.com');
        $this->assertFalse($exists);
    }
}