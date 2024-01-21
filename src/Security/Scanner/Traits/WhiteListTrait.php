<?php

namespace Solital\Core\Security\Scanner\Traits;

trait WhiteListTrait
{
    /**
     *
     * @var array
     */
    protected array $whitelist = [];
    protected array $customWhitelist = [];
    protected array $combined_whitelist = [];
    
    /**
     * @var int
     */
    protected int $combined_whitelist_count = 0;

    /**
     * @param array $a
     * 
     * @return void
     */
    public function setCustomWhitelist(array $a): void
    {
        $this->customWhitelist = $a;
    }

    /**
     * Check if the md5 checksum exists in the whitelist and returns true if it does.
     *
     * @param mixed $hash
     * 
     * @return bool 
     */
    protected function inWhitelist(mixed $hash): bool
    {
        if ($this->flagCombinedWhitelist) {
            if ($this->binarySearch($hash, $this->combined_whitelist, $this->combined_whitelist_count) > -1) {
                return true;
            }
        }

        return in_array($hash, $this->whitelist);
    }

    /**
     * Loads the whitelist files
     * 
     * @return void
     */
    public function loadWhitelists(): void
    {
        $a = array_merge(
            [dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'definitions' . DIRECTORY_SEPARATOR . 'whitelist.txt'
        ], $this->customWhitelist);

        foreach ($a as $file) {
            if (is_file($file)) {
                $fp = fopen($file, 'r');

                while (!feof($fp)) {
                    $line = fgets($fp);
                    $this->whitelist[] = substr($line, 0, 32);
                }

                fclose($fp);
            }
        }
    }

    /**
     * @param string $url
     * 
     * @return bool
     */
    protected function updateCombinedWhitelist(string $url = 'https://scr34m.github.io/php-malware-scanner'): bool
    {
        $latest_hash = trim(file_get_contents($url . '/database/compressed.sha256'));

        if ($latest_hash === false) {
            $this->error('Unable to download database checksum');
            return false;
        }

        $file = dirname(__DIR__, 2) . '/whitelist.dat';

        if (is_readable($file)) {
            $hash = hash_file('sha256', $file);

            if ($hash != $latest_hash) {
                $download = true;
            } else {
                $download = false;
            }
        } else {
            $download = true;
        }

        if ($download) {
            $data = file_get_contents($url . '/database/compressed.dat');

            if ($data === false) {
                $this->error('Unable to download database');
                return false;
            }

            file_put_contents($file, $data);
            $hash = hash_file('sha256', $file);
            if ($hash != $latest_hash) {
                $this->error('Downloaded database hash mismatch');
            }
        }

        $content = gzdecode(file_get_contents($file));
        $this->combined_whitelist = array();
        $this->combined_whitelist_count = 0;

        foreach (explode("\n", $content) as $line) { // faster than strtok, but needs more memory
            if ($line) {
                $this->combined_whitelist[] = $line;
                $this->combined_whitelist_count++;
            }
        }

        $this->combined_whitelist_count -= 1; // -1 because we use indexes in binary search
        echo 'Combined whitelist records count: ' . ($this->combined_whitelist_count + 1) . PHP_EOL;

        return true;
    }
}
