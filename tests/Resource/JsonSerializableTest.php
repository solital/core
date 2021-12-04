<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\JsonSerializable;

class JsonSerializableTest extends TestCase
{
    public function testJson()
    {
        $res = json_encode(new JsonSerializable(['foo' => 'bar']));
        $this->assertJson($res);
    }
}
