<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Entry;

use Psr\Log\InvalidArgumentException;
use Solital\Core\Logger\Processor\ProcessorAwareInterface;

/**
 * Log message
 *
 * @package Solital\Core\Logger
 */
interface LogEntryInterface extends ProcessorAwareInterface
{
    /**
     * Get the text message template (raw message)
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param  string $level
     * 
     * @return LogEntry
     * @throws InvalidArgumentException
     */
    public function setLevel(string $level);

    /**
     * Get the level
     *
     * @return string
     */
    public function getLevel(): string;

    /**
     * Set context
     *
     * @param  array $context
     * @return LogEntry
     */
    public function setContext(array $context);

    /**
     * Get the context
     *
     * @return array
     */
    public function getContext(): array;

    /**
     * @return string
     */
    public function __toString(): string;
}
