<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Number;

class NumberTest extends TestCase
{
    public function testNumberReduce()
    {
        $this->assertEquals(1.23, Number::reduce(1.2335566));
    }

    public function testNumberCurrency()
    {
        $this->assertEquals(
            'R$12,345,678.90', 
            Number::currency(12345678.90, 'BRL')
        );

        $this->assertEquals(
            '€12,345.12', 
            Number::currency(12345.12345, 'EUR')
        );
    }

    public function testNumberPercent()
    {
        $this->assertEquals('12.3%', Number::percent(0.123));
    }

    public function testNumberSpell()
    {
        $this->assertEquals(
            'twelve million three hundred forty-five thousand six hundred seventy-eight', 
            Number::spell(12345678)
        );

        $this->assertEquals(
            'duzentos e cinquenta e quatro', 
            Number::spell(254, 'pt-BR')
        );
    }

    public function testNumberFormat()
    {
        $this->assertEquals('12,345,678.90', Number::format(12345678.9));
    }

    public function testNumberClamp()
    {
        $this->assertEquals(1, Number::clamp(-2, 1, 100));
        $this->assertEquals(100, Number::clamp(125, 1, 100));
    }

    public function testNumberRoman()
    {
        $this->assertEquals('IV', Number::toRoman(4));
        $this->assertEquals('X', Number::toRoman(10));
        $this->assertEquals('LVII', Number::toRoman(57));
        $this->assertEquals('LVIII', Number::toRoman(58));
        $this->assertEquals('ↂ', Number::toRoman(10000));
    }
}
