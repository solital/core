<?php

namespace Solital\Core\Cache;

use Psr\SimpleCache\CacheInterface;
use Solital\Core\Exceptions\InvalidArgumentException;

class Cache implements CacheInterface
{
    /**
     * @var string
     */
    private string $value;

    /**
     * @var array
     */
    private array $multiple_results = [];

    /**
     * Return a directory of the cache
     */
    public function __construct($test = false)
    {
        if ($test == true) {
            $this->value = "tests" . DIRECTORY_SEPARATOR . "files_test" . DIRECTORY_SEPARATOR;
        } else {
            $this->value = SITE_ROOT . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "Storage" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * @param string $key A value of key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException("The parameter informed must be equal to string", 500);
        }

        $file_in_cache = $this->value . $key . ".cache.php";

        if (file_exists($file_in_cache)) {
            $file_cache = file_get_contents($file_in_cache);
            $decoded = json_decode($file_cache, true);

            if ($decoded['expire_at'] < time() || $decoded['expire_at'] == null) {
                $this->delete($key);
            } else {
                return $decoded;
            }
        }
    }

    /**
     * @param string           $key A value of key
     * @param mixed            $value The value that will be created the cache
     * @param int|DateInterval $ttl The TTL value of this item
     */
    public function set($key, $value, $ttl = null): bool
    {
        if ($ttl == null) {
            $ttl = 20;
        }

        if (!is_string($key)) {
            throw new InvalidArgumentException("The parameter $key informed must be equal to string", 500);
        }

        if (!$ttl instanceof \DateInterval && !is_int($ttl)) {
            throw new InvalidArgumentException("$ttl is not a valid value. Enter a value equal to int or DateInterval", 500);
        }

        $file_for_cache = $this->value . $key . ".cache.php";

        $expire = null;

        if ($ttl instanceof \DateInterval) {
            $expire = (new \DateTime('now'))->add($ttl)->getTimeStamp();
        } else if (is_int($ttl)) {
            $expire = time() + $ttl;
        }

        $value['expire_at'] = $expire;

        if (isset($value['expire_at'])) {
            $page = json_encode($value);

            $handle = fopen($file_for_cache, 'w');
            fwrite($handle, $page);
            fclose($handle);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $key The unique cache key of the item to delete.
     */
    public function delete($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException("The parameter informed must be equal to string", 500);
        }

        $file_for_cache = $this->value . $key . ".cache.php";

        if (file_exists($file_for_cache)) {
            unlink($file_for_cache);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Wipes clean the entire cache's keys.
     * 
     * @return bool
     */
    public function clear(): bool
    {
        if (is_dir($this->value)) {
            $directory = dir($this->value);

            while ($file = $directory->read()) {
                if (($file != '.') && ($file != '..')) {
                    unlink($this->value . $file);
                }
            }

            $directory->close();

            return true;
        }
    }

    /**
     * @param string $key The cache item key.
     * 
     * @return bool
     */
    public function has($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException("The parameter informed must be equal to string", 500);
        }

        $file_in_cache = $this->value . $key . ".cache.php";

        if (file_exists($file_in_cache)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $keys A list of keys that can obtained in a single operation.
     * @param null $default
     * 
     * @return iterable|null
     */
    public function getMultiple($keys, $default = null): ?iterable
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException("The parameter informed must be equal to array", 500);
        }

        foreach ($keys as $key => $value) {
            $file_in_cache = $this->value . $value . ".cache.php";

            if (file_exists($file_in_cache)) {
                $file_cache = file_get_contents($file_in_cache);
                $decoded = json_decode($file_cache, true);

                if ($decoded['expire_at'] < time() || $decoded['expire_at'] == null) {
                    $this->deleteMultiple($value);
                } else {
                    $this->multiple_results[] = $decoded;
                }
            }
        }

        return $this->multiple_results;
    }

    /**
     * @param array $values The value that will be created the cache with the keys
     * @param mixed $ttl     The TTL value of this item
     * 
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool
    {
        if ($ttl == null) {
            $ttl = 20;
        }

        if (!is_array($values)) {
            throw new InvalidArgumentException("The parameter informed must be equal to array", 500);
        }

        if (!$ttl instanceof \DateInterval && !is_int($ttl)) {
            throw new InvalidArgumentException("$ttl is not a valid value. Enter a value equal to int or DateInterval", 500);
        }

        foreach ($values as $key => $value) {
            $file_for_cache = $this->value . $key . ".cache.php";

            $expire = null;

            if ($ttl instanceof \DateInterval) {
                $expire = (new \DateTime('now'))->add($ttl)->getTimeStamp();
            } else if (is_int($ttl)) {
                $expire = time() + $ttl;
            }

            $value['expire_at'] = $expire;

            if (empty($value['expire_at'])) {
                return false;
            }

            $page = json_encode($value);
            $handle = fopen($file_for_cache, 'w');
            fwrite($handle, $page);
            fclose($handle);
        }

        return true;
    }

    /**
     * @param iterable $keys A list of string-based keys to be deleted.
     * 
     * @return Cache
     */
    public function deleteMultiple($keys)
    {
        if (!is_array($keys) && !is_string($keys)) {
            throw new InvalidArgumentException("The parameter informed must be equal to array", 500);
        }

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $this->verifyMultipleKeys($value);
            }

            return true;
        }

        $this->verifyMultipleKeys($keys);
    }

    /**
     * @param array|string $keys checks multiple keys
     * 
     * @return Cache
     */
    private function verifyMultipleKeys($keys)
    {
        $file_in_cache = $this->value . $keys . ".cache.php";

        if (file_exists($file_in_cache)) {
            unlink($file_in_cache);
        }
    }
}
