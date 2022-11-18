<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\JSON;

class JSONTest extends TestCase
{
    public function testJsonEncode()
    {
        $array = ["foo", "bar", "baz", "blong"];

        $json = new JSON();
        $res = $json->encode($array);
        $this->assertIsString($res);
    }

    public function testJsonEncodeError()
    {
        //$this->expectException('JsonException');

        $json_encode = "\xB1\x31";

        $json = new JSON();
        $res = $json->encode($json_encode, true);

        $this->assertStringContainsString('json_error', $res);
    }

    public function testJsonEncodeException()
    {
        //$this->expectException('JsonException');

        $json_encode = "\xB1\x31";

        $json = new JSON();
        $json->encode($json_encode, true);
    }

    public function testJsonDecode()
    {
        $json_encode = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

        $json = new JSON();
        $res = $json->decode($json_encode);
        $this->assertIsObject($res);
    }

    public function testJsonDecodeArray()
    {
        $json_encode = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

        $json = new JSON();
        $res = $json->decode($json_encode, true);
        $this->assertIsArray($res);
    }

    public function testJsonDecodeError()
    {
        //$this->expectException('JsonException');

        $json_encode = "{'Organization': 'PHP Documentation Team'}";

        $json = new JSON();
        $res = $json->decode($json_encode, true);

        $this->assertStringContainsString('json_error', $res);
    }
}
