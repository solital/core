<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Entry;

use Psr\Log\LogLevel;

trait LogLevelTrait
{
    /**
     * convert to numeric values
     *
     * @var array
     */
    protected array $convert = [
        LogLevel::DEBUG => 100,
        LogLevel::INFO => 200,
        LogLevel::NOTICE => 300,
        LogLevel::WARNING => 400,
        LogLevel::ERROR => 500,
        LogLevel::CRITICAL => 600,
        LogLevel::ALERT => 700,
        LogLevel::EMERGENCY => 800
    ];
}
