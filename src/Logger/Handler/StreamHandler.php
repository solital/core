<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Handler;

use Symfony\Component\Yaml\Yaml;
use Solital\Core\Kernel\Application;
use Solital\Core\Logger\Entry\LogEntryInterface;
use Solital\Core\Logger\Handler\HandlerAbstract;
use Solital\Core\Logger\Formatter\FormatterInterface;

class StreamHandler extends HandlerAbstract
{
    /**
     * @var mixed
     */
    protected mixed $stream = null;

    /**
     * @param  string|resource    $stream
     * @param  FormatterInterface $formatter
     */
    public function __construct(mixed $stream, ?FormatterInterface $formatter = null)
    {
        $config = Yaml::parseFile(Application::getDirConfigFiles(5) . 'bootstrap.yaml');

        if ($config['logs']['enabled_log_files'] == true) {
            $this->stream = $this->openStream($stream);
        }

        parent::__construct($formatter);
    }

    /**
     * Open stream for writing
     *
     * @param  string|resource $path
     * 
     * @return resource
     * @throws \LogicException if open failure
     */
    protected function openStream(mixed $path): mixed
    {
        if (is_string($path)) {
            if (str_contains('://', $path)) {
                $path = 'file://' . $path;
            }

            return fopen($path, 'a');
        }

        if (is_resource($path)) {
            return $path;
        }

        throw new \LogicException("failed to open stream");
    }

    /**
     * close the stream
     */
    protected function close()
    {
        if ($this->stream) {
            fclose($this->stream);
            $this->stream = false;
        }
    }

    /**
     * @param LogEntryInterface $entry
     * 
     * @return void
     */
    protected function write(LogEntryInterface $entry): void
    {
        if ($this->stream) {
            $msg = $this->getFormatter()->format($entry);
            flock($this->stream, LOCK_EX);
            fwrite($this->stream, $msg);
            flock($this->stream, LOCK_UN);
        }
    }
}
