<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Auth\Auth;
use Solital\Core\Security\Hash;

class AuthTest extends TestCase
{
    /**
     * Test Auth Sodium
     */
    public function testAuth()
    {
        $key = Hash::getSodiumKey();
        $encoded = Auth::sodium('password', $key);

        $this->assertIsString($encoded);
    }

    /**
     * Test Auth Sodium Verify
     */
    public function testAuthVerify()
    {
        $key = Hash::getSodiumKey();
        $encoded = Auth::sodium('password', $key);
        $decoded = Auth::sodiumVerify($encoded, 'password', $key);

        $this->assertTrue($decoded);
    }
}
