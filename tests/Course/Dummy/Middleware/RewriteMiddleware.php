<?php

use Solital\Core\Http\Middleware\BaseMiddlewareInterface;
use Solital\Core\Http\Request;

class RewriteMiddleware implements BaseMiddlewareInterface
{
    public function handle(): void
    {
        echo 'middleware-';
    }
}
