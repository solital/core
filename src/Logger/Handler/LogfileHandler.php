<?php

declare(strict_types=1);

namespace Solital\Core\Logger\Handler;

use Solital\Core\FileSystem\HandleFiles;
use Solital\Core\Kernel\Application;
use Solital\Core\Logger\Formatter\FormatterInterface;

class LogfileHandler extends StreamHandler
{
    /**
     * file rotation type
     *
     * @const int
     */
    const ROTATE_NONE = 0; // do not rotate
    const ROTATE_DATE = 1; // rotate by date

    /**
     * Constructor
     *
     * @param  string             $log_file
     * @param  null|FormatterInterface $formatter
     * @param  null|int                $rotate  rotate type
     * @throws \LogicException if path not writable
     */
    public function __construct(
        string $log_file,
        ?FormatterInterface $formatter = NULL,
        int $rotate = self::ROTATE_NONE
    ) {
        $file = date('Y-m-d-His') . "-" . $log_file . ".txt";
        $path = Application::getRootApp("Storage/log/", Application::DEBUG) . $file;

        $this->checkPath($path);

        if (file_exists($path)) {
            $this->doRotation($path, $rotate);
        }

        parent::__construct($path, $formatter);
    }

    /**
     * Check file path
     *
     * @param  string $path
     * 
     * @throws \LogicException if directory failure etc.
     */
    protected function checkPath(string $path)
    {
        try {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                (new HandleFiles)->create($dir);
            }
            if (!is_dir($dir) || !is_writable($dir)) {
                throw new \Exception("unable to write to $path");
            }
        } catch (\Throwable $e) {
            throw new \LogicException($e->getMessage());
        }
    }

    /**
     * Rotate file on start
     *
     * @param  string $path
     * @param  int    $type
     * 
     * @return bool
     */
    protected function doRotation(string $path, int $type): bool
    {
        switch ($type) {
                // by date
            case self::ROTATE_DATE:
                return $this->rotateByDate($path);
                // no rotation
            default:
                return true;
        }
    }

    /**
     * Rotate $path to $path_20160616
     *
     * @param  string $path
     * @param  string $format  date format
     * 
     * @return bool
     */
    protected function rotateByDate(string $path, string $format = 'Ymd'): bool
    {
        $time = filemtime($path);

        if ($time < strtotime('today')) {
            return rename($path, $path . '_' . date($format, $time));
        } else {
            return false;
        }
    }
}
