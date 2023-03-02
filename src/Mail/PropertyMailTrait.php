<?php

namespace Solital\Core\Mail;

use PHPMailer\PHPMailer\PHPMailer;

trait PropertyMailTrait
{
    /**
     * @var PHPMailer
     */
    protected PHPMailer $mail;

    /**
     * @var string
     */
    protected string $sender;

    /**
     * @var string
     */
    protected string $sender_name;

    /**
     * @var string
     */
    protected string $recipient;

    /**
     * @var string
     */
    protected string $recipient_name;
}
