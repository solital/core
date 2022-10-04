<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Handler;

use Psr\Log\LogLevel;
use Solital\Core\Logger\Entry\LogEntryInterface;
use Solital\Core\Logger\Formatter\FormatterInterface;

class SyslogHandler extends HandlerAbstract
{
    /**
     * syslog facility
     *
     * @var    int
     */
    protected int $facility;

    /**
     * syslog options
     *
     * @var    int
     */
    protected int $logopts;

    /**
     * syslog levels
     *
     * @var    array
     * @access protected
     */
    protected array $priorities = [
        LogLevel::DEBUG => \LOG_DEBUG,
        LogLevel::INFO => \LOG_INFO,
        LogLevel::NOTICE => \LOG_NOTICE,
        LogLevel::WARNING => \LOG_WARNING,
        LogLevel::ERROR => \LOG_ERR,
        LogLevel::CRITICAL => \LOG_CRIT,
        LogLevel::ALERT => \LOG_ALERT,
        LogLevel::EMERGENCY => \LOG_EMERG,
    ];

    /**
     * @param  int                $facility
     * @param  int                $logOpts
     * @param  null|FormatterInterface $formatter
     */
    public function __construct(
        int $facility = \LOG_USER,
        int $logOpts = \LOG_PID,
        ?FormatterInterface $formatter = null
    ) {
        $this->facility = $facility;
        $this->logopts = $logOpts;

        parent::__construct($formatter);
    }

    /**
     * @param LogEntryInterface $entry
     * 
     * @return void
     */
    protected function write(LogEntryInterface $entry): void
    {
        $context = $entry->getContext();
        $ident = $context['__channel'] ?? 'LOG';

        if (!openlog($ident, $this->logopts, $this->facility)) {
            throw new \LogicException("openlog() failed");
        }

        syslog(
            $this->priorities[$entry->getLevel()],
            $this->getFormatter()->format($entry)
        );

        closelog();
    }
}
