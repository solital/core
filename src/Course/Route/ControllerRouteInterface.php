<?php

namespace Solital\Core\Course\Route;

use Solital\Core\Course\Route\RouteInterface;

interface ControllerRouteInterface extends RouteInterface
{
    /**
     * Get controller class-name
     *
     * @return string
     */
    public function getController(): string;

    /**
     * Set controller class-name
     *
     * @param string $controller
     * @return static
     */
    public function setController(string $controller): self;

    /**
     * Find url that matches method, parameters or name.
     * Used when calling the url() helper.
     *
     * @param string|null $method
     * @param string|array|null $parameters
     * @param string|null $name
     * 
     * @return string
     */
    public function findUrl(?string $method = null, $parameters = null, ?string $name = null): string;
}
