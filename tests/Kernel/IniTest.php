<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Kernel\Application;

class IniTest extends TestCase
{
    public function testIni()
    {
        Application::startIniConfig();

        $this->assertEquals("America/Fortaleza", date_default_timezone_get());
        $this->assertEquals("157286400", ini_get("memory_limit"));
        $this->assertEquals("UTF-8", ini_get("default_charset"));
        $this->assertEquals(50, ini_get("max_execution_time"));
        $this->assertEquals("neutral", ini_get("mbstring.language"));
    }
}
