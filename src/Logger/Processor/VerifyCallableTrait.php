<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Processor;

use Solital\Core\Logger\Entry\LogEntryInterface;

trait VerifyCallableTrait
{
    /**
     * callable parameter class to match with
     *
     * @var string
     */
    protected static $parameterClass = LogEntryInterface::class;

    /**
     * Callable must take ONE parameter of THIS type $className
     *
     * ```php
     * callable(className $object) {
     * }
     * ```
     *
     * @param callable $callable
     * @param string   $className  parameter classname
     * 
     * @return string parameter real classname
     * @throws \LogicException if not valid processor
     */
    protected static function verifyCallable(
        callable $callable,
        ?string $className = NULL
    ): string {
        try {
            $parameters = static::getCallableParameters($callable);
            $className = $className ?? self::$parameterClass;
            if (
                1 !== count($parameters) ||
                !is_a($parameters[0]->getType()->getName(), $className, true)
            ) {
                throw new \InvalidArgumentException("non valid processor found");
            }

            return $parameters[0]->getType()->getName();
        } catch (\Throwable $e) {
            throw new \LogicException($e->getMessage());
        }
    }

    /**
     * Get callable parameters
     *
     * @param  callable $callable
     * 
     * @return \ReflectionParameter[]
     * @throws \InvalidArgumentException if something goes wrong
     */
    protected static function getCallableParameters(callable $callable): array
    {
        try {
            if (is_array($callable)) { // [class, method]
                $reflector = new \ReflectionClass($callable[0]);
                $method = $reflector->getMethod($callable[1]);
            } elseif (is_string($callable) || $callable instanceof \Closure) { // function
                $method = new \ReflectionFunction($callable);
            } else { // __invokable
                $reflector = new \ReflectionClass($callable);
                $method = $reflector->getMethod('__invoke');
            }
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        return $method->getParameters();
    }
}
