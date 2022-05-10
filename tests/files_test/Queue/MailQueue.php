<?php

use Solital\Core\Resource\Mail\Mailer;

class MailQueue
{
    public function dispatch()
    {
        echo get_class() . PHP_EOL;
        #(new Mailer)->sendQueue();
    }
}
