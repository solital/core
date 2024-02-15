<?php

namespace Solital\Test\Container;

class UserTest
{
    private $mailer;

    public function __construct(MailTest $mailer)
    {
        $this->mailer = $mailer;
    }

    public function setMailerTransport($transport)
    {
        $this->mailer->setTransport($transport);
    }
}
