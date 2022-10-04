<?php

declare(strict_types=1);

namespace Solital\Core\Logger;

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Solital\Core\Logger\Entry\LogEntry;
use Solital\Core\Logger\Entry\LogEntryInterface;
use Solital\Core\Logger\Handler\HandlerAwareTrait;
use Solital\Core\Logger\Handler\HandlerAwareInterface;

class Logger implements LoggerInterface, HandlerAwareInterface
{
    use LoggerTrait;
    use HandlerAwareTrait;

    /**
     * @var string
     */
    protected $channel;

    /**
     * Channel usually is APP ID
     *
     * @param  string $channel
     */
    public function __construct(string $channel)
    {
        $this->channel = $channel;
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $entry = ($this->initEntry($message, $level, $context))->process();

        foreach ($this->getHandlers($entry) as $handler) {
            $handler($entry);
        }
    }

    /**
     * @param  LogEntryInterface|string $message
     * @param  string                   $level
     * @param  array                    $context
     * @return LogEntryInterface
     * @throws \InvalidArgumentException if message not right
     */
    protected function initEntry($message, string $level, array $context): LogEntryInterface
    {
        $entry = $this->validate($message);

        // update channel name in context
        $this->setChannel($context);

        return $entry
            ->setLevel($level)
            ->setContext(array_merge($entry->getContext(), $context));
    }

    /**
     * @param mixed $message
     * 
     * @return LogEntryInterface
     * @throws \InvalidArgumentException if message not right
     */
    protected function validate(mixed $message): LogEntryInterface
    {
        if (is_string($message)) {
            $entry = new LogEntry($message);
        } elseif (is_object($message) && $message instanceof LogEntryInterface) {
            $entry = $message;
        } else {
            throw new \InvalidArgumentException("not valid message " . (string) $message);
        }
        return $entry;
    }

    /**
     * Set channel name in context
     *
     * @param  array &$context
     * 
     * @return void
     */
    protected function setChannel(array &$context): void
    {
        if (!isset($context['__channel'])) {
            $context['__channel'] = $this->channel;
        }
    }
}
