<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use PHPUnit\Framework\TestCase;
use Solital\Core\Kernel\DebugCore;
use Solital\Core\Mail\Mailer;

class MailTest extends TestCase
{
    public function testQueueEmail()
    {
        //DebugCore::setMailConfig();

        $mailer = new Mailer();
        $mailer->add('sender_email@gmail.com', 'Sender name', 'recipient_email@gmail.com', 'Recipient name');
        $res = $mailer->queue('E-mail test', '<h1>E-mail test</h1>');
        $this->assertTrue($res);
    }

    public function testSendQueueEmail()
    {
        //DebugCore::setMailConfig();
        
        $mailer = new Mailer();
        $mailer->sendQueue();
    }
}
