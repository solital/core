<?php

namespace Solital\Core\Console\Output;

class OutputException extends \Exception
{
    /**
     * @var string
     */
    private ?string $value = null;

    /**
     * @param string $message
     * @param string $value
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, ?string $value = null, int $code = 0, \Throwable $previous = null)
    {
        $this->value = $value;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $value = ($this->value != null) ? " [Used: " . $this->value . "]" : '';
        return __CLASS__ . ": " . $this->message . $value . "\n";
    }
}
