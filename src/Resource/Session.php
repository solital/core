<?php

namespace Solital\Core\Resource;

class Session 
{
    /**
     * @param string $index
     * @param mixed $value
     */
    public static function new(string $index, $value, string $key = null): object
    {
        if ($key != null) {
            $_SESSION[$index][$key] = (is_array($value) ? (object)$value : $value);
            return new static;
        }

        $_SESSION[$index] = (is_array($value) ? (object)$value : $value);
        return new static;
    }
    
    /**
     * @param string $index
     * @return bool
     */
    public static function delete(string $index, string $key = null): bool
    {
        if ($key != null) {
            if (isset($_SESSION[$index][$key])) {
                unset($_SESSION[$index][$key]);
                return true;
            }
        }

        if (isset($_SESSION[$index])) {
            unset($_SESSION[$index]);
            return true;
        }

        return false;
    }
    
    /**
     * @param string $index
     */
    public static function show(string $index, string $key = null)
    {
        if ($key != null) {
            if (isset($_SESSION[$index][$key])) {
                return $_SESSION[$index][$key];
            }
        }

        if (isset($_SESSION[$index])) {
            return $_SESSION[$index];
        }
    }

    /**
     * @param string $index
     * @return bool
     */
    public static function has(string $index, string $key = null): bool
    {
        if ($key != null) {
            if (isset($_SESSION[$index][$key])) {
                return true;
            } else {
                return false;
            }
        }

        if (isset($_SESSION[$index])) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @return bool
     */
    public static function destroy(): bool
    {
        session_destroy();
        return true;
    }
}
