<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Handler;

use Solital\Core\Logger\Entry\LogEntryInterface;
use Solital\Core\Logger\Formatter\DefaultFormatter;
use Solital\Core\Logger\Formatter\FormatterInterface;
use Solital\Core\Logger\Formatter\FormatterAwareTrait;
use Solital\Core\Logger\Formatter\FormatterAwareInterface;

/**
 * HandlerAbstract
 *
 * @package Solital\Core\Logger
 */
abstract class HandlerAbstract implements FormatterAwareInterface
{
    use FormatterAwareTrait;

    /**
     * @param null|FormatterInterface $formatter
     */
    public function __construct(?FormatterInterface $formatter = null)
    {
        $this->setFormatter($formatter ?? new DefaultFormatter());
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param LogEntryInterface $entry
     */
    public function __invoke(LogEntryInterface $entry)
    {
        if ($this->isHandling($entry)) {
            $this->write($entry);
        }
    }

    /**
     * Close the handler if wanted
     */
    protected function close()
    {
    }

    /**
     * Is this handler handling this log ?
     *
     * @param LogEntryInterface $entry
     * @return bool
     */
    protected function isHandling(LogEntryInterface $entry): bool
    {
        return $entry ? true : true;
    }

    /**
     * Write to handler's device
     *
     * @param LogEntryInterface $entry
     */
    abstract protected function write(LogEntryInterface $entry);
}
