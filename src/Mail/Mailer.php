<?php

namespace Solital\Core\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use Solital\Core\Kernel\Application;
use Solital\Core\Mail\QueueMail;

class Mailer extends QueueMail
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

    /**
     * __construct
     * 
     * @return void
     */
    public function __construct()
    {
        if (Application::MAILER_TEST_UNIT == true) {
            $this->connectUnitTest();
        } else {
            $config = Application::getYamlVariables(5, 'bootstrap.yaml');

            if ($config['mail_test']['mail_test_enable'] == true) {
                $this->connectionTest();
            } else {
                $this->connection();
            }
        }
    }


    /**
     * add
     *
     * @param string $sender_mail
     * @param string $sender_name
     * @param string $recipient
     * @param string $recipient_name
     * 
     * @return Mailer
     */
    public function add(string $sender_mail, string $sender_name, string $recipient, string $recipient_name): Mailer
    {
        $this->sender = $sender_mail;
        $this->sender_name = $sender_name;
        $this->recipient = $recipient;
        $this->recipient_name = $recipient_name;

        $this->mail->setFrom($this->sender, $this->sender_name);
        $this->mail->addAddress($this->recipient, $this->recipient_name);

        return $this;
    }

    /**
     * attach
     *
     * @param string $file_path
     * @param string $file_name
     * 
     * @return Mailer
     */
    public function attach(string $file_path, string $file_name = ''): Mailer
    {
        $this->mail->addAttachment($file_path, $file_name);

        return $this;
    }

    /**
     * embeddedImage
     *
     * @param string $image_path
     * @param string $cid
     * @param string $image_name
     * 
     * @return Mailer
     */
    public function embeddedImage(string $image_path, string $cid, string $image_name = ''): Mailer
    {
        $this->mail->addEmbeddedImage($image_path, $cid, $image_name);

        return $this;
    }

    /**
     * send
     *
     * @param string $subject
     * @param string $body
     * @param string $alt_body
     * 
     * @return bool
     */
    public function send(string $subject, string $body, string $alt_body = ""): bool
    {
        try {
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'utf-8';
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $alt_body;
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

    /**
     * @return Mailer
     */
    private function connection(): Mailer
    {
        $config = Application::getYamlVariables(5, 'bootstrap.yaml');

        $this->mail = new PHPMailer($config['mail_exceptions']);
        $this->mail->SMTPDebug = (int)getenv('MAIL_DEBUG');
        $this->mail->isSMTP();
        $this->mail->Host       = getenv('MAIL_HOST');
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = getenv('MAIL_USER');
        $this->mail->Password   = getenv('MAIL_PASS');
        $this->mail->SMTPSecure = getenv('MAIL_SECURITY');
        $this->mail->Port       = getenv('MAIL_PORT');
        $this->mail->setLanguage('br');

        return $this;
    }

    /**
     * @return Mailer
     */
    private function connectionTest(): Mailer
    {
        $config = Application::getYamlVariables(5, 'bootstrap.yaml');

        $this->mail = new PHPMailer($config['mail_exceptions']);
        $this->mail->SMTPDebug = (int)$config['mail_test']['mail_debug'];
        $this->mail->isSMTP();
        $this->mail->Host       = $config['mail_test']['mail_host'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $config['mail_test']['mail_user'];
        $this->mail->Password   = $config['mail_test']['mail_pass'];
        $this->mail->SMTPSecure = $config['mail_test']['mail_security'];
        $this->mail->Port       = $config['mail_test']['mail_port'];
        $this->mail->setLanguage('br');

        return $this;
    }

    /**
     * @return Mailer
     */
    private function connectUnitTest(): Mailer
    {
        $this->mail = new PHPMailer(true);
        $this->mail->SMTPDebug = 0;
        $this->mail->isSMTP();
        $this->mail->Host       = constant('MAIL_HOST');
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = constant('MAIL_USER');
        $this->mail->Password   = constant('MAIL_PASS');
        $this->mail->SMTPSecure = constant('MAIL_SECURE');
        $this->mail->Port       = constant('MAIL_PORT');
        $this->mail->setLanguage('br');

        return $this;
    }
}
