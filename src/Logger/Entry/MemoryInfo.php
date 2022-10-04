<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Entry;

use Solital\Core\Logger\Processor\MemoryProcessor;

class MemoryInfo extends LogEntry
{
    /**
     * default message template
     *
     * @var string
     */
    protected string $message = '{memory_used}M memory used, peak usage is {memory_peak}M';

    /**
     * @return array
     */
    protected static function classProcessors(): array
    {
        return [
            new MemoryProcessor()
        ];
    }
}
