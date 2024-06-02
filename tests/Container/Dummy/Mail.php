<?php

namespace Solital\Test\Container\Dummy;

class Mail
{
    public function setTransport($transport)
    {
        echo $transport . PHP_EOL;
    }
}
