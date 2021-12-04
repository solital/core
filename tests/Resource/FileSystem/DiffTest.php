<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\FileSystem\Diff;

class DiffTest extends TestCase
{
    public function testCompareLine()
    {
        $diff = Diff::compare("line1\nline2", "lineA\nlineB");
        $this->assertIsArray($diff);
    }

    public function testCompareString()
    {
        $diff = Diff::compare('bar', 'baz', true);
        $this->assertIsArray($diff);
    }

    public function testCompareFile()
    {
        $file1 = __DIR__ . DIRECTORY_SEPARATOR . 'style1.css';
        $file2 = __DIR__ . DIRECTORY_SEPARATOR . 'style2.css';

        $diff = Diff::compareFiles($file1, $file2);
        $this->assertIsArray($diff);
    }

    public function testCompareFileToString()
    {
        $file1 = __DIR__ . DIRECTORY_SEPARATOR . 'style1.css';
        $file2 = __DIR__ . DIRECTORY_SEPARATOR . 'style2.css';

        $diff = Diff::toString(Diff::compareFiles($file1, $file2));
        $this->assertIsString($diff);
    }

    public function testCompareFileToTable()
    {
        $file1 = __DIR__ . DIRECTORY_SEPARATOR . 'style1.css';
        $file2 = __DIR__ . DIRECTORY_SEPARATOR . 'style2.css';

        $diff = Diff::toTable(Diff::compareFiles($file1, $file2));
        $this->assertIsString($diff);
    }

    public function testCompareFileToHTML()
    {
        $file1 = __DIR__ . DIRECTORY_SEPARATOR . 'style1.css';
        $file2 = __DIR__ . DIRECTORY_SEPARATOR . 'style2.css';

        $diff = Diff::toHTML(Diff::compareFiles($file1, $file2));
        $this->assertIsString($diff);
    }
}
