<?php

namespace Solital\Core\FileSystem;

use Solital\Core\FileSystem\Exception\HandleFilesException;
use Solital\Core\FileSystem\HandleFoldersTrait;

class HandleFiles
{
    use HandleFoldersTrait;

    /**
     * @var string
     */
    protected string $file;

    /**
     * @return array
     * 
     * @throw Exception
     */
    public function files(): array
    {
        if (is_dir($this->folder)) {
            $dir = dir($this->folder);

            while (($files = $dir->read()) !== false) {
                if (($files != '.') && ($files != '..')) {
                    $this->files[] = $this->folder . $files;
                }
            }

            $dir->close();

            return $this->files;
        } else {
            throw new HandleFilesException("Folder '" . $this->folder . "' not found", 404);
        }
    }

    /**
     * @param string $folder
     * @return self
     */
    public function folder(string $folder): self
    {
        $this->folder = $folder . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * @param string $word
     * 
     * @return null|array
     */
    public function file($word): ?string
    {
        $iterator = new \FileSystemIterator($this->folder);
        foreach ($iterator as $file) {

            $filename = $file->getRealpath();

            if (strpos($filename, $word) !== false) {
                return $this->files[] = $filename;
            }
        }

        return null;
    }

    /**
     * @param string $file
     * @param bool   $delete
     * @return null|bool
     */
    public function fileExists(string $file, bool $delete = false): bool
    {
        if (file_exists($this->folder . $file)) {
            if ($delete == true) {
                unlink($this->folder . $file);
                return true;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $file_name
     * @param string $dir_name
     * 
     * @return mixed
     */
    public function getAndPutContents(string $file_name, string $dir_name)
    {
        $file = file_get_contents($file_name);
        $res = file_put_contents($dir_name, $file);

        return $res;
    }

    /**
     * @param string $file
     * @param string $new_file
     * 
     * @return bool
     */
    public function copy(string $file, string $new_file, bool $delete_original = false): bool
    {
        if (!file_exists($new_file) && $delete_original == false) {
            if (!copy($file, $new_file)) {
                return false;
            }
        } elseif (file_exists($new_file) && $delete_original == true) {
            if (!copy($file, $new_file)) {
                return false;
            }

            unlink($file);

            return true;
        }

        return true;
    }

    /**
     * @param string $file_name
     * 
     * @return string|null
     */
    public function getPermission(string $file_name): ?string
    {
        if (!is_file($file_name)) {
            return null;
        }

        $this->file = $file_name;
        $perm = substr(sprintf('%o', fileperms($this->file)), -4);

        return $perm;
    }

    /**
     * @param int $mode
     * 
     * @return bool
     */
    public function setPermission(string $file, int $mode): bool
    {
        if (is_file($file)) {
            $res = chmod($file, $mode);

            return $res;
        }

        return false;
    }

    /**
     * @param string $path_file
     * @param string $user_name
     * 
     * @return bool
     */
    public function setOwner(string $path_file, string $user_name): bool
    {
        $res = chown($path_file, $user_name);

        return $res;
    }
}
