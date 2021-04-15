<?php

namespace Solital\Core\Exceptions;

use Solital\Core\Exceptions\HttpException;

class InvalidArgumentHttpException extends HttpException
{
    /**
     * @param int $code
     * @param string $msg
     * @param string $description
     * @param string $component
     * 
     * @return void
     */
    public static function invalidExceptionMessage(int $code, string $msg, string $description = "", string $component = 'Solital'): void
    {
        include_once dirname(__DIR__, 2) . '/Exceptions/templates/view-error.php';
        die;
    }
}
