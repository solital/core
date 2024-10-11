<?php

namespace Solital\Core\Session;

use SessionHandler;
use SessionHandlerInterface;
use Solital\Core\Session\Enum\{SessionCacheLimiterEnum, SessionSaveHandlerEnum};
use Solital\Core\Session\Exception\{SessionExtensionNotFoundException, SessionConfigException};

final class SessionConfiguration
{
    /**
     * @var array
     */
    private static array $session_config = [];

    /**
     * @var string
     */
    private static string $handler_module = "";

    /**
     * @var string
     */
    private static string $session_filename;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Unset all of the session variables.
            session_unset();
            $_SESSION = [];

            // If it's desired to kill the session, also delete the session cookie.
            // Note: This will destroy the session, and not just the session data!
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            session_destroy();
        }
    }

    /**
     * Initialize session data
     *
     * @return void
     */
    public function start(): void
    {
        if (self::isSessionStarted() === false) session_start(self::$session_config);
    }

    /**
     * Get session filename
     *
     * @return string|null
     */
    public static function getSessionFilename(): ?string
    {
        self::$session_filename = session_save_path() . DIRECTORY_SEPARATOR . 'sess_' . session_id();
        clearstatcache(true, self::$session_filename);
        if (!file_exists(self::$session_filename)) return null;
        return self::$session_filename;
    }

    /**
     * Get session contents
     *
     * @param bool $encrypt
     * 
     * @return string|null
     */
    public static function getSessionContents(bool $encrypt = false): ?string
    {
        session_write_close();
        $filename = self::getSessionFilename();
        if (is_null($filename)) return null;
        $filename = file_get_contents($filename);

        if (self::isSessionStarted() === false && self::isCli() == false)
            session_start(self::$session_config);

        return ($encrypt === true) ? base64_encode($filename) : $filename;
    }

    /**
     * Set current cache expire
     *
     * @param int $value
     * 
     * @return self
     */
    public function setCacheExpire(int $value): self
    {
        session_cache_expire($value);
        return $this;
    }

    /**
     * Set the current cache limiter
     *
     * @param SessionCacheLimiterEnum $value
     * 
     * @return self
     */
    public function setCacheLimiter(SessionCacheLimiterEnum $value): self
    {
        session_cache_limiter(strtolower($value->name));
        return $this;
    }

    /**
     * Set the current session name
     *
     * @param string $name
     * 
     * @return self
     */
    public function setName(string $name): self
    {
        session_name(strtoupper($name));
        return $this;
    }

    /**
     * Sets user-level session storage
     *
     * @param SessionSaveHandlerEnum|object $handler
     * 
     * @return self
     * @throws SessionConfigException
     */
    public function setSaveHandler(mixed $handler): self
    {
        if ($handler instanceof SessionSaveHandlerEnum) {
            $this->extensionEnumIsEnabled($handler->name);
            self::$handler_module = $handler->name;
            session_module_name(strtolower($handler->name));
        } elseif ($handler instanceof SessionHandlerInterface || $handler instanceof SessionHandler) {
            self::$handler_module = get_class($handler);
            session_set_save_handler($handler, true);
        } else {
            throw new SessionConfigException(
                "Session handler must be instance of `SessionSaveHandlerEnum`, `SessionHandlerInterface` or `SessionHandler`"
            );
        }

        return $this;
    }

    /**
     * Get user-level session storage
     *
     * @return string
     */
    public static function getSaveHandler(): string
    {
        if (self::$handler_module == "") self::$handler_module = session_module_name();
        return self::$handler_module;
    }

    /**
     * Set the current session save path
     *
     * @param string $path
     * 
     * @return self
     */
    public function setSavePath(string $path): self
    {
        session_save_path($path);
        return $this;
    }

    /**
     * Specifies the number of seconds after which data will be seen as 'garbage' and potentially cleaned up. 
     * Default is 1440
     *
     * @param int $maxlifetime
     * 
     * @return self
     */
    public function setGcMaxlifetime(int $maxlifetime): self
    {
        self::$session_config["gc_maxlifetime"] = $maxlifetime;
        return $this;
    }

    /**
     * setGcProbability() in conjunction with session.gc_divisor is used to 
     * manage probability that the gc (garbage collection) routine is started. 
     * Defaults to 1
     *
     * @param int $probability
     * 
     * @return self
     */
    public function setGcProbability(int $probability): self
    {
        self::$session_config["gc_probability"] = $probability;
        return $this;
    }

    /**
     * session.gc_divisor coupled with session.gc_probability defines the probability that 
     * the gc (garbage collection) process is started on every session initialization. 
     * The probability is calculated by using gc_probability/gc_divisor, e.g. 
     * 1/100 means there is a 1% chance that the GC process starts on each request. 
     * session.gc_divisor defaults to 100.
     *
     * @param int $divisor
     * 
     * @return self
     */
    public function setGcDivisor(int $divisor): self
    {
        self::$session_config["gc_divisor"] = $divisor;
        return $this;
    }

    /**
     * Specifies whether the module will use strict session id mode
     *
     * @param bool $strict_mode
     * 
     * @return self
     */
    public function useStrictMode(bool $strict_mode = true): self
    {
        self::$session_config["use_strict_mode"] = $strict_mode;
        return $this;
    }

    /**
     * Check if sessions are enabled
     *
     * @return bool
     */
    public static function isSessionStarted(): bool
    {
        if (php_sapi_name() === 'cli') return false;
        if (version_compare(phpversion(), '5.4.0', '>='))
            return session_status() === PHP_SESSION_ACTIVE;

        return session_id() !== '';
    }

    /**
     * Perform session data garbage collection
     *
     * @param string|null $filename
     * 
     * @return void
     */
    public static function ExecuteGc(?string $filename): void
    {
        if (!is_null($filename)) {
            if (
                self::$handler_module == "Solital\Core\Session\Handler\EncryptedSessionHandler" ||
                self::$handler_module == "files" &&
                file_exists($filename)
            ) {
                if (filemtime($filename) < time() - 10) {
                    session_gc();
                    touch($filename);
                }
            }

            clearstatcache(true, $filename);
        }
    }

    /**
     * @param string $extension
     * 
     * @return void
     * @throws SessionExtensionNotFoundException
     */
    private function extensionEnumIsEnabled(string $extension): void
    {
        if ($extension === "memcached") {
            if (!class_exists('Memcached') || !extension_loaded('memcached'))
                throw new SessionExtensionNotFoundException('Memcached extension not enabled!');
        }

        if ($extension === "sqlite") {
            if (!extension_loaded('sqlite'))
                throw new SessionExtensionNotFoundException('sqlite extension not enabled!');
        }

        if ($extension === "redis") {
            if (!extension_loaded('redis'))
                throw new SessionExtensionNotFoundException('redis extension not enabled!');
        }
    }

    /**
     * @param object $classname
     * 
     * @return string
     */
    /* private function getClassWithoutNamespace(object $classname): string
    {
        $classname = get_class($classname);
        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
        return $pos;
    } */

    private static function isCli(): bool
    {
        if (defined('STDIN')) return true;
        if (php_sapi_name() === 'cli') return true;
        if (array_key_exists('SHELL', $_ENV)) return true;
        if (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and isset($_SERVER['argv']) > 0) return true;
        if (!array_key_exists('REQUEST_METHOD', $_SERVER)) return true;
        return false;
    }
}
