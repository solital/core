<?php

namespace Solital\Core\Course\ClassLoader;

interface ClassLoaderInterface
{
    /**
     * @param string $class
     * 
     * @return void
     */
    public function loadClass(string $class);

    /**
     * @param \Closure $closure
     * @param array $parameters
     * 
     * @return void
     */
    public function loadClosure(\Closure $closure, array $parameters);

}