<?php

namespace Solital\Core\Security;

use Solital\Core\Database\ORM;
use Solital\Core\Resource\Session;
use Solital\Core\Exceptions\NotFoundException;

class Guardian
{
    /**
     * @var string
     */
    private string $table;

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
     * Checks for constants
     * 
     * @return void
     */
    private static function verifyConstants(): void
    {
        if ($_ENV['INDEX_LOGIN'] == "" || empty($_ENV['INDEX_LOGIN'])) {
            NotFoundException::notFound(404, "INDEX_LOGIN not defined", "You have not determined any 
            indexes in the INDEX_LOGIN constant. Check your 'config.php' file", "Guardian");
        }
    }
}
