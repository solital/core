<?php

namespace Solital\Core\Http\Exceptions;

use Solital\Core\Exceptions\HttpException;

class InvalidArgumentException extends HttpException
{
    public static function alertMessage(int $code, string $msg) 
    {
        include_once dirname(__DIR__, 2).'/Exceptions/templates/error-router.php';
        die;
    }
}
