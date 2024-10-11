<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Cookie;

class CookieTest extends TestCase
{
    public function testCookie()
    {
        $name = 'test_cookie';
        $value = '31d4d96e407aad42';
        
        Cookie::setcookie($name, $value);
        $this->assertTrue(Cookie::exists($name));
        $this->assertSame($value, Cookie::get($name));

        Cookie::unset($name);
        $this->assertFalse(Cookie::exists($name));

        $this->expectException(\Exception::class);
        new Cookie('');
    }
}
