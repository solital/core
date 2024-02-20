<?php

namespace Solital\Core\Kernel\Traits;

use Solital\Core\Exceptions\RuntimeException;
use Solital\Core\Kernel\Application;

trait ClassLoaderTrait
{
    /**
     * Load class in directory
     * 
     * @param string $dir
     * 
     * @return array
     */
    public static function classLoaderInDirectory(string $dir): array
    {
        // store already declared classes:
        $predeclaredClasses = get_declared_classes();

        // Load classes inside the given folder:
        $i = new \FileSystemIterator($dir, \FileSystemIterator::SKIP_DOTS);

        foreach ($i as $f) {
            require_once $f->getPathname();
        }

        // Enjoy
        return array_diff(get_declared_classes(), $predeclaredClasses);
    }

    /**
     * Recursively loads all php files in all subdirectories of the given path
     *
     * @param string $directory
     *
     * @throws \Exception
     */
    public static function autoload(string $directory)
    {
        // Ensure this path exists
        if (!is_dir($directory)) {
            return;
        }

        // Get a listing of the current directory
        $scanned_dir = scandir($directory);

        // Ignore these items from scandir
        $ignore = [
            '.',
            '..'
        ];

        // Remove the ignored items
        $scanned_dir = array_diff($scanned_dir, $ignore);

        if (empty($scanned_dir)) {
            return;
        }

        if (count($scanned_dir) > 250) {
            throw new RuntimeException('Too many files attempted to load via autoload');
        }

        foreach ($scanned_dir as $item) {

            $filename  = $directory . '/' . $item;
            $real_path = realpath($filename);

            if (false === $real_path) {
                continue;
            }

            $filetype = filetype($real_path);

            if (empty($filetype)) {
                continue;
            }

            // If it's a directory then recursively load it
            if ('dir' === $filetype) {
                self::autoload($real_path);
            } // If it's a file, let's try to load it
            else if ('file' === $filetype) {

                if (true !== is_readable($real_path)) {
                    continue;
                }

                // Don't allow files that have been uploaded
                if (is_uploaded_file($real_path)) {
                    continue;
                }

                // Only for files that really exist
                if (true !== file_exists($real_path)) {
                    continue;
                }

                $pathinfo = pathinfo($real_path);

                // An empty filename wouldn't be a good idea
                if (empty($pathinfo['filename'])) {
                    continue;
                }

                // Sorry, need an extension
                if (empty($pathinfo['extension'])) {
                    continue;
                }

                // Actually, we want just a PHP extension!
                if ('php' !== $pathinfo['extension']) {
                    continue;
                }

                $filesize = filesize($real_path);

                // Don't include negative sized files
                if ($filesize < 0) {
                    throw new RuntimeException('File size is negative, not autoloading');
                }

                // Don't include files that are greater than 300kb
                if ($filesize > 300000) {
                    throw new RuntimeException('File size is greater than 300kb, not autoloading');
                }

                require_once($real_path);
            }
        }
    }

    /**
     * Return all user command in app/Console/Command/
     *
     * @return array
     */
    public static function getUserCommands(): array
    {
        $command_class = [];

        $cmd_dir = Application::getRootApp('Console/Command', false);
        $handle = Application::provider('handler-file');

        if (is_dir($cmd_dir)) {
            $files = $handle->folder($cmd_dir)->files();

            if (!is_null($files)) {
                foreach ($files as $file) {
                    $class[] = str_replace('.php', '', basename($file));
                }

                foreach ($class as $class) {
                    $command_class[] = "\Solital\Console\Command\\" . $class;
                }
            }
        }

        return $command_class;
    }
}
