<?php
require_once 'Exception/MiddlewareLoadedException.php';

use Solital\Core\Http\Middleware\BaseMiddlewareInterface;

class DummyMiddleware implements BaseMiddlewareInterface
{
	public function handle() : void
	{
		throw new MiddlewareLoadedException('Middleware loaded!');
	}

}