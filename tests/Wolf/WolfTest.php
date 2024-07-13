<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Wolf\Wolf;

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

    public function testArgsWithoutEscapeSpecialChar()
    {
        $wolf = new Wolf();
        $res = $wolf->setArgs([
            'xss' => '<strong>Solital</strong>'
        ], false);

        $this->assertEquals("<strong>Solital</strong>", $res->getArgs()['xss']);
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

        $wolf->setView('contact')->setCacheTime('week')->render();
    }

    public function testWithInstance()
    {
        $wolf = new Wolf();
        $wolf->setArgs([
            'name' => 'Solital',
            'email' => 'solital@email.com'
        ]);
        $wolf->setView('contact');
        $wolf->setCacheTime('hour');
        //$wolf->setMinify('all');

        $this->assertIsArray($wolf->getArgs());

        //$wolf->render(); // Equals: <h1>cache 3 contact test</h1>
    }
}
