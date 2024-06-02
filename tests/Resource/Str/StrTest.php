<?php

namespace Kemo;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Str\Str;

class StrTest extends TestCase
{
    public function testCallThrowsStrException()
    {
        $this->expectException('BadMethodCallException');

        $str = new Str('foo');
        $str->method_that_will_never_exists();
    }

    public static function providerRenderValue()
    {
        return array(
            array('foo'),
            array('something going on'),
        );
    }

    /**
     * @param        string $string
     */
    #[DataProvider('providerRenderValue')]
    public function testRenderValue($string)
    {
        $str = new Str($string);

        $this->assertEquals($string, (string) $str);
        $this->assertEquals($string, $str->value());
    }

    public function testValues()
    {
        $str = new Str(' something going on ');
        $str->ltrim();
        $str->replace(array('going' => 'not going'));

        $this->assertEquals($str->values(), array(
            ' something going on ',
            'something going on ',
            'something not going on '
        ));
    }

    public function testAfter()
    {
        $str = new Str("This is a test");
        $str->after("This is");
        $this->assertEquals(" a test", $str->value());
    }

    public function testBefore()
    {
        $str = new Str("This is a test");
        $str->before("a test");
        $this->assertEquals("This is ", $str->value());
    }

    public static function providerAddCslashes()
    {
        return array(
            array('foo=bar', 'A..z', '\f\o\o=\b\a\r'),
            array('testescape', 'acep', 't\est\es\c\a\p\e'),
        );
    }

    /**
     * @param string $string
     * @param string $charlist
     */
    #[DataProvider('providerAddCslashes')]
    public function testAddCslashes($string, $charlist, $expected)
    {
        $str = new Str($string);
        $slashed = (string) $str->addCslashes($charlist);

        $this->assertEquals($expected, $slashed);
    }

    public static function providerAddSlashes()
    {
        return array(
            array("aa'bb", "aa\'bb"),
            array("O'''Reilly", "O\'\'\'Reilly"),
        );
    }

    /**
     * @param string $string
     * @param string $expected
     */
    #[DataProvider('providerAddSlashes')]
    public function testAddSlashes($string, $expected)
    {
        $str = new Str($string);
        $slashed = $str->addSlashes();

        $this->assertEquals($expected, $slashed);
    }

    public static function providerChunkSplit()
    {
        return array(
            array('foobar', 2, ':', 'fo:ob:ar:'),
            array('foo bar foobar', 3, '!', 'foo! ba!r f!oob!ar!'),
        );
    }

    /**
     * @param $string
     * @param $length
     * @param $end
     * @param $expected
     */
    #[DataProvider('providerChunkSplit')]
    public function testChunkSplit($string, $length, $end, $expected)
    {
        $str = new Str($string);
        $split = (string) $str->chunkSplit($length, $end);

        $this->assertEquals($expected, $split);
    }

    public static function providerConcat()
    {
        return array(
            array('foo', 'bar', 'foobar'),
            array('', 'foobar', 'foobar'),
        );
    }

    /**
     * @param $string
     * @param $concat
     * @param $expected
     */
    #[DataProvider('providerConcat')]
    public function testConcat($string, $concat, $expected)
    {
        $str = new Str($string);
        $str->concat($concat);

        $this->assertSame($expected, $str->value());
    }

    public static function providerIreplace()
    {
        return array(
            array('foo=bar', array('FOO' => 'bar'), 'bar=bar'),
            array('coolCoOlCooLCOOL', array('cool' => '0'), '0000'),
        );
    }

    /**
     * @param $string
     * @param $elements
     * @param $expected
     */
    #[DataProvider('providerIreplace')]
    public function testIreplace($string, array $replacements, $expected)
    {
        $str = new Str($string);
        $result = $str->ireplace($replacements);

        $this->assertEquals($expected, $result);
    }

    public static function providerLtrim()
    {
        return array(
            array(" \n\r foo", NULL, 'foo'),
            array('_-_foo', '_-', 'foo'),
        );
    }

