<?php

require_once dirname(__DIR__) . "/bootstrap.php";

use PHPUnit\Framework\TestCase;
use Solital\Core\Resource\Message;

class MessageTest extends TestCase
{
    public function testMessageInstance()
    {
        $message = new Message();
        $message->new('msg.test', 'Normal message test');

        $this->assertTrue($message->has('msg.test'));
        $this->assertEquals('Normal message test', $message->get('msg.test'));
        $this->assertFalse($message->has('msg.test'));

        message('msg.test', 'Normal message test');
    }

    public function testMessageHelper()
    {
        message('msg.test', 'Normal message test');

        $this->assertTrue(message()->has('msg.test'));
        $this->assertEquals('Normal message test', message()->get('msg.test'));
        $this->assertFalse(message()->has('msg.test'));
    }

    public function testCustomMessage()
    {
        $message = new Message();
        $message->info('msg.info.test', 'Info message test');
        $message->success('msg.success.test', 'Success message test');
        $message->warning('msg.warning.test', 'Warning message test');
        $message->error('msg.error.test', 'Error message test');

        $this->assertTrue($message->has('msg.info.test', Message::INFO));
        $this->assertTrue($message->has('msg.success.test', Message::SUCCESS));
        $this->assertTrue($message->has('msg.warning.test', Message::WARNING));
        $this->assertTrue($message->has('msg.error.test', Message::ERROR));

        $this->assertEquals('<div class="alert-info">Info message test</div>', $message->get('msg.info.test'));
        $this->assertEquals('<div class="alert-success">Success message test</div>', $message->get('msg.success.test'));
        $this->assertEquals('<div class="alert-warning">Warning message test</div>', $message->get('msg.warning.test'));
        $this->assertEquals('<div class="alert-error">Error message test</div>', $message->get('msg.error.test'));

        $this->assertFalse($message->has('msg.info.test', Message::INFO));
        $this->assertFalse($message->has('msg.success.test', Message::SUCCESS));
        $this->assertFalse($message->has('msg.warning.test', Message::WARNING));
        $this->assertFalse($message->has('msg.error.test', Message::ERROR));
    }

    public function testCustomMessageHelper()
    {
        message()->info('msg.info.test', 'Info message test');
        message()->success('msg.success.test', 'Success message test');
        message()->warning('msg.warning.test', 'Warning message test');
        message()->error('msg.error.test', 'Error message test');

        $this->assertTrue(message()->has('msg.info.test', Message::INFO));
        $this->assertTrue(message()->has('msg.success.test', Message::SUCCESS));
        $this->assertTrue(message()->has('msg.warning.test', Message::WARNING));
        $this->assertTrue(message()->has('msg.error.test', Message::ERROR));

        $this->assertEquals('<div class="alert-info">Info message test</div>', message()->get('msg.info.test'));
        $this->assertEquals('<div class="alert-success">Success message test</div>', message()->get('msg.success.test'));
        $this->assertEquals('<div class="alert-warning">Warning message test</div>', message()->get('msg.warning.test'));
        $this->assertEquals('<div class="alert-error">Error message test</div>', message()->get('msg.error.test'));

        $this->assertFalse(message()->has('msg.info.test', Message::INFO));
        $this->assertFalse(message()->has('msg.success.test', Message::SUCCESS));
        $this->assertFalse(message()->has('msg.warning.test', Message::WARNING));
        $this->assertFalse(message()->has('msg.error.test', Message::ERROR));
    }

    public function testHasError()
    {
        $message = new Message();
        $message->error('msg.error.test', 'Error message test');

        $this->assertTrue($message->hasErrors());
        $this->assertEquals('<div class="alert-error">Error message test</div>', $message->get('msg.error.test'));
        $this->assertFalse($message->hasErrors());
    }

    public function testHasErrorHelper()
    {
        message()->error('msg.error.test', 'Error message test');

        $this->assertTrue(message()->hasErrors());
        $this->assertEquals('<div class="alert-error">Error message test</div>', message()->get('msg.error.test'));
        $this->assertFalse(message()->hasErrors());
    }
}
