<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Formatter;

use Solital\Core\Logger\Entry\LogEntryInterface;

interface FormatterInterface
{
    /**
     * convert to formatted message
     *
     * @param  LogEntryInterface $entry
     * 
     * @return string
     */
    public function format(LogEntryInterface $entry): string;
}
