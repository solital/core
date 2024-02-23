<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Memorize\Utils;
use Solital\Test\Resource\Memorize\Support\ValueObject;

class UtilsTest extends TestCase
{
    /**
     * @param $string
     * @param $item
     * @dataProvider hashProvider
     */
    public function testStringify($string, $item)
    {
        $this->assertEquals($string, Utils::stringify($item));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testStringifyResource()
    {
        $this->expectException('RuntimeException');
        Utils::stringify(fopen('php://temp', 'r'));
    }

    public function testStringifyLambda()
    {
        $string = Utils::stringify(function () {
            return 1;
        });

        $this->assertEquals('c:'.__FILE__.'-30-32', $string);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testStringifyCustomObject()
    {
        $this->expectException('RuntimeException');
        Utils::stringify(new \DateTime('now'));
    }

    public function hashProvider()
    {
        return [
            'int'           => ['i:15;', 15],
            'float'         => ['d:15.5;', 15.5],
            'string'        => ['s:3:"str";', 'str'],
            'empty string'  => ['s:0:"";', ''],
            'null'          => ['N;', null],
            'false'         => ['b:0;', false],
            'true'          => ['b:1;', true],
            'zero'          => ['i:0;', 0],
            'empty array'   => ['a:', []],
            'array'         => ['a:i:0;-i:1;,i:1;-i:2;,i:2;-i:3;,', [1,2,3]],
            'object'        => ['Solital\Test\Resource\Memorize\Support\ValueObject:eccbc87e4b5ce2fe28308fd9f2a7baf3', new ValueObject(3)],
            'object2'       => ['Solital\Test\Resource\Memorize\Support\ValueObject:a87ff679a2f3e71d9181a67b7542122c', new ValueObject(4)],
            'array objects' => ['a:i:0;-Solital\Test\Resource\Memorize\Support\ValueObject:c4ca4238a0b923820dcc509a6f75849b,i:1;-Solital\Test\Resource\Memorize\Support\ValueObject:c81e728d9d4c2f636f067f89cc14862c,', [new ValueObject(1), new ValueObject(2)]],
            'assoc array'   => ['a:s:1:"a";-i:1;,s:1:"b";-Solital\Test\Resource\Memorize\Support\ValueObject:e4da3b7fbbce2345d7772b0674a318d5,', ['a' => 1, 'b' => new ValueObject(5)]],
        ];
    }


    public function testHash()
    {
        $items = [0, null, false, ''];
        $hashes = array_map(function ($item) { return Utils::hash($item); }, $items);

        $this->assertEquals(count(array_unique($hashes)), 4) ;
    }

    public function testHashObjects()
    {
        $this->assertEquals(Utils::hash(new ValueObject(1)), Utils::hash(new ValueObject(1)));
    }

}

