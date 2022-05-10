<?php

namespace Solital\Core\Http\Middleware;

interface BaseMiddlewareInterface
{
    /**
     * @return void
     */
    public function handle(): void;
}