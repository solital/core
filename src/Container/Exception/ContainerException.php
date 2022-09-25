<?php

namespace Solital\Core\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Thrown when a circular dependency reference is detected.
 *
 * A circular dependency is when a dependency ends up depending on itself.
 * This will cause an infinite loop so we use an array in the container to
 * track what it is creating so that it won't run into this problem. 
 * 
 * If we do, we throw this exception to prevent infinite loops.
 */
class ContainerException extends \InvalidArgumentException implements ContainerExceptionInterface
{
}
