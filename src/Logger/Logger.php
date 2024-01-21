<?php

namespace Solital\Core\Logger;

use Monolog\Logger as Monolog;
use Solital\Core\Logger\Exception\LoggerException;

class Logger extends AbstractHandlers
{
    /**
     * @var array
     */
    private static array $channel;

    /**
     * @param string $channel
     * 
     * @return Monolog
     * @throws LoggerException
     */
    public static function channel(string $channel): Monolog
    {
        self::getLogConfig();

        if (array_key_exists($channel, self::$log_config['channel'])) {
            self::$channel = self::$log_config['channel'][$channel];

            return self::createLog($channel);
        }

        throw new LoggerException("Channel '" . $channel . "' not exists in 'logger.yaml'");
    }

    /**
     * @param string $channel
     * 
     * @return Monolog
     */
    private static function createLog(string $channel): Monolog
    {
        $monolog = new Monolog($channel);

        if (self::$log_config['enable_logs'] === true) {
            $level = self::setLevel(self::$channel['level']);
            $handle = self::setHandler(self::$channel['type'], $level, self::$channel['path']);

            if (isset(self::$channel['processor'])) {
                foreach (self::$channel['processor'] as $processor) {
                    $class = new \ReflectionClass("\Monolog\Processor\\$processor");
                    $instance = $class->newInstance();
                    $monolog->pushProcessor($instance);
                }
            }

            $monolog->pushHandler($handle);
        }

        return $monolog;
    }
}
