<?php

namespace Solital\Core\Resource\Mail;

use PHPMailer\PHPMailer\PHPMailer;

class PHPMailerClass
{
    /**
     * @var PHPMailer
     */
    private $mail;

    /**
     * __construct
     * 
     * @param bool $exceptions
     *
     * @return void
     */
    public function __construct($exceptions = true)
    {
        $this->mail = new PHPMailer($exceptions);

        $this->mail->SMTPDebug = (int)$_ENV['PHPMAILER_DEBUG'];
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['PHPMAILER_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['PHPMAILER_USER'];
        $this->mail->Password   = $_ENV['PHPMAILER_PASS'];
        $this->mail->SMTPSecure = $_ENV['PHPMAILER_SECURITY'];
        $this->mail->Port       = $_ENV['PHPMAILER_PORT'];
        $this->mail->setLanguage('br');
    }


    /**
     * add
     *
     * @param string $sender
     * @param string $senderName
     * @param string $recipient
     * @param string $recipientName
     * 
     * @return PHPMailerClass
     */
    public function add(string $sender, string $senderName, string $recipient, string $recipientName): PHPMailerClass
    {
        $this->mail->setFrom($sender, $senderName);
        $this->mail->addAddress($recipient, $recipientName);

        return $this;
    }

    /**
     * attach
     *
     * @param string $filePath
     * @param string $fileName
     * 
     * @return PHPMailerClass
     */
    public function attach(string $filePath, string $fileName = ''): PHPMailerClass
    {
        $this->mail->addAttachment($filePath, $fileName);

        return $this;
    }

    /**
     * embeddedImage
     *
     * @param string $imagePath
     * @param string $cid
     * @param string $imageName
     * 
     * @return PHPMailerClass
     */
    public function embeddedImage(string $imagePath, string $cid, string $imageName = ''): PHPMailerClass
    {
        $this->mail->addEmbeddedImage($imagePath, $cid, $imageName);

        return $this;
    }

    /**
     * send
     *
     * @param string $subject
     * @param string $body
     * @param string $altbody
     * 
     * @return bool
     */
    public function sendEmail(string $subject, string $body, string $altbody = ""): bool
    {
        try {
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'utf-8';
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altbody;
            $this->mail->send();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * error
     *
     * @return string
     */
    public function error(): string
    {
        return $this->mail->ErrorInfo;
    }
}
