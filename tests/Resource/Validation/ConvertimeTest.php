<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Validation\Convertime as ValidationConvertime;

class ConvertimeTest extends TestCase
{
    /**
     * testWeekend
     */
    public function testWeekend()
    {
        $res = (new ValidationConvertime())->isWeekend('2021-07-18');
        $this->assertTrue($res);
    }
}
