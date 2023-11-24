<?php

declare(strict_types=1);

namespace Solital\Core\Logger\tests\Handler;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Handler\EmailHandler;
use Solital\Core\Logger\Logger;

class EmailHandlerTest extends TestCase
{
    public function testEmailSend()
    {
        $logger = new Logger('Test');
        $logger->addHandler(
            LogLevel::DEBUG,
            new EmailHandler()
        );
        $logger->info('test');
    }
}