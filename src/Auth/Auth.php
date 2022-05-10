<?php

namespace Solital\Core\Auth;

use Symfony\Component\Yaml\Yaml;
use Solital\Core\Auth\Reset;
use Solital\Core\Kernel\Application;
use Solital\Core\Resource\{Cookie, Session};
use Solital\Core\Security\{Hash, Guardian};

class Auth extends Reset
{
    /**
     * @var string
     */
    protected static string $table_db;

    /**
     * @var string
     */
    private static string $type;

    /**
     * @var string
     */
    private string $user_column;

    /**
     * @var string
     */
    private string $pass_column;

    /**
     * @var string
     */
    private string $user_post;

    /**
     * @var string
     */
    private string $pass_post;

    /**
     * @var bool
     */
    private bool $remember = false;

    /**
     * @var int
     */
    private int $time;

    /**
     * @var string
     */
    protected static string $login_url;

    /**
     * @var string
     */
    protected static string $dashboard_url;

    /**
     * @param string $index
     * 
     * @return null
     */
    public static function isLogged(string $redirect = "", string $index = "")
    {
        self::getEnv();

        if ($index == '') {
            $index = $_ENV['INDEX_LOGIN'];
        }

        if ($redirect == "") {
            $redirect = self::$dashboard_url;
        }

        if (isset($_SESSION[$index])) {
            response()->redirect($redirect);
            exit;
        }

        return null;
    }

    /**
     * @param string $index
     * 
     * @return null
     */
    public static function isNotLogged(string $redirect = "", string $index = "")
    {
        self::getEnv();

        if ($index == '') {
            $index = $_ENV['INDEX_LOGIN'];
        }

        if ($redirect == "") {
            $redirect = self::$login_url;
        }

        if (empty($_SESSION[$index])) {
            response()->redirect($redirect);
            exit;
        }

        return null;
    }

    /**
     * @param string $table
     * 
     * @return static
     */
    public static function login(string $table)
    {
        self::$type = 'login';
        self::$table_db = $table;

        return new static;
    }

    /**
     * @param string $table
     * 
     * @return static
     */
    public static function forgot(string $table)
    {
        self::$type = 'forgot';
        self::$table_db = $table;

        return new static();
    }

    /**
     * @param string $table
     * 
     * @return static
     */
    public static function change(string $table)
    {
        self::$type = 'change';
        self::$table_db = $table;

        return new static();
    }

    /**
     * Authenticates the user in the system
     * 
     * @return mixed
     */
    public function register(string $redirect = "")
    {
        self::getEnv();

        switch (self::$type) {
            case 'login':
                return $this->registerLogin($redirect);

                break;

            case 'forgot':
                return $this->registerForgot();

                break;

            case 'change':
                return $this->registerChange();

                break;
        }

        return $this;
    }

    /**
     * Database columns
     * 
     * @param string $user_column
     * @param string $pass_column
     */
    public function columns(string $user_column, string $pass_column = ""): self
    {
        $this->user_column = $user_column;
        $this->pass_column = $pass_column;

        return $this;
    }

    /**
     * HTML input values
     * 
     * @param string $user_post
     * @param string $pass_post
     */
    public function values(string $user_post, string $pass_post): self
    {
        $this->user_post = $user_post;
        $this->pass_post = $pass_post;

        return $this;
    }

    /**
     * @param int $time
     * 
     * @return Auth
     */
    public function remember(int $time = 644800): Auth
    {
        $this->remember = true;
        $this->time = $time;

        return $this;
    }

    /**
     * @param string $redirect
     */
    public static function isRemembering(string $redirect)
    {
        if (Cookie::exists('auth_remember_login') == true) {
            $user = Cookie::get('auth_remember_login');
            Guardian::validate($redirect, $user);
        }
    }

    /**
     * @param string $time
     * 
     * @return Auth
     */
    public function timeHash(string $time): Auth
    {
        $this->time_hash = $time;

        return $this;
    }

    /**
     * @param string $mail_sender
     * 
     * @return Auth
     */
    public function mailSender(string $mail_sender): Auth
    {
        $this->mail_sender = $mail_sender;

        return $this;
    }

    /**
     * @param string $name_sender
     * @param string $name_recipient
     * @param string $subject
     * @param string $message
     * 
     * @return Auth
     */
    public function fields(string $name_sender, string $name_recipient, string $subject, string $message = ""): Auth
    {
        $this->name_sender = $name_sender;
        $this->name_recipient = $name_recipient;
        $this->subject = $subject;
        $this->message_email = $message;

        return $this;
    }

    /**
     * @param string $redirect
     * @param string $index
     * 
     * @return void
     */
    public static function logoff(string $redirect = "", string $index = ''): void
    {
        self::getEnv();

        if ($index == '') {
            $index = $_ENV['INDEX_LOGIN'];
        }

        if ($redirect == "") {
            $redirect = self::$login_url;
        }

        Session::delete($index);
        Cookie::unset("auth_remember_login");
        response()->redirect($redirect);
        exit;
    }

    /**
     * @param string $message
     * @param string $key
     * 
     * @return string
     */
    public static function sodium(string $message, string $key): string
    {
        Hash::checkSodium();

        $ciphertext = sodium_crypto_auth($message, $key);
        $encoded = base64_encode($ciphertext);

        return $encoded;
    }

    /**
     * @param string $hash
     * @param string $message
     * @param string $key
     * 
     * @return bool
     */
    public static function sodiumVerify(string $hash, string $message, string $key): bool
    {
        Hash::checkSodium();

        $decoded = base64_decode($hash);
        $result = sodium_crypto_auth_verify($decoded, $message, $key);

        return $result;
    }

    /**
     * @param string $redirect
     * 
     * @return bool
     */
    private function registerLogin(string $redirect): bool
    {
        if ($redirect == "") {
            $redirect = self::$dashboard_url;
        }

        $user = filter_input(INPUT_POST, $this->user_post);
        $pass = filter_input(INPUT_POST, $this->pass_post);

        $res = Guardian::verifyLogin()
            ->table(self::$table_db)
            ->fields($this->user_column, $this->pass_column, $user, $pass);

        if ($res) {
            if ($this->remember == true) {
                Cookie::setcookie("auth_remember_login", $this->user_post, time() + $this->time, "/");
            }

            Guardian::validate($redirect, $user);
        } else {
            return false;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function registerForgot(): bool
    {
        $res = $this->tableForgot(self::$table_db, $this->user_column)
            ->forgotPass($this->user_post, $this->pass_post);

        if ($res == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function registerChange(): bool
    {
        $res = $this->changePass(
            self::$table_db,
            $this->user_column,
            $this->pass_column,
            $this->user_post,
            $this->pass_post
        );

        if ($res == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return void
     */
    private static function getEnv(): void
    {
        $config = Yaml::parseFile(Application::getDirConfigFiles(5) . 'auth.yaml');
        self::$dashboard_url = $config['auth']['auth_dashboard_url'];
        self::$login_url = $config['auth']['auth_login_url'];
    }
}
