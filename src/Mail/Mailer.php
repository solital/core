<?php

namespace Solital\Core\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use SensitiveParameter;
use Solital\Core\Mail\QueueMail;
use Solital\Core\Kernel\{Application, DebugCore};

class Mailer extends QueueMail
{
    use PropertyMailTrait;

    public function __construct()
    {
        if (DebugCore::isCoreDebugEnabled() == true) {
            $this->mailerConfig('unit');
        } else {
            $config = Application::yamlParse('mail.yaml');

            if ($config['mail_test']['mail_test_enable'] == true) {
                $this->mailerConfig('development');
            } else {
                $this->mailerConfig('production');
            }
        }

        $this->connection();
    }

    /**
     * @return PHPMailer
     */
    public function getConnection(): PHPMailer
    {
        return $this->mail;
    }

    /**
     * @param string $subject
     * 
     * @return Mailer
     */
    public function setSubject(string $subject): Mailer
    {
        $this->mail->Subject = $subject;
        return $this;
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
    public function add(
        #[SensitiveParameter] string $sender_mail,
        string $sender_name,
        #[SensitiveParameter] string $recipient,
        string $recipient_name
    ): Mailer {
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
    public function send(#[SensitiveParameter] string $subject, string $body, string $alt_body = ""): bool
    {
        try {
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'utf-8';
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $alt_body;
            $this->mail->send();

            return true;
        } catch (\Exception) {
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
        $this->mail = new PHPMailer($this->php_mailer_config['exceptions']);
        $this->mail->isSMTP();
        $this->mail->SMTPAuth   = true;
        $this->mail->SMTPDebug  = $this->php_mailer_config['debug'];
        $this->mail->Host       = $this->php_mailer_config['host'];
        $this->mail->Username   = $this->php_mailer_config['user'];
        $this->mail->Password   = $this->php_mailer_config['pass'];
        $this->mail->SMTPSecure = $this->php_mailer_config['security'];
        $this->mail->Port       = $this->php_mailer_config['port'];
        $this->mail->setLanguage('br');

        return $this;
    }

    /**
     * @param string $type
     * 
     * @return array
     */
    private function mailerConfig(string $type): array
    {
        $config = Application::yamlParse('mail.yaml');

        if ($type === 'unit') {
            $mailer_config = DebugCore::getMailConfig();

            $this->php_mailer_config['exceptions'] = true;
            $this->php_mailer_config['debug']      = 0;
            $this->php_mailer_config['host']       = ($mailer_config['host'] ?? throw new \Exception("Mail 'host' is empty"));
            $this->php_mailer_config['user']       = ($mailer_config['user'] ?? throw new \Exception("Mail 'user' is empty"));
            $this->php_mailer_config['pass']       = ($mailer_config['pass'] ?? throw new \Exception("Mail 'pass' is empty"));
            $this->php_mailer_config['security']   = ($mailer_config['security'] ?? throw new \Exception("Mail 'security' is empty"));
            $this->php_mailer_config['port']       = ($mailer_config['port'] ?? throw new \Exception("Mail 'port' is empty"));
        }

        if ($type === 'development') {
            $this->php_mailer_config['exceptions'] = $config['mail_exceptions'];
            $this->php_mailer_config['debug']      = (int)$config['mail_test']['mail_debug'];
            $this->php_mailer_config['host']       = $config['mail_test']['mail_host'];
            $this->php_mailer_config['user']       = $config['mail_test']['mail_user'];
            $this->php_mailer_config['pass']       = $config['mail_test']['mail_pass'];
            $this->php_mailer_config['security']   = $config['mail_test']['mail_security'];
            $this->php_mailer_config['port']       = $config['mail_test']['mail_port'];
        }

        if ($type === 'production') {
            $this->php_mailer_config['exceptions'] = $config['mail_exceptions'];
            $this->php_mailer_config['debug']      = (int)getenv('MAIL_DEBUG');
            $this->php_mailer_config['host']       = getenv('MAIL_HOST');
            $this->php_mailer_config['user']       = getenv('MAIL_USER');
            $this->php_mailer_config['pass']       = getenv('MAIL_PASS');
            $this->php_mailer_config['security']   = getenv('MAIL_SECURITY');
            $this->php_mailer_config['port']       = getenv('MAIL_PORT');
        }

        return $this->php_mailer_config;
    }
}
