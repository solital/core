<?php

namespace Solital\Test\Container\Dummy;

class User
{
    private $mailer;

    public function __construct(Mail $mailer)
    {
        $this->mailer = $mailer;
    }

    public function setMailerTransport($transport)
    {
        $this->mailer->setTransport($transport);
    }
}
