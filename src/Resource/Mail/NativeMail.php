<?php

namespace Solital\Core\Resource\Mail;

use Solital\Core\Resource\Validation\Valid;

class NativeMail extends PHPMailerClass
{
    /**
     * Email sender
     * 
     * @var string
     */
    private static string $sender;

    /**
     * Email recipient
     * 
     * @var string
     */
    private static string $recipient;

    /**
     * Email subject
     * 
     * @var string
     */
    private static string $subject;

    /**
     * Email message
     * 
     * @var string
     */
    private static string $message;

    /**
     * Send email
     * 
     * @param string $sender
     * @param string $recipient
     * @param string $subject
     * @param string $message
     * @param string $reply_to
     * @param string $type
     * @param string $charset
     * @param string $priority
     * 
     * @return bool
     */
    public static function send(string $sender, string $recipient, string $subject, string $message, string $reply_to = null, string $type = "text/plan", string $charset = "UTF-8", int $priority = 3): bool
    {
        $validateSender = Valid::email($sender);
        $validateRecipient = Valid::email($recipient);

        if ($validateSender == null) {
            return false;
        } elseif ($validateRecipient == null) {
            return false;
        }

        self::$sender = $sender;
        self::$recipient = $recipient;
        self::$subject = $subject;
        self::$message = $message;

        $headers = "MIME-Version: 1 .1\r\n";
        $headers .= "Content-type: " . $type . "; charset=" . $charset . "\r\n";
        $headers .= "From: " . self::$sender . "\r\n";

        if ($reply_to != null) {
            $headers .= "Reply-To: " . $reply_to . "\r\n";
        }

        $headers .= "X-Priority: " . $priority . "\n";
        $send = mail(self::$recipient, self::$subject, self::$message, $headers);

        if ($send) {
            return true;
        } else {
            return false;
        }
    }
}
