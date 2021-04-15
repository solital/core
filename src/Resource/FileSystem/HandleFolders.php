<?php

namespace Solital\Core\Resource\FileSystem;

use FilesystemIterator;
use RecursiveIteratorIterator;

abstract class HandleFolders
{
    /**
     * @var string
     */
    protected string $folder;

    /**
     * @var array
     */
    protected array $files = [];

    /**
     * @param string $folder
     * @return HandleFiles
     */
    public function folder(string $folder): HandleFiles
    {
        $this->folder = $folder . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * @param string $dir
     * @param int $permission
     * @return bool
     */
    public function create(string $dir, int $permission = 0777): bool
    {
        if (!is_dir($dir)) {
            \mkdir($dir, $permission, true);
            \chmod($dir, $permission);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $dir
     * @param bool $safe
     * @return bool
     */
    public function remove(string $dir, bool $safe = true): bool
    {
        $this->files = (new HandleFiles())->folder($dir)->files();

        if (is_dir($dir)) {
            if ($safe == false) {
                $this->removeFiles($dir);
                return true;
            }

            if (is_array($this->files)) {
                return false;
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                exec(sprintf("rd /s /q %s", escapeshellarg($dir)));
            } else {
                rmdir($dir);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $dir
     * @return bool
     */
    private function removeFiles(string $dir): bool
    {
        $di = new \RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($ri as $file) {
            $file->isDir() ?  \rmdir($file) : \unlink($file);
        }

        \rmdir($dir);

        return true;
    }
}
