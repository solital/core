<?php

use Solital\Core\Http\Request;
use Solital\Core\Http\Middleware\MiddlewareInterface;

class RewriteMiddleware implements MiddlewareInterface {

    public function handle(Request $request)  : void {

        $request->setRewriteCallback(function() {
            return 'ok';
        });

    }

}