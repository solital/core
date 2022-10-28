<?php

namespace Solital\Core\Security;

use Katrina\Katrina;
use Respect\Validation\Validator;
use Solital\Core\Mail\Mailer;
use Solital\Core\Auth\Password;
use Solital\Core\Resource\Session;
use Solital\Core\Kernel\Application;
use Solital\Core\Exceptions\InvalidArgumentException;

class Guardian
{
    /**
     * @var string
     */
    private string $table;

    /**
     * @var Password
     */
    private Password $password;

    /**
     * @var Mailer
     */
    private static Mailer $mailer;

    public function __construct()
    {
        $this->password = new Password();
    }

    /**
     * Verify login
     * 
     * @return self
     */
    public static function verifyLogin(): self
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
     * 
     * @return mixed
     */
    public function fields(string $email_column, string $pass_column, string $email, string $password): mixed
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE $email_column = '$email';";
        $res = Katrina::customQuery($sql, false);

        if (!is_object($res) || $res == false) {
            return false;
        }

        if ($this->password->verify($password, $res->$pass_column)) {
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
        $res = Katrina::customQuery($sql, false);

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
        Session::set($index, $session);
        response()->redirect($redirect);
        exit;
    }

    /**
     * @param string $domain
     * 
     * @return self
     * @throws InvalidArgumentException
     */
    public static function validateDomain(): self
    {
        $config = Application::getYamlVariables(5, 'bootstrap.yaml');

        if ($config['verify_domain']['enable_verification'] == true) {
            self::checkEnvMail();
            self::$mailer = new Mailer();

            if (Application::DEBUG == false &&  empty(getenv('APP_DOMAIN'))) {
                $file = fopen(dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . ".env", "a+");

                if (!$file) {
                    throw new \Exception("Failed to open '.env' file");
                }

                fwrite($file, "\n\n# APP DOMAIN\n" . 'APP_DOMAIN="' . get_url() . '"');

                while (($line = fgets($file)) !== false) {
                    echo $line;
                }

                if (!feof($file)) {
                    throw new \Exception("fgets() unexpected failure");
                }

                fclose($file);
            }

            self::sendTo(
                $config['verify_domain']['send_to'],
                $config['verify_domain']['recipient_name']
            );
        }

        return new static;
    }

    /**
     * @param string|null $send_to
     * @param string|null $recipient_name
     * 
     * @throws InvalidArgumentException
     */
    private static function sendTo(?string $send_to, ?string $recipient_name)
    {
        if (!$send_to || !$recipient_name) {
            throw new InvalidArgumentException("Variables not defined in 'bootstrap.yaml' file: verify_domain");
        }

        $url = get_url();
        $email_validation = Validator::email()->validate($send_to);

        if ($email_validation == false) {
            throw new InvalidArgumentException("Recipient email not valid");
        }

        if ($url != getenv('APP_DOMAIN')) {
            self::$mailer->add(
                getenv('MAIL_USER'),
                'Solital Guardian',
                $send_to,
                $recipient_name
            )->send('Solital: Security Alert', 'We detected that your project made in Solital is in another domain. The detected domain is: ' . $url);
        }
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
     * @throws InvalidArgumentException
     */
    private static function verifyConstants(): void
    {
        if ($_ENV['INDEX_LOGIN'] == "" || empty($_ENV['INDEX_LOGIN'])) {
            throw new InvalidArgumentException("INDEX_LOGIN not defined", 404);
        }
    }

    /**
     * Check if email variable have defined
     * 
     * @return void
     * @throws InvalidArgumentException
     */
    private static function checkEnvMail(): void
    {
        if (
            !getenv('MAIL_DEBUG') || getenv('MAIL_DEBUG') == "" ||
            !getenv('MAIL_HOST') || getenv('MAIL_HOST') == "" ||
            !getenv('MAIL_USER') || getenv('MAIL_USER') == "" ||
            !getenv('MAIL_PASS') || getenv('MAIL_PASS') == "" ||
            !getenv('MAIL_SECURITY') || getenv('MAIL_SECURITY') == "" ||
            !getenv('MAIL_PORT') || getenv('MAIL_PORT') == ""
        ) {
            throw new InvalidArgumentException("Email variables have not been defined in the '.env' file");
        }
    }
}
