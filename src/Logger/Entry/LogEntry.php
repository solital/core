<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Entry;

use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;
use Solital\Core\Logger\Processor\ProcessorAwareTrait;

/**
 * Log message prototype
 *
 * @package Solital\Core\Logger
 */
class LogEntry implements LogEntryInterface
{
    use LogLevelTrait;
    use ProcessorAwareTrait;

    /**
     * message template
     *
     * @var string
     */
    protected string $message = 'log message';

    /**
     * @var string
     */
    protected string $level = LogLevel::INFO;

    /**
     * @var array
     */
    protected array $context;

    /**
     * @param string $message
     * @param array  $context
     */
    public function __construct(string $message = '', array $context = [])
    {
        if (!empty($message)) {
            $this->message = $message;
        }

        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $level
     * 
     * @return LogEntry
     * @throws InvalidArgumentException
     */
    public function setLevel(string $level): LogEntry
    {
        if (!isset($this->convert[$level])) {
            throw new InvalidArgumentException("Unknown log level");
        }

        $this->level = $level;
        return $this;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param array $context
     * 
     * @return LogEntry
     */
    public function setContext(array $context): LogEntry
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $this->process();
        return $this->interpolate($this->getMessage(), $this->getContext());
    }

    /**
     * @param string $message  message
     * @param array  $context
     * 
     * @return string
     */
    protected function interpolate(string $message, array $context): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }
}
