<?php

namespace Solital\Core\Resource\FileSystem;

use Solital\Core\Exceptions\NotFoundException;
use Solital\Core\Resource\FileSystem\HandleFolders;

class HandleFiles extends HandleFolders
{
    /**
     * @var string
     */
    protected string $file;

    /**
     * @return array
     * 
     * @throw NotFoundException
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
            NotFoundException::notFound(404, "Folder '" . $this->folder . "'", "", "HandleFiles");
        }
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
        if (!copy($file, $new_file)) {
            return false;
        } elseif ($delete_original == true) {
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

    /**
     * @param string $file_name
     * 
     * @return string
     */
    public function getFullPermission(string $file_name): string
    {
        $this->file = $file_name;

        $perms = fileperms($this->file);

        if (($perms & 0xC000) == 0xC000) {
            // Socket
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            // Link simbólico
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            // Regular
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            // Bloco especial
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            // Diretório
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            // Caractere especial
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            // FIFO pipe
            $info = 'p';
        } else {
            // Desconhecido
            $info = 'u';
        }

        // Proprietário
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

        // Grupo
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

        // Outros
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }
}
