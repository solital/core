<?php

namespace Solital\Core\Resource;

use Solital\Core\Resource\Session;

final class Message
{
    const CLEAN = 'clean';
    const INFO = 'info';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const ERROR = 'error';
    
    /**
     * @var string
     */
    private string $htmlOpen1 = "<div class=\"";
    
    /**
     * @var string
     */
    private string $htmlOpen2 = "\">";
    
    /**
     * @var string
     */
    private string $htmlClose = "</div>";

    /**
     * Create a new flash message
     * 
     * @param string $index
     * @param string $message
     * 
     * @return void
     */
    public function new(string $index, string $message): void
    {
        $_SESSION['solital_flash_messages'][self::CLEAN][$index] = $message;
        Session::set($index, $message);
    }

    /**
     * Create a new info flash message
     * 
     * @param string $index
     * @param string $message
     * @param string $class
     * 
     * @return void
     */
    public function info(string $index, string $message, string $class = "alert-info"): void
    {
        $message = $this->htmlOpen1 . $class . $this->htmlOpen2 . $message . $this->htmlClose;
        $this->customNewMessage($index, self::INFO, $message);
    }

    /**
     * Create a new success flash message
     * 
     * @param string $index
     * @param string $message
     * @param string $class
     * 
     * @return void
     */
    public function success(string $index, string $message, string $class = "alert-success"): void
    {
        $message = $this->htmlOpen1 . $class . $this->htmlOpen2 . $message . $this->htmlClose;
        $this->customNewMessage($index, self::SUCCESS, $message);
    }

    /**
     * Create a new warning flash message
     * 
     * @param string $index
     * @param string $message
     * @param string $class
     * 
     * @return void
     */
    public function warning(string $index, string $message, string $class = "alert-warning"): void
    {
        $message = $this->htmlOpen1 . $class . $this->htmlOpen2 . $message . $this->htmlClose;
        $this->customNewMessage($index, self::WARNING, $message);
    }

    /**
     * Create a new error flash message
     * 
     * @param string $index
     * @param string $message
     * @param string $class
     * 
     * @return void
     */
    public function error(string $index, string $message, string $class = "alert-error"): void
    {
        $message = $this->htmlOpen1 . $class . $this->htmlOpen2 . $message . $this->htmlClose;
        $this->customNewMessage($index, self::ERROR, $message);
    }

    /**
     * Get a flash message and unset after
     * 
     * @param string $index
     * 
     * @return null|string
     */
    public function get(string $index): ?string
    {
        $levels = [
            self::CLEAN,
            self::INFO,
            self::SUCCESS,
            self::WARNING,
            self::ERROR
        ];

        foreach ($levels as $level) {
            if (isset($_SESSION['solital_flash_messages'][$level][$index])) {
                try {
                    return (string)$_SESSION['solital_flash_messages'][$level][$index];
                } finally {
                    unset($_SESSION['solital_flash_messages'][$level][$index]);
                    //Session::delete($index);
                }
            }
        }

        return null;
    }

    /**
     * Check if has a flash message
     *
     * @param string $index
     * @param string $level
     * 
     * @return bool
     */
    public function has(string $index, ?string $level = null): bool
    {
        if ($level === null) {
            $level = self::CLEAN;
        }

        return (isset($_SESSION['solital_flash_messages'][$level][$index])) ? true : false;
    }

    /**
     * See if there are any queued error messages
     * 
     * @return bool 
     */
    public function hasErrors()
    {
        return empty($_SESSION['solital_flash_messages'][self::ERROR]) ? false : true;
    }

    /**
     * Create a flash mesage with custom level
     *
     * @param string $index
     * @param string $type
     * @param string $message
     * 
     * @return void
     */
    private function customNewMessage(string $index, string $type, string $message): void
    {
        $_SESSION['solital_flash_messages'][$type][$index] = $message;
        //Session::set($index, $message);
    }
}