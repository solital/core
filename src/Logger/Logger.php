<?php

namespace Solital\Core\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger as Monolog;
use Monolog\Processor\ProcessorInterface;
use Solital\Core\Logger\Exception\LoggerException;

class Logger extends AbstractHandlers
{
    /**
     * @var array
     */
    private static array $channel;

    /**
     * The logging channel
     * 
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
     * Custom Handlers
     *
     * @param string $channel
     * @param array $handlers
     * @param array|null $processors
     * 
     * @return Monolog
     */
    public static function customHandler(string $channel, array $handlers, ?array $processors = null): Monolog
    {
        self::getLogConfig();

        $monolog = new Monolog($channel, $handlers);

        if ($processors != null) {
            foreach ($processors as $processor) {
                if ($processor instanceof ProcessorInterface || !is_callable($processor)) {
                    throw new LoggerException("Processor must be instace of 'ProcessorInterface' or callable");
                }

                $monolog->pushProcessor($processor);
            }
        }

        return $monolog;
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
            $handler = self::setHandler(self::$channel['type'], $level, self::$channel['path']);

            if (isset(self::$channel['processor'])) {
                foreach (self::$channel['processor'] as $processor) {
                    $class = new \ReflectionClass("\Monolog\Processor\\$processor");
                    $instance = $class->newInstance();
                    $monolog->pushProcessor($instance);
                }
            }

            $monolog->pushHandler($handler);
        }

        return $monolog;
    }
}
