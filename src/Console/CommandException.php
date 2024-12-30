<?php

namespace Solital\Core\Console;

use Throwable;

class CommandException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString(): string
    {
        return self::class . ": {$this->message}\n";
    }
}
