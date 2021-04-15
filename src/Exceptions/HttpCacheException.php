<?php

namespace Solital\Core\Exceptions;

use Solital\Core\Exceptions\HttpException;

class HttpCacheException extends HttpException
{
    /**
     * @param int $code
     * @param string $msg
     * 
     * @return void
     */
    public static function alertMessage(int $code, string $msg, string $description = "", string $component = 'HTTP Cache'): void
    {
        include_once dirname(__DIR__, 2) . '/Exceptions/templates/view-error.php';
        die;
    }
}
