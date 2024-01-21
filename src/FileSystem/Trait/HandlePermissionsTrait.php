<?php

namespace Solital\Core\FileSystem\Trait;

use Symfony\Component\Filesystem\Filesystem;

trait HandlePermissionsTrait
{
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
            (new Filesystem())->chmod($file, $mode);
            return true;
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
        if (is_file($path_file)) {
            (new Filesystem())->chown($path_file, $user_name);
            return true;
        }

        return false;
    }
}
