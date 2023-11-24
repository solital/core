<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Handler;

use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Kernel\Application;
use Solital\Core\Logger\Entry\LogEntryInterface;
use Solital\Core\Logger\Formatter\DefaultFormatter;
use Solital\Core\Logger\Formatter\FormatterInterface;

class EmailHandler extends HandlerAbstract
{
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
    protected function write(LogEntryInterface $entry)
    {
    }
}
