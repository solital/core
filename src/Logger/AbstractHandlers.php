<?php

namespace Solital\Core\Logger;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Level;
use Solital\Core\Kernel\Application;
use Solital\Core\Mail\Mailer;

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
        self::$log_config = Application::getYamlVariables(5, 'logger.yaml');
    }

    /**
     * @param string $handler_name
     * @param mixed $level
     * 
     * @return mixed
     */
    protected static function setHandler(string $handler_name, mixed $level, string $log_path): mixed
    {
        $path = Application::getRootApp("Storage/", Application::DEBUG);

        switch ($handler_name) {
            case 'stream':
                $handle = new StreamHandler($path . $log_path, $level);
                break;

            case 'syslog':
                $handle = new SyslogHandler($handler_name, level: $level);
                break;

            case 'mail':
                $handle = self::mailerHandler();
                break;
        }

        return $handle;
    }

    /**
     * @param string $level
     * 
     * @return mixed
     */
    protected static function setLevel(string $level): mixed
    {
        switch ($level) {
            case 'debug':
                $level = Level::Debug;
                break;

            case 'info':
                $level = Level::Info;
                break;

            case 'notice':
                $level = Level::Notice;
                break;

            case 'warning':
                $level = Level::Warning;
                break;

            case 'error':
                $level = Level::Error;
                break;

            case 'critical':
                $level = Level::Critical;
                break;

            case 'alert':
                $level = Level::Alert;
                break;

            case 'emergency':
                $level = Level::Emergency;
                break;
        }

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
