<?php

namespace Solital\Core\FileSystem;

use Solital\Core\FileSystem\Trait\HandlePermissionsTrait;
use Solital\Core\FileSystem\Exception\HandleFilesException;
use Solital\Core\Resource\Str\Str;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class HandleFiles
{
    use HandlePermissionsTrait;

    /**
     * @var string
     */
    private string $folder;

    /**
     * @var string
     */
    private string $file;

    /**
     * @var array
     */
    private array $files = [];

    /**
     * @var Finder
     */
    private Finder $finder;

    /**
     * @var Filesystem
     */
    private Filesystem $file_system;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->finder = new Finder();
        $this->file_system = new Filesystem();
    }

    /**
     * Define the folder containing the files
     * 
     * @param string $folder
     * @return self
     */
    public function folder(string $folder): self
    {
        $this->folder = $folder;

        if (Str::contains($folder, "/")) {
            $this->folder = str_replace("/", DIRECTORY_SEPARATOR, $folder);
        }

        $this->finder->files()->in($this->folder);

        return $this;
    }

    /**
     * @return null|array
     */
    public function files(): ?array
    {
        if ($this->finder->hasResults()) {
            foreach ($this->finder as $file) {
                $this->files[] = $file->getRealPath();
            }

            return $this->files;
        }

        return null;
    }

    /**
     * Check if there is a file inside the folder
     * 
     * @param string $file_exists
     * @param bool   $delete
     * 
     * @return bool
     */
    public function fileExists(string $file_exists, bool $delete = false): bool
    {
        $full_file = $this->folder . DIRECTORY_SEPARATOR . $file_exists;
        $exists = $this->file_system->exists($full_file);

        if ($exists == true && $delete === true) {
            $this->file_system->remove($full_file);
            return true;
        }

        return $exists;
    }

    /**
     * Create a folder
     * 
     * @param string $dir
     * @param int $permission
     * 
     * @return bool
     * @throws IOExceptionInterface
     */
    public function create(string $dir, int $permission = 0777): bool
    {
        try {
            $this->file_system->mkdir($dir, $permission);
            return true;
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at " . $exception->getPath();
        }

        return false;
    }

    /**
     * Delete a folder
     * 
     * @param string $dir
     * @param bool $safe
     * 
     * @return bool
     * @throws HandleFilesException|IOExceptionInterface
     */
    public function remove(string $dir, bool $safe = true): bool
    {
        if ($this->file_system->exists($dir) == true) {

            $this->finder->files()->in($dir);

            if ($this->finder->hasResults()) {
                if ($safe === true) {
                    throw new HandleFilesException("Safe mode enabled: " . $dir . " directory has files");
                }
            }

            try {
                $this->file_system->remove($dir);
                return true;
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while removing your directory at " . $exception->getPath();
            }
        }

        throw new HandleFilesException($dir . " folder not exists");
    }

    /**
     * Use the native PHP functions `file_get_contents` and `file_put_contents` at the same time
     * @param string $file_name
     * @param string $dir_name
     * 
     * @return int|false
     */
    public function getAndPutContents(string $file_name, string $dir_name): int|false
    {
        $file = file_get_contents($file_name);
        $res = file_put_contents($dir_name, $file);

        return $res;
    }

    /**
     * Make a copy of a file
     * 
     * @param string $file
     * @param string $new_file
     * 
     * @return bool
     */
    public function copy(string $file, string $new_file, bool $delete_original = false): bool
    {
        if (Str::contains($file, "/") && Str::contains($new_file, "/")) {
            $file = str_replace("/", DIRECTORY_SEPARATOR, $file);
            $new_file = str_replace("/", DIRECTORY_SEPARATOR, $new_file);
        }

        if ($this->file_system->exists($file) == true) {
            $this->file_system->copy($file, $new_file);

            if ($delete_original === true) {
                $this->file_system->remove($file);
            }

            return true;
        }

        return false;
    }
}
