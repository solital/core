<?php

namespace Solital\Core\Container\tests;

class MailTest
{
    public function setTransport($transport)
    {
        echo $transport . PHP_EOL;
    }
}
