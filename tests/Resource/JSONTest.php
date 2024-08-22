<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\JSON;

class JSONTest extends TestCase
{
    public function testJsonEncode()
    {
        $array = ["foo", "bar", "baz", "blong"];
        $res = encodeJSON($array);
        $this->assertIsString($res);
    }

    public function testJsonEncodeError()
    {
        $value = "\xB1\x31";
        $res = encodeJSON($value);
        $value = decodeJSON($res, true);
        $this->assertArrayHasKey('json_error', $value);
    }

    public function testJsonDecode()
    {
        $value = '{"a":1,"b":2,"c":3,"d":4,"e":5}';
        $res = decodeJSON($value);
        $this->assertIsObject($res);
    }

    public function testJsonDecodeArray()
    {
        $value = '{"a":1,"b":2,"c":3,"d":4,"e":5}';
        $res = decodeJSON($value, true);
        $this->assertIsArray($res);
    }

    public function testJsonDecodeError()
    {
        $value = "{'Organization': 'PHP Documentation Team'}";
        $res = decodeJSON($value, true);
        $this->assertArrayHasKey('json_error', $res);
    }

    public function testJsonDecodeException()
    {
        $this->expectException('JsonException');
        $value = "{'Organization': 'PHP Documentation Team'}";
        $json = new JSON();
        $json->enableJsonException();
        $json->decode($value, true);
    }
}
