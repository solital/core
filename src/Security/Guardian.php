<?php

namespace Solital\Core\Security;

use Respect\Validation\Validator;
use Solital\Core\Database\ORM;
use Solital\Core\Resource\Mail\PHPMailerClass;
use Solital\Core\Resource\Session;

class Guardian
{
    /**
     * @var string
     */
    private string $table;

    /**
     * @var PHPMailerClass
     */
    private static PHPMailerClass $mailer;

    /**
     * @var string
     */
    private static string $domain;

    /**
     * Verify login
     */
    public static function verifyLogin()
    {
        return new static;
    }

    /**
     * @param string $table
     * 
     * @return Guardian
     */
    public function table(string $table): Guardian
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param string $email_column
     * @param string $pass_column
     * @param string $email
     * @param string $password
     */
    public function fields(string $email_column, string $pass_column, string $email, string $password)
    {
        $sql = "SELECT * FROM $this->table WHERE $email_column = '$email';";
        $res = ORM::query($sql);

        if (!is_array($res) || !$res) {
            return false;
        }

        if (password_verify($password, $res[$pass_column])) {
            return $res;
        } else {
            return false;
        }
    }

    /**
     * @param string $table
     * @param string $email_column
     * @param string $email
     * 
     * @return bool
     */
    public static function verifyEmail(string $table, string $email_column, string $email): bool
    {
        $sql = "SELECT * FROM $table WHERE $email_column = '$email';";
        $res = ORM::query($sql);

        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $redirect
     * @param string $session
     * @param string $index
     * 
     * @return void
     */
    public static function validate(string $redirect, string $session, string $index = ''): void
    {
        if ($index == '') {
            $index = $_ENV['INDEX_LOGIN'];
        }

        self::verifyConstants();
        Session::new($index, $session);
        response()->redirect($redirect);
        exit;
    }

    /**
     * @param string $domain
     * 
     * @return new static
     */
    public static function validateDomain(string $domain)
    {
        self::checkEnvMail();
        self::$mailer = new PHPMailerClass();
        self::$domain = $domain;

        $domain_validation = Validator::url()->validate($domain);

        if ($domain_validation == false) {
            throw new \Exception("Domain is not valid");
        }

        return new static;
    }

    /**
     * @param string $recipient_name
     * @param string $recipient_mail
     * 
     * @return Guardian
     */
    public function sendTo(string $recipient_name, string $recipient_mail): Guardian
    {
        $url = get_url();

        $email_validation = Validator::email()->validate($recipient_mail);

        if ($email_validation == false) {
            throw new \Exception("Recipient email not valid");
        }

        if ($url != self::$domain) {
            self::$mailer->add(getenv('PHPMAILER_USER'), 'Solital Guardian', $recipient_mail, $recipient_name)
                ->sendEmail('Solital: Security Alert', 'We detected that your project made in Solital is in another domain. The detected domain is: ' . $url);
        }

        return $this;
    }

    /**
     * Return error
     */
    public function error()
    {
        if (self::$mailer->error()) {
            return self::$mailer->error();
        }
    }

    /**
     * Checks for constants
     * 
     * @return void
     */
    private static function verifyConstants(): void
    {
        if ($_ENV['INDEX_LOGIN'] == "" || empty($_ENV['INDEX_LOGIN'])) {
            throw new \Exception("INDEX_LOGIN not defined", 404);
        }
    }

    /**
     * Check if email variable have defined
     */
    private static function checkEnvMail()
    {
        if (
            getenv('PHPMAILER_DEBUG') || getenv('PHPMAILER_HOST') || getenv('PHPMAILER_USER')
            || getenv('PHPMAILER_PASS') || getenv('PHPMAILER_SECURITY') || getenv('PHPMAILER_PORT')
        ) {
            throw new \Exception("Email variables have not been defined in the .env file");
        }
    }
}
