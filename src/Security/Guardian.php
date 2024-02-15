<?php

namespace Solital\Core\Security;

use Katrina\Katrina;
use Solital\Core\Mail\Mailer;
use Solital\Core\Auth\Password;
use Solital\Core\Course\Course;
use Respect\Validation\Validator;
use Solital\Core\Resource\Session;
use Solital\Core\Kernel\Application;
use Solital\Core\Exceptions\InvalidArgumentException;
use Solital\Core\Kernel\Dotenv;

/** @phpstan-consistent-constructor */
class Guardian
{
    /**
     * @var string
     */
    private string $table;

    /**
     * @var Mailer
     */
    private static Mailer $mailer;

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

        if ((new Password())->verify($password, $res->$pass_column)) {
            return $res;
        }

        return false;
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
        }

        return false;
    }

    /**
     * Verify if Solital project is at another domain
     * 
     * @param string $domain
     * 
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validateDomain(): void
    {
        $config = Application::getYamlVariables(5, 'bootstrap.yaml');

        if ($config['verify_domain']['enable_verification'] == true) {
            self::checkEnvMail();
            self::$mailer = new Mailer();

            if (Application::DEBUG == false &&  Dotenv::isset('APP_DOMAIN') == false) {
                $file = fopen(dirname(__DIR__, 5) . DIRECTORY_SEPARATOR . ".env", "a+");

                if (!$file) {
                    throw new \Exception("Failed to open '.env' file");
                }

                fwrite($file, "\n\n# APP DOMAIN\n" . 'APP_DOMAIN="' . self::getUrl() . '"');

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
    }

    /**
     * @param string|null $send_to
     * @param string|null $recipient_name
     * 
     * @return void
     * @throws InvalidArgumentException
     */
    private static function sendTo(?string $send_to, ?string $recipient_name): void
    {
        if (!$send_to || !$recipient_name) {
            throw new InvalidArgumentException("Variables not defined in 'bootstrap.yaml' file: verify_domain");
        }

        $url = self::getUrl();
        $email_validation = Validator::email()->validate($send_to);

        if ($email_validation == false) {
            throw new InvalidArgumentException("Recipient e-mail not valid");
        }

        $template = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'template-verify-domain.php');
        $template = str_replace('{{ link }}', $url, $template);

        if ($url != getenv('APP_DOMAIN')) {
            self::$mailer->add(
                getenv('MAIL_USER'),
                'Solital Guardian',
                $send_to,
                $recipient_name
            )->send('Solital: Security Alert', $template);
        }
    }

    /**
     * Get atual url
     * 
     * @param string $uri
     * 
     * @return string
     */
    public static function getUrl(string $uri = null)
    {
        $http = 'http://';

        if (isset($_SERVER['HTTPS'])) {
            $http = 'https://';
        }

        $url = $http . $_SERVER['HTTP_HOST'];

        if (isset($uri)) {
            $url = $http . $_SERVER['HTTP_HOST'] . "/" . $uri;
        }

        return $url;
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
     * Check if email variable have defined
     * 
     * @return void
     * @throws InvalidArgumentException
     */
    private static function checkEnvMail(): void
    {
        if (
            getenv('MAIL_DEBUG') == "" ||
            getenv('MAIL_HOST') == "" ||
            getenv('MAIL_USER') == "" ||
            getenv('MAIL_PASS') == "" ||
            getenv('MAIL_SECURITY') == "" ||
            getenv('MAIL_PORT') == ""
        ) {
            throw new InvalidArgumentException("Email variables have not been defined in the '.env' file");
        }
    }
}
