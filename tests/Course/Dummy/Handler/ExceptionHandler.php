<?php

class ExceptionHandler implements \Solital\Core\Exceptions\ExceptionHandlerInterface
{
	public function handleError(\Solital\Core\Http\Request $request, \Exception $error)  : void
	{
	    echo $error->getMessage();
	}

}