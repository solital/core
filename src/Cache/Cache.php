<?php

namespace Solital\Core\Cache;

use Psr\SimpleCache\CacheInterface;
use Solital\Core\Kernel\Application;
use Solital\Core\Cache\Exception\InvalidArgumentException;

/**
 * @deprecated Use \Solital\Core\Cache\SimpleCache
 */
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
    public function __construct()
    {
        $this->value = Application::getRootApp("Storage/cache/");
    }

    /**
     * @param string $key A value of key
     * @return mixed
     * 
     * @throws InvalidArgumentException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException("$key must be equal to string");
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

        return null;
    }

    /**
     * @param string           $key A value of key
     * @param mixed            $value The value that will be created the cache
     * @param int|DateInterval $ttl The TTL value of this item
     * 
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        if ($ttl == null) {
            $ttl = 20;
        }

        if (!is_string($key)) {
            throw new InvalidArgumentException("$key must be equal to string");
        }

        /* if (!$ttl instanceof \DateInterval || !is_int($ttl)) {
            throw new InvalidArgumentException("$ttl is not a valid value. Enter a value equal to int or DateInterval");
        } */

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
     * 
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException("$key must be equal to string");
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

        return false;
    }

    /**
     * @param string $key The cache item key.
     * 
     * @return bool
     */
    public function has(string $key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException("$key must be equal to string");
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
     * @return iterable
     * @throws InvalidArgumentException
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        if (!is_iterable($keys)) {
            throw new InvalidArgumentException("$keys must be equal to array or a Traversable");
        }

        foreach ($keys as $key => $value) {
            $file_in_cache = $this->value . $value . ".cache.php";

            if (file_exists($file_in_cache)) {
                $file_cache = file_get_contents($file_in_cache);
                $decoded = json_decode($file_cache, true);

                if ($decoded['expire_at'] < time() || $decoded['expire_at'] == null) {
                    $this->delete($value);
                } else {
                    $this->multiple_results[] = $decoded;
                }
            }
        }

        return $this->multiple_results;
    }

    /**
     * @param iterable $values The value that will be created the cache with the keys
     * @param null|int|\DateInterval $ttl     The TTL value of this item
     * 
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        if ($ttl == null) {
            $ttl = 20;
        }

        if (!is_iterable($values)) {
            throw new InvalidArgumentException("$values must be equal to array or a Traversable");
        }

        if (!$ttl instanceof \DateInterval && !is_int($ttl)) {
            throw new InvalidArgumentException("$ttl is not a valid value. Enter a value equal to int or DateInterval");
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
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        if (!is_iterable($keys)) {
            throw new InvalidArgumentException("$keys must be equal to array or a Traversable");
        }

        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                $this->verifyMultipleKeys($value);
            }

            return true;
        }

        $this->verifyMultipleKeys($keys);

        return true;
    }

    /**
     * @param array|string $keys checks multiple keys
     * 
     * @return Cache
     */
    private function verifyMultipleKeys($keys): Cache
    {
        $file_in_cache = $this->value . $keys . ".cache.php";

        if (file_exists($file_in_cache)) {
            unlink($file_in_cache);
        }

        return $this;
    }
}
