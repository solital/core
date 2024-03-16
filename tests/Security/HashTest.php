<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Security\Hash;

require_once dirname(__DIR__) . '/bootstrap.php';

class HashTest extends TestCase
{
    public function testCrypt()
    {
        $hash = Hash::encrypt('word_to_encrypt');
        $this->assertIsString($hash);
    }

    public function testDecrypt()
    {
        $hash = Hash::encrypt('word_to_encrypt');
        $res = Hash::decrypt($hash)->value();
        $this->assertEquals('word_to_encrypt', $res);
    }

    public function testDecryptIsVaid()
    {
        $hash = Hash::encrypt('word_to_encrypt');
        $res = Hash::decrypt($hash)->isValid();
        $this->assertTrue($res);
    }

    public function testSodium()
    {
        $isset = Hash::checkSodium();
        $this->assertTrue($isset);
    }

    public function testSodiumCrypt()
    {
        $encoded = Hash::sodiumCrypt("word_to_encrypt");
        $this->assertIsString($encoded);
    }

    public function testSodiumDecrypt()
    {
        $key = Hash::getSodiumKey();
        $encoded = Hash::sodiumCrypt("word_to_encrypt", $key);
        $decoded = Hash::sodiumDecrypt($encoded, $key);
        $this->assertIsString($decoded);
    }
}
