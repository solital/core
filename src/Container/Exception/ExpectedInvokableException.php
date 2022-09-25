<?php

namespace Solital\Core\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Thrown when we want to add an invokable definition but that definition
 * is not considered invokable. 
 * 
 * This is determined by the Container::invokable method.
 */
class ExpectedInvokableException extends \InvalidArgumentException implements ContainerExceptionInterface
{
}