    /**
     * @param $string
     * @param $mask
     * @param $expected
     */
    #[DataProvider('providerLtrim')]
    public function testLtrim($string, $mask, $expected)
    {
        $str = new Str($string);
        $trimmed = $str->ltrim($mask);

        $this->assertEquals($expected, $trimmed);
    }

    public static function providerNl2Br()
    {
        return array(
            array("foo\nbar" . PHP_EOL, "foo<br />\nbar<br />" . PHP_EOL),
        );
    }

    /**
     * @param $string
     * @param $mask
     * @param $expected
     */
    #[DataProvider('providerNl2Br')]
    public function testNl2Br($string, $expected)
    {
        $str = new Str($string);
        $str->nl2br();

        $this->assertEquals($expected, $str->value());
    }

    public static function providerPad()
    {
        return array(
            array('foo',    16, '-.-;', STR_PAD_RIGHT, 'foo-.-;-.-;-.-;-'),
            array('bar',    8,  'foo',  STR_PAD_LEFT,  'foofobar'),
            array('foobar', 10, ':',    STR_PAD_BOTH,  '::foobar::'),
        );
    }

    /**
     * @param $string
     * @param $pad_length
     * @param $pad_string
     * @param $pad_type
     * @param $expected
     */
    #[DataProvider('providerPad')]
    public function testPad($string, $pad_length, $pad_string, $pad_type, $expected)
    {
        $str = new Str($string);
        $str->pad($pad_length, $pad_string, $pad_type);

        $this->assertEquals($expected, $str->value());
    }

    public static function providerRepeat()
    {
        return array(
            array('foo', 5, 'foofoofoofoofoo'),
        );
    }

    public function testRemoveAccents()
    {
        $str = new Str("àèìÒ");
        $str->removeAccents();
        $this->assertEquals("aeiO", $str->value());
    }

    #[DataProvider('providerRepeat')]
    public function testRepeat($string, $multiplier, $expected)
    {
        $str = new Str($string);

        $this->assertEquals($expected, $str->repeat($multiplier));
    }

    public static function providerReplace()
    {
        return array(
            array(
                'foo can be bar but foo does not have to',
                array('foo' => 'fOo', 'DoEs Not' => 'fail', 'to' => '2'),
                NULL,
                'fOo can be bar but fOo does not have 2',
            ),
        );
    }

    #[DataProvider('providerReplace')]
    public function testReplace($string, $replacements, $count, $expected)
    {
        $str = new Str($string);

        $this->assertEquals($expected, $str->replace($replacements, $count));
    }

    public static function providerReverse()
    {
        return array(
            array('knitS raW pupilS regaL', 'Lager Slipup War Stink'),
            array('deSseRTs', 'sTResSed'),
            array('long live the king', 'gnik eht evil gnol'),
        );
    }

    #[DataProvider('providerReverse')]
    public function testReverse($string, $expected)
    {
        $str = new Str($string);

        $this->assertEquals($expected, $str->reverse());
    }

    public static function providerRot13()
    {
        return array(
            array('999', '999'),
            array('foobar', 'sbbone'),
            array('PHP 5.3', 'CUC 5.3'),
        );
    }

    /**
     * @param $string
     * @param $expected
     */
    #[DataProvider('providerRot13')]
    public function testRot13($string, $expected)
    {
        $str = new Str($string);

        $this->assertEquals($expected, $str->rot13());
    }

    public static function providerRTrim()
    {
        return array(
            array(' foo      ', NULL, ' foo'),
            array('ofoooooo', 'o', 'of'),
            array('_foobar ="__-', array('_', '-', '=', '"'), '_foobar '),
        );
    }

    /**
     * @param $string
     * @param $mask
     * @param $expected
     */
    #[DataProvider('providerRTrim')]
    public function testRTrim($string, $mask, $expected)
    {
        $str = new Str($string);

        $this->assertEquals($expected, $str->rtrim($mask));
    }

