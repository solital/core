<?php

use PHPUnit\Framework\TestCase;
use Solital\Core\Wolf\Wolf;
use Solital\Core\Kernel\Application;

class WolfTest extends TestCase
{
    public function testArgs()
    {
        $wolf = new Wolf();
        $res = $wolf->setArgs([
            'name' => 'Solital',
            'email' => 'solital@email.com'
        ]);

        $this->assertIsArray((array)$res->getArgs());
    }

    public function testArgsSpecialChar()
    {
        $wolf = new Wolf();
        $res = $wolf->setArgs([
            'xss' => '<strong>Solital</strong>'
        ]);

        $this->assertEquals("&lt;strong&gt;Solital&lt;/strong&gt;", $res->getArgs()['xss']);
    }

    public function testRender()
    {
        $wolf = new Wolf();
        $wolf->setArgs([
            'name' => 'Solital',
            'email' => 'solital@email.com'
        ]);

        $res = $wolf->setView('home')->render();
        $this->assertEquals('<h1>home test SOLITAL</h1><p>solital@email.com</p>', $res);
    }

    public function testCacheView()
    {
        $result = false;

        $wolf = new Wolf();
        $wolf->setArgs([
            'name' => 'Solital',
            'email' => 'solital@email.com'
        ]);

        #$wolf->setView('contact')->forOneWeek()->render();

        $dir_view = Application::getRootTest("view/");
        $file_name = "contact-20220325-12.cache.php";

        if (file_exists($dir_view . "cache" . DIRECTORY_SEPARATOR . $file_name)) {
            $result = true;
        }

        $this->assertTrue($result);
    }

    public function testWolfCacheClass()
    {
        $result = false;
        #WolfCache::cache()->forOneMinute()->makeCache('welcome');

        $dir_view = Application::getRootTest("view/");
        $file_name = "contact-20220325-12.cache.php";

        if (file_exists($dir_view . "cache" . DIRECTORY_SEPARATOR . $file_name)) {
            $result = true;
        }

        $this->assertTrue($result);
    }
}
