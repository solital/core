<?php

use Solital\Core\Session\Handler\ApcuSessionHandler;

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Session\Enum\SessionSaveHandlerEnum;
use Solital\Core\Session\SessionConfiguration;

class SessionConfigTest extends TestCase
{
    private string $storage = __DIR__ . DIRECTORY_SEPARATOR . "storage";
    private mixed $handler;

    public function setUp(): void
    {
        if (!is_dir($this->storage)) mkdir($this->storage);

        //$this->handler = new ApcuSessionHandler();
        $this->handler = SessionSaveHandlerEnum::FILES;

        $session = new SessionConfiguration();
        $session->setSaveHandler($this->handler);
        $session->setSavePath($this->storage);
        $session->start();
    }

    public function testGetContents()
    {
        //dump_die($this->handler instanceof SessionSaveHandlerEnum);
        session("name", "session test");

        if ($this->handler instanceof SessionSaveHandlerEnum) {
            $this->assertEquals(
                'name|s:12:"session test";',
                SessionConfiguration::getSessionContents()
            );
        } else {
            $this->markTestSkipped();
        }
    }

    public function testGetSaveHandler()
    {
        if ($this->handler instanceof SessionSaveHandlerEnum) {
            $this->assertEquals("FILES", SessionConfiguration::getSaveHandler());
        } else {
            $this->markTestSkipped();
        }
    }

    public function testGetFilename()
    {
        if ($this->handler instanceof SessionSaveHandlerEnum) {
            $filename = SessionConfiguration::getSessionFilename();
            (file_exists($filename)) ? $exists = true : $exists = false;

            clearstatcache(true, $filename);
            $this->assertTrue($exists);

            SessionConfiguration::ExecuteGc($filename);
        } else {
            $this->markTestSkipped();
        }
    }

    public function __destruct()
    {
        array_map(
            'unlink',
            array_filter((array) array_merge(glob($this->storage . "/*")))
        );
    }
}
