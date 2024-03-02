<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'mailConfig.php';

use Solital\Core\Logger\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testLog()
    {
        Logger::channel('single')->error('My info message');
    }

    /* public function testMailLog()
    {
        Logger::channel('mail')->error('My info message');
    } */

    public function testException()
    {
        $this->expectException('Solital\Core\Logger\Exception\LoggerException');

        Logger::channel('channel_not_exists')->info('My info message');
    }
}
