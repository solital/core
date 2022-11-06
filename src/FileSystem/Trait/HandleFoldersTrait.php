<?php

namespace Solital\Core\FileSystem;

trait HandleFoldersTrait
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
        $handle_files_obj = new HandleFiles();
        $handle_files_obj2 = clone $handle_files_obj;

        $this->files = $handle_files_obj2->folder($dir)->files();

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
     * @param string $file_name
     * 
     * @return string
     */
    public function getFullPermission(string $file_name): string
    {
        $perms = fileperms($file_name);

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

    /**
     * @param string $dir
     * @return bool
     */
    private function removeFiles(string $dir): bool
    {
        $di = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($ri as $file) {
            $file->isDir() ?  \rmdir($file) : \unlink($file);
        }

        \rmdir($dir);

        return true;
    }
}
