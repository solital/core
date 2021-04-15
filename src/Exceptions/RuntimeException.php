<?php

namespace Solital\Core\Exceptions;

class RuntimeException extends \RuntimeException
{
    /**
     * @param string $msg
     * 
     * @return void
     */
    public static function exceptionMessage(string $msg, int $code = 404, string $description = "", string $component = 'Solital'): void
    {
        include_once dirname(__DIR__) . '/Exceptions/templates/view-error.php';
        die;
    }
}
