<?php

namespace Solital\Core\Auth;

use Katrina\Sql\KatrinaStatement;
use Katrina\Connection\Connection;
use Solital\Core\Mail\Mailer;
use Solital\Core\Auth\Password;
use Solital\Core\Security\Hash;
use Solital\Core\Validation\Valid;

abstract class Reset
{
    /**
     * @var string
     */
    private string $table;

    /**
     * @var string
     */
    private string $column;

    /**
     * @var Mailer
     */
    private Mailer $mailer;

    /**
     * @var string
     */
    protected string $link;

    /**
     * @var string
     */
    protected string $time_hash = "+1 hour";

    /**
     * @var string
     */
    protected string $message_email = "";

    /**
     * @var string
     */
    protected string $subject = "Forgot Password";

    /**
     * @var string
     */
    protected string $name_recipient = "User";

    /**
     * @var string
     */
    protected string $name_sender = "Solital Framework";

    /**
     * @var string
     */
    protected string $mail_sender;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->mailer = new Mailer();
    }

    /**
     * @param string $table
     * 
     * @return Reset
     */
    public function tableForgot(string $table, string $column): Reset
    {
        $this->table = $table;
        $this->column = $column;

        return $this;
    }

    /**
     * @param string $email
     * @param string $uri
     * @param string $time
     * 
     * @return bool
     */
    public function forgotPass(string $email, string $uri): bool
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE " . $this->column . " = '$email';";

        $res = KatrinaStatement::executeQuery($sql, false);

        if (!$res == false) {
            $res = $this->sendHash($email, $uri, $this->time_hash);

            if ($res == true) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string $table
     * @param string $column_user
     * @param string $column_pass
     * @param string $email
     * @param string $value
     * 
     * @return bool|null
     */
    public function changePass(
        string $table,
        string $column_user,
        string $column_pass,
        string $email,
        string $value
    ): ?bool {
        $value_hash = (new Password())->create($value);

        $sql = "UPDATE $table SET $column_pass = ? WHERE $column_user = '$email'";

        try {
            $stmt = Connection::getInstance()->prepare($sql);
            $stmt->bindValue(1, $value_hash, \PDO::PARAM_STR);
            $stmt->execute();

            return true;
        } catch (\PDOException $e) {
            return false;
        }

        return null;
    }

    /**
     * @param string $email
     * @param string $time
     * 
     * @return string
     */
    private function generateDefaultLink(string $uri, string $email, string $time): ?string
    {
        if (empty($this->link)) {
            $hash = Hash::encrypt($email, $time);
            $this->link = $uri . $hash;

            return $this->link;
        }

        return null;
    }

    /**
     * @param string $email
     * @param string $uri
     * @param string $time
     * 
     * @return bool
     */
    private function sendHash(string $email, string $uri, string $time): bool
    {
        $valid = Valid::email($email);

        if ($valid == null) {
            return false;
        }

        $this->generateDefaultLink($uri, $email, $time);

        if ($this->message_email == '') {
            $this->message_email = "
            <h1>" . $this->subject . "</h1>

            <p>Hello " . $this->name_recipient . ", click the link below to change your password</p>

            <p><a href='" . $this->link . "' target='_blank' style='padding: 10px; background-color: red; 
            border-radius: 5px; color: #fff; text-decoration: none;'>Change Password</a></p>
            
            <small>Sent from the solital framework</small>";
        }

        $res = $this->mailer->add($this->mail_sender, $this->name_sender, $email, $this->name_recipient)
            ->send($this->subject, $this->message_email);

        if ($this->mailer->error()) {
            echo $this->mailer->error();
            exit;
        }

        if ($res == true) {
            return true;
        } else {
            return false;
        }
    }
}
