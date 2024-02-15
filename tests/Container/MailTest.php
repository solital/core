<?php

namespace Solital\Test\Container;

class MailTest
{
    public function setTransport($transport)
    {
        echo $transport . PHP_EOL;
    }
}
