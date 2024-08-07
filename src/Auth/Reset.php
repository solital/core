<?php

namespace Solital\Core\Auth;

use Katrina\Sql\KatrinaStatement;
use Solital\Core\Mail\Mailer;
use Solital\Core\Auth\Password;
use Solital\Core\Kernel\Application;
use Solital\Core\Security\{Guardian, Hash};

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
     * @var string
     */
    protected string $link = "";

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
    protected string $subject = "Reset Password";

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
    protected ?string $mail_sender = null;

    /**
     * Construct
     */
    public function __construct(
        private readonly Mailer $mailer = new Mailer()
    ) {
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
     * 
     * @return bool
     */
    public function forgotPass(string $email, string $uri): bool
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE " . $this->column . " = '$email';";
        $res = KatrinaStatement::executeQuery($sql, false);

        if (!$res == false) {
            $res = $this->sendHash($email, $uri, $this->time_hash);

            if ($res == true) return true;
            return false;
        }

        return false;
    }

    /**
     * @param string $table
     * @param string $column_user
     * @param string $column_pass
     * @param string $email
     * @param string $value
     * 
     * @return bool
     */
    public function changePass(
        string $table,
        string $column_user,
        string $column_pass,
        string $email,
        string $value
    ): bool {
        $value_hash = (new Password())->create($value);
        $sql = "UPDATE $table SET $column_pass = '$value_hash' WHERE $column_user = '$email'";

        return KatrinaStatement::executePrepare($sql);
    }

    /**
     * @param string $email
     * @param string $time
     * 
     * @return string
     */
    private function generateDefaultLink(string $uri, string $email, string $time): ?string
    {
        if ($this->link == "") {
            $domain = Guardian::getUrl();
            $hash = Hash::encrypt($email, $time);
            $this->link = $domain . $uri . $hash;

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
        $valid = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($valid == null) return false;

        $this->generateDefaultLink($uri, $email, $time);

        if ($this->message_email == '') {
            $template = Application::getConsoleComponent('core-templates/template-recovery-password.php');
            $template = file_get_contents($template);
            $template = str_replace(['{{ subject }}', '{{ name_recipient }}', '{{ link }}'], [$this->subject, $this->name_recipient, $this->link], $template);
            $this->message_email = $template;
        }

        $res = $this->mailer->add($this->mail_sender, $this->name_sender, $email, $this->name_recipient)
            ->send($this->subject, $this->message_email);

        if ($this->mailer->error()) {
            echo $this->mailer->error();
            exit;
        }

        if ($res == true) return true;
        return false;
    }
}
