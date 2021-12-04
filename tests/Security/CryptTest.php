<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Security\Encrypt\Crypt;

class CryptTest extends TestCase
{
    public function testCrypt()
    {
        $secretKey = 'ec3a85570a2b2122ad835984ba12e91f';
        $iv = base64_decode('hEHLyH4Irwqvnl8uJpHrnQ==');
        $password = "crypt";

        $crypt = new Crypt($secretKey, $iv);
        #$crypt->setCipherMethod("aes-128-cbc");
        $this->assertEquals($iv, $crypt->iv());

        $encryptedPassword = $crypt->encrypt($password);

        $this->assertEquals('Huv3VJXH2uYT9jCobEgyJQ==', $encryptedPassword);

        $decryptedPassword = $crypt->decrypt($encryptedPassword);
        $this->assertEquals($password, $decryptedPassword);
    }

    public function testGeneratingIV()
    {
        $secretKey = 'ec3a85570a2b2122ad835984ba12e91f';
        $password = "crypt";

        $crypt = new Crypt($secretKey);
        $iv = $crypt->iv();
        $this->assertEquals(16, strlen($iv));
    }

    public function testGenerateKey()
    {
        $key = Crypt::generateKey();
        $this->assertEquals(1024, strlen($key));
    }
}