    public function testShuffle()
    {
        $str = new Str('foo bar foobar pebkac fubar');
        $original = $str->value();
        $str->shuffle();
        $this->assertNotEquals($str->value(), $original);
    }

    public function testShorten()
    {
        $str = new Str('This is a test');
        $str->shorten(10);
        $this->assertEquals($str->value(), 'This is...');
    }

    public function testSlug()
    {
        $str = new Str('This is a test');
        $str->slug();
        $this->assertEquals($str->value(), 'this-is-a-test');
    }

    public static function providerSpecialChars()
    {
        return array(
            array('<p>This should <br>not be wrapped</p>', '&lt;p&gt;This should &lt;br&gt;not be wrapped&lt;/p&gt;', '<p>This should <br>not be wrapped</p>')
        );
    }

    /**
     * @param $string
     * @param $allowed_tags
     * @param $expected
     */
    #[DataProvider('providerSpecialChars')]
    public function testSpecialChars($string, $expected1, $expected2)
    {
        $str = new Str($string);
        $res1 = $str->specialchars();
        $chars_removed = $res1->value();
        $res2 = $str->specialchars(true);
        $chars_back = $res2->value();

        $this->assertEquals($expected1, $chars_removed);
        $this->assertEquals($expected2, $chars_back);
    }

    public static function providerStripTags()
    {
        return array(
            array('<p>This should <br>not be wrapped</p>', NULL, 'This should not be wrapped'),
            array('<html><div><p><br/><br></p></div></html>', NULL, ''),
            array('<html><div><p><br/><br></p></div></html>', '<br>', '<br/><br>'),
        );
    }

    /**
     * @param $string
     * @param $allowed_tags
     * @param $expected
     */
    #[DataProvider('providerStripTags')]
    public function testStripTags($string, $allowed_tags, $expected)
    {
        $str = new Str($string);

        $this->assertEquals($expected, $str->stripTags($allowed_tags));
    }

    public static function providerTranslate()
    {
        return array(
            array(':foo :bar', array(':foo' => ':bar', ':bar' => ':newbar'), ':bar :newbar'),
            array('foo is bar', array('foo' => 'bar', 'bar' => 'foo'), 'bar is foo'),
        );
    }

    /**
     * @param $string
     * @param $translations
     * @param $expected
     */
    #[DataProvider('providerTranslate')]
    public function testTranslate($string, $translations, $expected)
    {
        $str = new Str($string);

        $this->assertEquals($expected, $str->translate($translations));
    }

    public static function providerTrim()
    {
        return array(
            array(" \n foo      ", null, 'foo'),
            array('offsoooooo', 'o', 'ffs'),
            array('_foobar ="__-', array('_', '-', '=', '"'), 'foobar '),
        );
    }

    /**
     * @param $string
     * @param $mask
     * @param $expected
     */
    #[DataProvider('providerTrim')]
    public function testTrim($string, $mask, $expected)
    {
        $str = new Str($string);
        $this->assertEquals($expected, $str->trim($mask));
    }

    public function testUpper()
    {
        $str = new Str('string');
        $str->toUpper();
        $this->assertEquals($str->value(), 'STRING');
    }

    public function testLower()
    {
        $str = new Str('STRING');
        $str->toLower();
        $this->assertEquals($str->value(), 'string');
    }

    public static function providerUndo()
    {
        return array(
            array(' foo is bar ', 1, 'foo is bar'),
            array(' foo is bar ', 2, ' foo is bar'),
            array(' foo is bar ', 10, ' foo is bar '),
        );
    }

    /**
     * @param $steps
     * @param $expected
     */
    #[DataProvider('providerUndo')]
    public function testUndo($string, $steps, $expected)
    {
        $str = new Str($string);
        $str->rtrim();
        $str->ltrim();
        $str->replace(array(' is ' => '_is_'));

        $this->assertEquals($expected, $str->undo($steps));
    }

    /**
     * @param $steps
     * @param $expected
     */
    public function testUndoArgument()
    {
        $this->expectException('\TypeError');

        $str = new Str('unimportant string');
        $str->undo('string');
    }
}
