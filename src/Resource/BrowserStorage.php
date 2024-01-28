<?php

namespace Solital\Core\Resource;

use Solital\Core\Resource\Str\Str;

/** @phpstan-consistent-constructor */
class BrowserStorage
{
    /**
     * @var string
     */
    private static string $type;

    /**
     * @return static
     */
    public static function local()
    {
        self::$type = "local";
        return new static();
    }

    /**
     * @return static
     */
    public static function session()
    {
        self::$type = "session";
        return new static();
    }

    /**
     * @param string $key
     * @param mixed $value
     * 
     * @return void
     */
    public function setItem(string $key, mixed $value): void
    {
        $cookie = new Cookie($key);

        if (is_string($value)) {
            $cookie->setValue($value);
            $value = "'" . $value . "'";
        }

        if (is_array($value)) {
            $value = (new JSON)->encode($value);
            $cookie->setValue($value);
            $value = "JSON.stringify(" . $value . ")";
        }

        $cookie->setPath('/');
        $cookie->save();

        if (self::$type === "local") {
            echo "<script>localStorage.setItem('$key', $value);</script>";
        }

        if (self::$type == "session") {
            echo "<script>sessionStorage.setItem('$key', $value);</script>";
        }
    }

    /**
     * @param string $key
     * @param bool   $special_chars
     * 
     * @return mixed
     */
    public function getItem(string $key, bool $special_chars = false): mixed
    {
        if (self::$type == "local") {
            echo "<script>var value = localStorage.getItem('$key');</script>";
        }

        if (self::$type == "session") {
            echo "<script>var value = sessionStorage.getItem('$key');</script>";
        }

        echo "<script>
        if (!value) {
            alert('BrowserStorage: variable \"" . $key . "\" not found');
        }
        </script>";

        $value = Cookie::get($key);

        if (str_contains($value, '{')) {
            $value = (new JSON)->decode($value, true);
        }

        if ($special_chars == true) {
            $value = (new Str($value))->specialchars();
        }

        return $value;
    }

    /**
     * @param string $key
     * 
     * @return void
     */
    public function removeItem(string $key): void
    {
        $cookie = new Cookie($key);
        $cookie->delete();

        if (self::$type == "local") {
            echo "<script>localStorage.removeItem('$key');</script>";
        }

        if (self::$type == "session") {
            echo "<script>sessionStorage.removeItem('$key');</script>";
        }
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        self::clearCookies();

        if (self::$type == "local") {
            echo "<script>localStorage.clear();</script>";
        }

        if (self::$type == "session") {
            echo "<script>sessionStorage.clear();</script>";
        }
    }

    /**
     * @return void
     */
    public static function clearAll(): void
    {
        self::clearCookies();
        echo "<script>localStorage.clear();</script>";
        echo "<script>sessionStorage.clear();</script>";
    }

    /**
     * @return void
     */
    private static function clearCookies(): void
    {
        $past = time() - 3600;
        foreach ($_COOKIE as $key => $value) {
            setcookie($key, $value, $past, '/');
        }
    }
}
