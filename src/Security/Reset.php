<?php

namespace Solital\Core\Security;

use Solital\Core\Resource\Mail;
use Solital\Database\Forgot\Forgot;

class Reset
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $column;

    /**
     * @param string $table
     * @return Reset
     */
    public function table(string $table, string $column): Reset
    {
        $this->table = $table;
        $this->column = $column;

        return $this;
    }

    /**
     * @param string $email
     * @param string $uri
     * @return bool
     */
    public function forgotPass(string $email, string $uri, string $time = "+1 hour"): bool
    {
        $sql = "SELECT * FROM ". $this->table ." WHERE ". $this->column ." = '$email';";
        $res = (new Forgot())->queryDatabase($sql);
        
        if (!$res == false) {
            $res = $this->updatePass($email, $uri, $time);

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
     * @param string $email
     * @param string $uri
     * @return bool
     */
    private function updatePass(string $email, string $uri, string $time): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $hash = Hash::encrypt($email, $time);
        $thisUri = request()->getHost();
        
        $message = "
            <h1>Forgot Password</h1>

            <p>Hello, click the link below to change your password</p>

            <p><a href='".$thisUri.$uri."/".$hash."' target='_blank' style='padding: 10px; background-color: red; 
            border-radius: 5px; color: #fff; text-decoration: none;'>Change Password</a></p>
            
            <small>Sent from the solital framework</small>
        ";

        $res = Mail::send(EMAIL['SENDER'], $email, "Forgot Password", $message, null, "text/html");

        if ($res == true) {
            return true;
        } else {
            return false;
        }
    }
}