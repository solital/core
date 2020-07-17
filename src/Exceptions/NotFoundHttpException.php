<?php

namespace Solital\Core\Exceptions;

class NotFoundHttpException extends HttpException 
{
    public static function alertMessage(int $code, string $msg) 
    {
        include_once dirname(__DIR__).'/Exceptions/templates/error-router.php';
        die;
    }
}
