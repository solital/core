<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Handler;

use Solital\Core\Logger\Formatter\AnsiFormatter;
use Solital\Core\Logger\Entry\LogEntryInterface;
use Solital\Core\Logger\Formatter\FormatterInterface;

/**
 * log to a terminal
 *
 * @package Solital\Core\Logger
 */
class TerminalHandler extends StreamHandler
{
    /**
     * @param  string|resource    $stream
     * @param  null|FormatterInterface $formatter
     */
    public function __construct(
        $stream = 'php://stderr',
        ?FormatterInterface $formatter = null
    ) {
        if (!in_array($stream, ['php://stderr', 'php://stdout'])) {
            throw new \LogicException("unknown stream");
        }

        parent::__construct($stream, $formatter ?? new AnsiFormatter());
    }

    /**
     * @param LogEntryInterface $entry
     * 
     * @return bool
     */
    protected function isHandling(LogEntryInterface $entry): bool
    {
        return 'cli' === php_sapi_name();
    }
}
