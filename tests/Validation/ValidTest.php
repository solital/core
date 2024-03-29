<?php

require_once dirname(__DIR__, 2) . "/src/Resource/Helpers/others.php";

use PHPUnit\Framework\TestCase;
use Solital\Core\Validation\Valid;

class ValidTest extends TestCase
{
    /**
     * Test validation email
     */
    public function testEmail()
    {
        $res = Valid::email('solital@email.com');
        $this->assertIsString($res);
    }

    /**
     * Test validation number
     */
    public function testNumber()
    {
        $res = Valid::number(20.5);
        $this->assertIsNumeric($res);
    }

    /**
     * Test validation base64
     */
    public function testbase64()
    {
        $hash = base64_encode("test");

        $res = Valid::isBase64($hash);
        $this->assertTrue($res);
    }

    /**
     * Test validation identical
     */
    public function testIdentical()
    {
        $res = Valid::identical("foo", "foo");
        $this->assertTrue($res);
    }
}
