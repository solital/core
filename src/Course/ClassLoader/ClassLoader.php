<?php

namespace Solital\Core\Course\ClassLoader;

use Solital\Core\Exceptions\RuntimeException;

class ClassLoader implements ClassLoaderInterface
{
    /**
     * Load class
     *
     * @param string $class
     * 
     * @return mixed
     * @throws RuntimeException
     */
    public function loadClass(string $class): mixed
    {
        if (class_exists($class) === false) {
            throw new RuntimeException("Class '$class' does not exist", 404);
        }

        return new $class();
    }

    /**
     * Load closure
     *
     * @param mixed $closure
     * @param array $parameters
     * 
     * @return mixed
     */
    public function loadClosure(mixed $closure, array $parameters): mixed
    {
        $reflect = new \ReflectionFunction($closure);

        if (!empty($reflect->getParameters())) {
            return call_user_func_array($closure, $parameters);
        }

        return call_user_func($closure, $parameters);
    }
}
