<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Solital\Core\Logger\Logger;
use PHPUnit\Framework\TestCase;
use Solital\Core\Kernel\Application;
use Solital\Core\Kernel\DebugCore;
use Solital\Core\Logger\Exception\LoggerException;

class LoggerTest extends TestCase
{
    public function checkLogsAreEnabled()
    {
        $config = Application::yamlParse('logger.yaml');
        
        if ($config['enable_logs'] == false) {
            throw new \Exception("Logs are disabled");
        }
    }

    public function testLog()
    {
        $this->checkLogsAreEnabled();
        Logger::channel('single')->debug('My info message');

        $dir = Application::getRootApp('Storage/log/', false);
        $file_exists = Application::fileExistsWithoutCache($dir . "logs.log");
        $this->assertTrue($file_exists);
    }

    public function testSysLog()
    {
        $this->checkLogsAreEnabled();
        Logger::channel('main')->debug('My info message');

        $dir = "c:/wamp64/logs/";
        $file_exists = Application::fileExistsWithoutCache($dir . "syslogs.log");
        $this->assertTrue($file_exists);
    }

    public function testCustomHandler()
    {
        $this->checkLogsAreEnabled();

        $path = Application::getRootApp("Storage/", DebugCore::isCoreDebugEnabled());
        $handler[] = new StreamHandler($path . 'log/custom.log', Level::Debug);

        Logger::customHandler('custom-channel', $handler)->debug('My info message');

        $dir = Application::getRootApp('Storage/log/', false);
        $file_exists = Application::fileExistsWithoutCache($dir . "custom.log");
        $this->assertTrue($file_exists);
    }

    /* public function testMailLog()
    {
        Logger::channel('mail')->error('My info message');
    } */

    public function testException()
    {
        $this->expectException(LoggerException::class);
        $this->checkLogsAreEnabled();
        Logger::channel('channel_not_exists')->info('My info message');
    }
}
