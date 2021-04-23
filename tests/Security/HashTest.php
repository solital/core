<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Security\Hash;

class HashTest extends TestCase
{
    /**
     * Check if lib sodium exists
     */
    public function testLib()
    {
        $isset = Hash::checkSodium();

        $this->assertTrue($isset);
    }

    /**
     * Test crypt
     */
    public function testCrypt()
    {
        $key = Hash::getSodiumKey();
        $encoded = Hash::sodiumCrypt("HashTest!", $key);

        $this->assertIsString($encoded);
    }

    /**
     * Test decrypt
     */
    public function testDecrypt()
    {
        $key = Hash::getSodiumKey();
        $encoded = Hash::sodiumCrypt("HashTest!", $key);
        $decoded = Hash::sodiumDecrypt($encoded, $key);

        $this->assertIsString($decoded);
    }
}
