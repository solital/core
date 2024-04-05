<?php

namespace Solital\Core\Kernel\Exceptions;

class DotenvException extends \RuntimeException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getMessageException(): string
    {
        return __CLASS__ . ": " . $this->message . "\n";
    }
}
