<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\FileSystem\HandleFiles;

class HandleFilesTest extends TestCase
{
    public function testListFiles()
    {
        $files = new HandleFiles;
        $res = $files->folder(__DIR__ . "/files")->files();
        $this->assertIsArray($res);
    }

    public function testFileExists()
    {
        $files = new HandleFiles;
        $res = $files->folder(__DIR__ . "/files")->fileExists('file1.php');
        $this->assertTrue($res);

        $res = $files->folder(__DIR__ . "/files")->fileExists('file5.php');
        $this->assertFalse($res);

        /* $res = $files->folder(__DIR__ . "/files")->fileExists('delete.php', true);
        $this->assertTrue($res); */
    }

    /* public function testCreateFolder()
    {
        $files = new HandleFiles;
        $res = $files->create(__DIR__ . "/files_test");
        $this->assertTrue($res);
    } */

    /* public function testDeleteFolder()
    {
        $files = new HandleFiles;
        $res = $files->remove(__DIR__ . "/files_test", false);
        $this->assertTrue($res);
    } */

    /* public function testCopyFile()
    {
        $files = new HandleFiles;
        $res = $files->copy(__DIR__ . "/files/file1.php", __DIR__ . "/files/file1_bkp.php", true);
        $this->assertTrue($res);
    } */

    public function testGetPermissions()
    {
        $files = new HandleFiles;
        $full_permission = $files->getFullPermission(__DIR__ . "/files/file1.php");
        $permission = $files->getFullPermission(__DIR__ . "/files/file1.php");
        
        $this->assertIsString($full_permission);
        $this->assertIsString($permission);
    }
}