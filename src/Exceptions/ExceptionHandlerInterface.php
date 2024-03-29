<?php

namespace Solital\Core\Exceptions;

use Solital\Core\Http\Request;

interface ExceptionHandlerInterface
{
    /**
     * @param Request $request
     * @param \Exception $error
     */
    public function handleError(Request $request, \Exception $error): void;
}