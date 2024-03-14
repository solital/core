<?php

use Solital\Core\Course\Router;
use Solital\Core\Course\RouterBootManagerInterface;
use Solital\Core\Http\Request;

class FindUrlBootManager implements RouterBootManagerInterface
{
    protected mixed $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    /**
     * Called when router loads it's routes
     *
     * @param Router $router
     * @param Request $request
     */
    public function boot(Router $router, Request $request): void
    {
        $contact = TestRouter::getUri('contact')->getPath();

        if ($contact != null) {
            $this->setResult(true);
        }
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }
}
