<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\JsonSerializable;

class JsonSerializableTest extends TestCase
{
    public function testJsonSerializable()
    {
        $res = json_encode(new JsonSerializable(['foo' => 'bar']));
        $this->assertJson($res);
    }
}
