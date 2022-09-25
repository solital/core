<?php

/**
 * Phoole (PHP7.2+)
 *
 * @category  Library
 * @package   Solital\Core\Logger
 * @copyright Copyright (c) 2019 Hong Zhang
 */

declare(strict_types=1);

namespace Solital\Core\Logger\Formatter;

use Solital\Core\Logger\Entry\LogEntryInterface;

/**
 * DefaultFormatter
 *
 * @package Solital\Core\Logger
 */
class DefaultFormatter implements FormatterInterface
{
    /**
     * @param LogEntryInterface $entry
     * 
     * @return string
     */
    public function format(LogEntryInterface $entry): string
    {
        $mesg = '';

        // channel name
        $context = $entry->getContext();
        if (isset($context['__channel'])) {
            $mesg .= '[' . strtolower($context['__channel']). '] ';
        }

        return date('Y-m-d H:i:s'). ' ' . $mesg . '[' . $entry->getLevel() . ']' .  ': ' . $entry . $this->getEol();
    }

    /**
     * Get EOL char base on the platform WIN or UNIX
     *
     * @return string
     */
    protected function getEol(): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return "\r\n";
        } else {
            return "\n";
        }
    }
}
