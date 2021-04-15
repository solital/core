<?php

namespace Solital\Core\Exceptions;

use Solital\Core\Exceptions\HttpException;

class NotFoundException extends HttpException
{
    /**
     * @param int $code
     * @param string $msg
     * @param string $description
     * @param string $component
     * 
     * @return void
     */
    public static function notFound(int $code, string $msg, string $description = "", string $component = 'Solital'): void
    {
        include_once dirname(__DIR__) . '/Exceptions/templates/view-error.php';
        die;
    }
}
