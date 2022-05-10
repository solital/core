<?php

require_once dirname(__DIR__, 3) . "/src/Resource/Helpers/others.php";

use PHPUnit\Framework\TestCase;

class OthersTest extends TestCase
{
    public function testMultiArrayKey()
    {
        $records = [
            [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ]
        ];

        $res = multi_array_value('John', $records);
        $this->assertIsArray($res);
    }

    public function testMappedImplode()
    {
        $records = [
            'id' => 2135,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ];

        $res = mapped_implode(",", $records, "=");
        $this->assertEquals("id=2135,first_name=John,last_name=Doe", $res);
    }
}
