<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests;

use PHPUnit\Framework\TestCase;
use Solital\Core\Logger\LoggerFile;

class LoggerFileTest extends TestCase
{
    public function testFile()
    {
        $result = false;
        $channel = 'events';
        $logger  = new LoggerFile($channel, 'file_log_test');

        $logger->info('Logger really is simple.');

        $generated_log = $channel . "-file_log_test-" . date('Y-m-d.H-i-s') . ".txt";

        if (file_exists(dirname(__DIR__) . "/files_test/Storage/log/" . $generated_log)) {
            $result = true;
        }

        $this->assertTrue($result);
    }
}
