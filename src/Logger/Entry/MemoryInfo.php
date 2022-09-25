<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Entry;

use Solital\Core\Logger\Processor\MemoryProcessor;

/**
 * A log entry with predefined message template to log memory usage
 *
 * ```php
 * // initiate log with app id
 * $log = new Logger('MyApp');
 *
 * // add handler to this MemoryInfo
 * $log->addHandler(
 *     LogLevel::INFO,
 *     new LogfileHandler('system.log'),
 *     MemoryInfo::class
 * );
 *
 * // use it
 * $log->info(new MemoryInfo());
 * ```
 *
 * @package Solital\Core\Logger
 */
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
