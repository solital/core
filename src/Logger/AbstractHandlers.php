<?php

namespace Solital\Core\Logger;

use Monolog\Level;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\{StreamHandler, SyslogHandler};
use Solital\Core\Mail\Mailer;
use Solital\Core\Kernel\{Application, DebugCore};

abstract class AbstractHandlers
{
    /**
     * @var array
     */
    protected static array $log_config;

    /**
     * Get variales in `logger.yaml` file
     *
     * @return void
     */
    protected static function getLogConfig(): void
    {
        self::$log_config = Application::yamlParse('logger.yaml');
    }

    /**
     * @param string $handler_name
     * @param mixed $level
     * 
     * @return mixed
     */
    protected static function setHandler(string $handler_name, mixed $level, string $log_path): mixed
    {
        $path = Application::getRootApp("Storage/", DebugCore::isCoreDebugEnabled());

        $handle = match ($handler_name) {
            'stream' => new StreamHandler($path . $log_path, $level),
            'syslog' => new SyslogHandler($handler_name, level: $level),
            'mail' => self::mailerHandler()
        };

        return $handle;
    }

    /**
     * @param string $level
     * 
     * @return mixed
     */
    protected static function setLevel(string $level): mixed
    {
        $level = match ($level) {
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'critical' => Level::Critical,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency,
            default => $level,
        };

        return $level;
    }

    /**
     * @return MailerHandler
     */
    private static function mailerHandler(): MailerHandler
    {
        $mailer = new Mailer();
        $mailer->add(self::$log_config['path'], 'Solital Mailer Log', self::$log_config['path'], 'Recipient name');
        $mailer->setSubject('Solital Mailer Log');
        $conn = $mailer->getConnection();

        $mailer = new MailerHandler($conn);
        $mailer->setFormatter(new HtmlFormatter());

        return $mailer;
    }
}
