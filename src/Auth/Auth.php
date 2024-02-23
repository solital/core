<?php

namespace Solital\Core\Auth;

use Solital\Core\Auth\Reset;
use Solital\Core\Course\Course;
use Solital\Core\Http\Controller\HttpControllerTrait;
use Solital\Core\Kernel\Application;
use Solital\Core\Security\{Hash, Guardian};
use Solital\Core\Resource\{Cookie, Session};

final class Auth extends Reset
{
    use HttpControllerTrait;

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
     * Check if exist an user
     * 
     * @param string $user
     * @param string $redirect
     * 
     * @return void
     */
    public static function check(string $user = '', string $redirect = ''): void
    {
        self::getEnv();

        if ($redirect == "") {
            $redirect = self::$login_url;
        }

        if (is_null(self::user($user))) {
            Course::response()->redirect($redirect);
            exit;
        }
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
     * @return bool
     */
    public function register(string $redirect = ""): bool
    {
        self::getEnv();

        $register = match (self::$type) {
            'login' => $this->registerLogin($redirect),
            'forgot' => $this->registerForgot(),
            'change' => $this->registerChange()
        };

        return $register;
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
     * Remember login in next time
     * 
     * @param string $form_name
     * @param int $time
     * 
     * @return Auth
     */
    public function remember(string $form_name, int $time = 644800): Auth
    {
        $form_value = $this->getRequestParams()->all([$form_name]);

        if (!empty($form_value)) {
            $value = filter_var($form_value[$form_name], FILTER_VALIDATE_BOOL);

            if ($value == true) {
                $this->remember = true;
                $this->time = $time;

                return $this;
            }
        }

        return $this;
    }

    /**
     * Redirect login if is remembering login
     * 
     * @param string $redirect
     * 
     * @return void
     */
    public static function isRemembering(string $redirect = ''): void
    {
        self::getEnv();

        if (Cookie::exists('auth_remember_login') == true) {
            Cookie::get('auth_remember_login');

            if ($redirect == "") {
                $redirect = self::$dashboard_url;
            }

            Course::response()->redirect($redirect);
            exit;
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
     * Custom mail sender when recovery password
     * 
     * @param string $mail_sender
     * 
     * @return Auth
     */
    public function customMailSender(string $mail_sender): Auth
    {
        $this->mail_sender = $mail_sender;

        return $this;
    }

    /**
     * Custom email template when recovery password
     * 
     * @param string $name_sender
     * @param string $name_recipient
     * @param string $subject
     * @param string $html_message
     * 
     * @return Auth
     */
    public function customMailFields(string $name_sender, string $name_recipient, string $subject, string $html_message = ""): Auth
    {
        $this->name_sender = $name_sender;
        $this->name_recipient = $name_recipient;
        $this->subject = $subject;
        $this->message_email = $html_message;

        return $this;
    }

    /**
     * Logoff application
     * 
     * @param string $user
     * @param string $redirect
     * 
     * @return void
     */
    public static function logoff(string $user = '', string $redirect = ""): void
    {
        self::getEnv();

        if ($redirect == "") {
            $redirect = self::$login_url;
        }

        self::deleteUser($user);
        Session::delete('auth_users');
        Cookie::unset('auth_remember_login');
        Course::response()->redirect($redirect);
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
     * @param string $username
     * 
     * @return mixed
     */
    public static function user(string $username = ''): mixed
    {
        $users = Session::get('auth_users');

        if ($username != '') {
            if (!is_null($users)) {
                //return (array_key_exists($username, $users)) ? $users[$username] : null;
                return $users[$username] ?? null;
            }
        }

        return $users;
    }

    /**
     * @param string $username
     * 
     * @return void
     */
    public static function registerUser(string $username): void
    {
        Session::set('auth_users', [$username => $username]);
    }

    /**
     * @param string $username
     * 
     * @return void
     */
    public static function deleteUser(string $username): void
    {
        Session::delete('auth_users', $username);
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
        $user_crypt = Hash::encrypt($user);

        $res = Guardian::verifyLogin()
            ->table(self::$table_db)
            ->fields($this->user_column, $this->pass_column, $user, $pass);

        if ($res) {
            if ($this->remember == true) {
                Cookie::setcookie('auth_remember_login', $user_crypt, time() + $this->time, "/");
            }

            self::registerUser($user);
            Course::response()->redirect($redirect);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function registerForgot(): bool
    {
        if (is_null($this->mail_sender)) {
            $this->mail_sender = getenv('MAIL_USER');
        }

        $res = $this->tableForgot(self::$table_db, $this->user_column)
            ->forgotPass($this->user_post, $this->pass_post);

        if ($res == true) {
            return true;
        }

        return false;
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
        }

        return false;
    }

    /**
     * @return void
     */
    private static function getEnv(): void
    {
        $config = Application::yamlParse('auth.yaml');
        self::$dashboard_url = $config['auth']['auth_dashboard_url'];
        self::$login_url = $config['auth']['auth_login_url'];
    }
}
