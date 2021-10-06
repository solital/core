<?php

namespace Solital\Core\Course\ClassLoader;

use ReflectionFunction;
use Solital\Core\Exceptions\NotFoundHttpException;

class ClassLoader implements ClassLoaderInterface
{
    /**
     * @var Container|null
     */
    protected $container;

    /**
     * @var array
     */
    private array $closure_params = [];

    /**
     * @var array
     */
    private array $router_params = [];

    /**
     * Load class
     *
     * @param string $class
     * 
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function loadClass(string $class)
    {
        if (class_exists($class) === false) {
            throw new \Exception("Class '$class' does not exist", 404);
        }

        return new $class();
    }

    /**
     * Load closure
     *
     * @param \Closure $closure
     * @param array $parameters
     * 
     * @return void
     */
    public function loadClosure(\Closure $closure, array $parameters)
    {
        $this->closureHasParam($closure, $parameters);
    }

    /**
     * @param \Closure $closure
     * @param array $parameters
     * 
     * @return mixed|null
     * @throws NotFoundHttpException
     */
    public function closureHasParam(\Closure $closure, array $parameters)
    {
        $reflect = new ReflectionFunction($closure);

        foreach ($reflect->getParameters() as $closure_params) {
            $closure_params = (array)$closure_params;
            array_push($this->closure_params, $closure_params['name']);
        }

        foreach ($parameters as $key => $params) {
            array_push($this->router_params, $key);
        }

        $diff1 = array_diff($this->router_params, $this->closure_params);
        $diff2 = array_diff_assoc($this->closure_params, $this->router_params);

        if (empty($diff1) && empty($diff2)) {
            return \call_user_func_array($closure, $parameters);
        } else {
            throw new \Exception("Parameter not defined in the URL or function", 404);
        }

        return null;
    }
}
