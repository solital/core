<?php

class ExceptionHandlerFirst implements \Solital\Core\Exceptions\ExceptionHandlerInterface
{
	public function handleError(\Solital\Core\Http\Request $request, \Exception $error) : void
	{
	    global $stack;
	    $stack[] = static::class;

		$request->setUrl(new \Solital\Core\Http\Uri('/'));
	}

}