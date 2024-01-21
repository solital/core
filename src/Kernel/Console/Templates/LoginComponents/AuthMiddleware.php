<?php 

namespace Solital\Middleware;

use Solital\Core\Auth\Auth;
use Solital\Core\Http\Middleware\BaseMiddlewareInterface;

class AuthMiddleware implements BaseMiddlewareInterface
{
	/**
	 * @return void
	 */
	public function handle(): void
	{
        Auth::check();
	}
}