<?php

namespace Solital\Core\Auth;

use PDO;
use Katrina\Connection\DB;
use Solital\Core\Database\ORM;
use Solital\Core\Security\Hash;
use Solital\Core\Resource\Mail\NativeMail;
use Solital\Core\Resource\Mail\PHPMailerClass;
use Solital\Core\Resource\Validation\Valid;

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
     * @var PHPMailerClass
     */
    private $mailer;

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
     * @var bool
     */
    protected bool $mailerExceptions = false;

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
    protected string $name_sender = "User";

    /**
     * Construct
     */
    public function __construct()
    {
        $this->mailer = new PHPMailerClass($this->mailerExceptions);
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
        $res = ORM::query($sql);

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
        $value_hash = pass_hash($value);

        $sql = "UPDATE $table SET $column_pass = ? WHERE $column_user = '$email'";

        try {
            $stmt = DB::prepare($sql);
            $stmt->bindValue(1, $value_hash, PDO::PARAM_STR);
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
     * @return null|string
     */
    private function generateDefaultLink(string $uri, string $email, string $time): ?string
    {
        if ($this->link = "") {
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

        $res = $this->mailer->add($_ENV['MAIL_SENDER'], $this->name_sender, $email, $this->name_recipient)
            ->sendEmail($this->subject, $this->message);

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
