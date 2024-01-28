<?php

namespace Solital\Core\Http\Middleware;

use Solital\Core\Http\Request;

/**
 * @deprecated Use Solital\Core\Http\Middleware\BaseMiddlewareInterface
 */
interface MiddlewareInterface
{
    /**
     * @param Request $request
     */
    public function handle(Request $request): void;
}
