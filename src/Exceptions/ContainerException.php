<?php

namespace Solital\Core\Exceptions;

use ModernPHPException\ModernPHPException;
use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \Exception implements ContainerExceptionInterface
{
    // Redefine the exception so message isn't optional
    public function __construct($id, \Exception $exception, $code = 0)
    {
        $message = "Error retrieving the entry: " . $id . "\nThrew an exception with the message: " . $exception->getMessage();

        (new ModernPHPException())->start()->errorHandler($code, $message, $exception->getFile(), $exception->getLine());
    }
}
