<?php

namespace Solital\Test\Security;

use PHPUnit\Framework\TestCase;
use Solital\Core\Kernel\Application;
use Solital\Core\Security\Hash;

require_once dirname(__DIR__) . '/bootstrap.php';

class HashTest extends TestCase
{
    public function setUp(): void
    {
        Application::getInstance();
    }

    public function testCrypt()
    {
        $hash = Hash::encrypt('word_to_encrypt');
        $this->assertIsString($hash);

        $hash = encrypt('word_to_encrypt');
        $this->assertIsString($hash);
    }

    public function testDecrypt()
    {
        $hash = Hash::encrypt('word_to_encrypt');
        $res = Hash::decrypt($hash)->value();
        $this->assertEquals('word_to_encrypt', $res);

        $hash = encrypt('word_to_encrypt');
        $res = decrypt($hash)->value();
        $this->assertEquals('word_to_encrypt', $res);
    }

    public function testDecryptIsValid()
    {
        $hash = Hash::encrypt('word_to_encrypt');
        $res = Hash::decrypt($hash)->isValid();
        $this->assertTrue($res);

        $hash = encrypt('word_to_encrypt');
        $res = decrypt($hash)->isValid();
        $this->assertTrue($res);
    }

    public function testDecryptIsNotValid()
    {
        $hash = Hash::encrypt('word_to_encrypt', '-1 hour');
        $res = Hash::decrypt($hash)->isValid();
        $this->assertFalse($res);

        $hash = encrypt('word_to_encrypt', '-1 hour');
        $res = decrypt($hash)->isValid();
        $this->assertFalse($res);
    }
}
