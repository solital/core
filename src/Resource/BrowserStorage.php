<?php

namespace Solital\Core\Resource;

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
        if (is_string($value)) {
            $value = "'" . $value . "'";
        }

        if (self::$type === "local") {
            echo "<script>localStorage.setItem('$key', $value);</script>";
        } elseif (self::$type == "session") {
            echo "<script>sessionStorage.setItem('$key', $value);</script>";
        }
    }

    /**
     * @param string $key
     * 
     * @return string|null
     */
    public function getItem(string $key): ?string
    {
        if (self::$type == "local") {
            echo "<script>
            var value = localStorage.getItem('$key');
            document.cookie = '$key=' + value + '; expires=Fri, " . date('d M Y') . " 23:59:59 GMT;'</script>";
        } elseif (self::$type == "session") {
            echo "<script>
            var value = sessionStorage.getItem('$key');
            document.cookie = '$key=' + value + '; expires=Fri, " . date('d M Y') . " 23:59:59 GMT;'</script>";
        }

        $res = $_COOKIE[$key];

        return htmlspecialchars($res);
    }

    /**
     * @param string $key
     * 
     * @return void
     */
    public function removeItem(string $key): void
    {
        if (self::$type == "local") {
            echo "<script>localStorage.removeItem('$key');
            document.cookie = '$key=;expires=Thu, 01 Jan 1970 00:00:00 GMT'</script>";
        } elseif (self::$type == "session") {
            echo "<script>sessionStorage.removeItem('$key');
            document.cookie = '$key=;expires=Thu, 01 Jan 1970 00:00:00 GMT'</script>";
        }
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        if (self::$type == "local") {
            echo "<script>localStorage.clear();</script>";
        } elseif (self::$type == "session") {
            echo "<script>sessionStorage.clear();</script>";
        }
    }

    /**
     * @return void
     */
    public static function clearAll(): void
    {
        echo "<script>localStorage.clear();</script>";
        echo "<script>sessionStorage.clear();</script>";
    }
}
