<?php

namespace Solital\Core\Security;

use Katrina\Katrina;
use Respect\Validation\Validator;
use Solital\Core\Mail\Mailer;
use Solital\Core\Auth\Password;
use Solital\Core\Exceptions\InvalidArgumentException;
use Solital\Core\Kernel\{Application, DebugCore, Dotenv};

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
     * @return void
     * @throws InvalidArgumentException
     */
    public static function validateDomain(): void
    {
        $config = Application::yamlParse('bootstrap.yaml');

        if ($config['verify_domain']['enable_verification'] == true) {
            self::checkEnvMail();
            self::$mailer = new Mailer();

            if (DebugCore::isCoreDebugEnabled() == false && Dotenv::isset('APP_DOMAIN') == false) {
                Dotenv::add('APP_DOMAIN', self::getUrl(), 'APP DOMAIN');
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

        $template = Application::getConsoleComponent('core-templates/template-verify-domain.php');
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
     * Protect route with basic authentication
     *
     * @param string $user
     * @param string $pass
     * 
     * @return void
     */
    public static function protectBasicAuth(string $user, #[\SensitiveParameter] string $pass): void
    {
        $url = self::getUrl();

        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="' . $url . '"');
            self::unauthorizeAuth();
        }

        if ($_SERVER['PHP_AUTH_USER'] != $user || $_SERVER['PHP_AUTH_PW'] != $pass) {
            header('WWW-Authenticate: Basic realm="' . $url . '"');
            self::unauthorizeAuth();
        }
    }

    /**
     * Protect route with digest authentication
     *
     * @param array $users_with_pass
     * 
     * @return void
     */
    public static function protectDigestAuth(#[\SensitiveParameter] array $users_with_pass): void
    {
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('WWW-Authenticate: Digest realm="Restricted area",qop="auth",nonce="' . uniqid() . '",opaque="' . md5('Restricted area') . '"');
            self::unauthorizeAuth();
        }

        if (
            !($data = self::httpDigestParse($_SERVER['PHP_AUTH_DIGEST'])) ||
            !isset($users_with_pass[$data['username']])
        ) {
            self::unauthorizeAuth();
        }

        $A1 = md5($data['username'] . ':Restricted area:' . $users_with_pass[$data['username']]);
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);

        if ($data['response'] != $valid_response) {
            self::unauthorizeAuth();
        }
    }

    /**
     * Unauthorize authentication
     *
     * @return never
     */
    public static function unauthorizeAuth(): never
    {
        header('HTTP/1.1 401 Unauthorized');
        http_response_code(401);

        echo 'Access denied. You are not allowed to access the router.';
        exit;
    }

    /**
     * @param mixed $txt
     * 
     * @return mixed
     */
    private static function httpDigestParse(mixed $txt): mixed
    {
        $needed_parts = ['nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1];
        $data = [];
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
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
