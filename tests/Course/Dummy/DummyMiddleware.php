<?php
require_once 'Exception/MiddlewareLoadedException.php';

use Solital\Core\Http\Request;
use Solital\Core\Http\Middleware\MiddlewareInterface;

class DummyMiddleware implements MiddlewareInterface
{
	public function handle(Request $request) : void
	{
		throw new MiddlewareLoadedException('Middleware loaded!');
	}

}