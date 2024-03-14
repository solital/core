<?php

use Solital\Core\Course\ClassLoader\ClassLoaderInterface;

class CustomClassLoader implements ClassLoaderInterface
{
    public function loadClass(string $class)
    {
        return new DummyController();
    }

    public function loadClosure(\Closure $closure, array $parameters)
    {
        return call_user_func_array($closure, [true]);
    }
}
