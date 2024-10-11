<?php

namespace Solital\Test\Auth;

require_once dirname(__DIR__) . '/bootstrap.php';

use SecurePassword\SecurePassword;
use PHPUnit\Framework\TestCase;
use Solital\Core\Auth\Password;
use Solital\Core\Kernel\Application;

class PasswordTest extends TestCase
{
    public function testPassword()
    {
        $pass = new Password();
        $hash = $pass->create('solital');
        $this->assertIsString($hash);
    }

    public function testPasswordVerify()
    {
        $pass = new Password();
        $hash = $pass->create('solital');
        $res = $pass->verify('solital', $hash);
        $this->assertTrue($res);
    }

    public function testNeedsHash()
    {
        $hash_argon2 = '$argon2i$v=19$m=65536,t=4,p=1$Y2Q2aTh3V3dNVEFueHRaeA$rvpYC9soRNXZgPcem0yRzvGpldNrEBWH5RMZk6sFrMM';
        $pass = new Password();
        $new_hash = $pass->needsRehash("solital", $hash_argon2);
        $this->assertIsString($new_hash);
    }

    public function testNoNeedsHash()
    {
        $value = "solital";
        $hash_bcrypt = (new SecurePassword())->useBcrypt()->createHash($value)->getHash();

        $pass = new Password();
        $res = $pass->needsRehash($value, $hash_bcrypt);
        $this->assertFalse($res);
    }

    public function testHelpers()
    {
        Application::getInstance();

        $hash = pass_hash('solital');
        $res = pass_verify('solital', $hash);
        $this->assertTrue($res);
    }

    public function testPasswordPolicy()
    {
        $customErrorMessages = [
            'min_length' => 'Your password is too short. It must be at least 15 characters long.',
            'uppercase' => 'Your password must contain at least one uppercase letter.',
        ];

        $password = new Password();
        $password->setMinimumLength(15);
        $password->requireUppercase();
        $password->requireDigits();
        $password->requireSpecialChars();
        $password->setSpecialChars('!@#%');
        $password->setCustomErrorMessages($customErrorMessages);
        $result = $password->validatePassword('strongpassword');

        $expect = [
            "Your password is too short. It must be at least 15 characters long.",
            "Your password must contain at least one uppercase letter.",
            "Password must contain at least one digit.",
            "Password must contain at least one special character."
        ];

        $this->assertSame($expect, $result);
    }
}
