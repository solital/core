<?php

namespace Solital\Core\Course\Route;

use Solital\Core\Http\Request;
use Solital\Core\Exceptions\ExceptionHandlerInterface;

interface GroupRouteInterface extends RouteInterface
{
    /**
     * Method called to check if a domain matches
     *
     * @param Request $request
     * @return bool
     */
    public function matchDomain(Request $request): bool;

    /**
     * Add exception handler
     *
     * @param ExceptionHandlerInterface|string $handler
     * @return static
     */
    public function addExceptionHandler($handler): self;

    /**
     * Set exception-handlers for group
     *
     * @param array $handlers
     * @return static
     */
    public function setExceptionHandlers(array $handlers);

    /**
     * Get exception-handlers for group
     *
     * @return array
     */
    public function getExceptionHandlers(): array;

    /**
     * Get domains for domain.
     *
     * @return array
     */
    public function getDomains(): array;

    /**
     * Set allowed domains for group.
     *
     * @param array $domains
     * @return static
     */
    public function setDomains(array $domains): self;

    /**
     * Set prefix that child-routes will inherit.
     *
     * @param string $prefix
     * @return static
     */
    public function setPrefix($prefix): self;

    /**
     * Get prefix.
     *
     * @return string|null
     */
    public function getPrefix(): ?string;
}
