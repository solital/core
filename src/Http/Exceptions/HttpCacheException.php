<?php

namespace Solital\Core\Http\Exceptions;

use Solital\Core\Exceptions\HttpException;

class HttpCacheException extends HttpException
{
    /**
     * @param int $code
     * @param string $msg
     * 
     * @return void
     */
    public static function alertMessage(int $code, string $msg): void
    {
        include_once dirname(__DIR__, 2) . '/Exceptions/templates/error-router.php';
        die;
    }
}
