<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Str\Str;

class StrStaticTest extends TestCase
{
    public static function providerCompare()
    {
        return array(
            array('foobar', new Str('fooba'), 1),
        );
    }

    /**
     * @covers ::compare
     * @param $string
     * @param $target
     * @param $expected
     */
    #[DataProvider('providerCompare')]
    public function testCompare($string, $target, $expected)
    {
        $result = Str::compare($string, $target);
        $this->assertEquals($expected, $result);
    }

    public static function providerCompareInsensitive()
    {
        return array(
            array('hello wOrLd', 'HELLO world', 0),
        );
    }

    /**
     * @covers ::compareInsensitive
     * @param $str
     * @param $target
     * @param $expected
     */
    #[DataProvider('providerCompareInsensitive')]
    public function testCompareInsensitive($string, $target, $expected)
    {
        $result = Str::compareInsensitive($string, $target);
        $this->assertEquals($expected, $result);
    }

    public static function providerContains()
    {
        return array(
            array('containing string', 'ning', TRUE),
            array('noncontaining', 'foobar', FALSE),
        );
    }

    /**
     * @covers ::contains
     * @param $string
     * @param $substring
     * @param $expected
     */
    #[DataProvider('providerContains')]
    public function testContains($string, $substring, $expected)
    {
        $result = Str::contains($string, $substring);
        $this->assertSame($result, $expected);
    }

    public static function providerCountChars()
    {
        return array(
            array('abababcabc', array('a' => 4, 'b' => 4, 'c' => 2)),
        );
    }

    /**
     * @covers ::countChars
     * @param $string
     * @param $expected
     */
    #[DataProvider('providerCountChars')]
    public function testCountChars($string, $expected)
    {
        $result = Str::countChars($string);
        $this->assertEquals($expected, $result);
    }

    public static function providerPosition()
    {
        return array(
            array('foo is bar sometimes', ' is ', 0, 3),
        );
    }

    /**
     * @covers ::position
     * @covers ::value
     * @param $string
     * @param $substring
     * @param $offset
     * @param $expected
     */
    #[DataProvider('providerPosition')]
    public function testPosition($string, $substring, $offset, $expected)
    {
        $result = Str::position($string, $substring, $offset);
        $this->assertEquals($expected, $result);
    }

    public static function providerUniqueChars()
    {
        return array(
            array('abcabdabcd', 'abcd'),
            array('zd111cba', '1abcdz'),
            array('z2d3c88ba', '238abcdz'),
        );
    }

    /**
     * @covers ::uniqueChars
     * @covers ::value
     * @param $string
     * @param $expected
     */
    #[DataProvider('providerUniqueChars')]
    public function testUniqueChars($string, $expected)
    {
        $unique = Str::uniqueChars($string);
        $this->assertEquals($expected, $unique);
    }

    public static function providerWordCount()
    {
        return array(
            array('foo is here 3 times foo foo', NULL, 6),
        );
    }

    /**
     * @covers ::wordCount
     * @covers ::value
     * @param $string
     * @param $charlist
     * @param $expected
     */
    #[DataProvider('providerWordCount')]
    public function testWordCount($string, $charlist, $expected)
    {
        $result = Str::wordCount($string, $charlist);
        $this->assertEquals($expected, $result);
    }

    public static function providerWords()
    {
        return array(
            array('foo foo bar foo', NULL, array(0 => 'foo', 4 => 'foo', 8 => 'bar', 12 => 'foo')),
        );
    }

    /**
     * @covers ::words
     * @covers ::value
     * @param $string
     * @param $charlist
     * @param $expected
     */
    #[DataProvider('providerWords')]
    public function testWords($string, $charlist, $expected)
    {
        $result = Str::words($string, $charlist);
        $this->assertEquals($expected, $result);
    }
}
