<?php

namespace Solital\Core\Exceptions;

use ModernPHPException\ModernPHPException;
use Psr\Container\NotFoundExceptionInterface;

class ContainerNotFoundException extends \Exception implements NotFoundExceptionInterface
{
    /**
     * @param mixed $id
     */
    public function __construct($id)
    {
        (new ModernPHPException())->start()->errorHandler(404, "Dependency \"" . $id . "\" not found", __FILE__, __LINE__);
    }
}
