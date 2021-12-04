<?php

use Solital\Core\Auth\Auth;
use PHPUnit\Framework\TestCase;
use Solital\Core\Security\Hash;

class AuthTest extends TestCase
{
    /**
     * Test Auth Sodium
     */
    public function testSodiumAuth()
    {
        $key = Hash::getSodiumKey();
        $encoded = Auth::sodium('password', $key);

        $this->assertIsString($encoded);
    }

    /**
     * Test Auth Sodium Verify
     */
    public function testSodiumAuthVerify()
    {
        $key = Hash::getSodiumKey();
        $encoded = Auth::sodium('password', $key);
        $decoded = Auth::sodiumVerify($encoded, 'password', $key);

        $this->assertTrue($decoded);
    }
}
