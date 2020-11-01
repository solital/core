<?php

namespace Solital\Core\Exceptions;

class NotFoundHttpException extends HttpException
{
    /**
     * @var bool
     */
    protected static $error = false;

    /**
     * @var string
     */
    protected static $url;
    
    /**
     * alertMessage
     *
     * @param int $code
     * @param string $msg
     * @return void
     */
    public static function alertMessage(int $code, string $msg) 
    {
        if (self::$error == true) {
            header('Location: ' . self::$url);
            die;
        }

        http_response_code($code);
        include_once dirname(__DIR__).'/Exceptions/templates/error-router.php';   
        die;
    }
}
