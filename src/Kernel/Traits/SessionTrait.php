<?php

namespace Solital\Core\Kernel\Traits;

use Katrina\Connection\Connection;
use Solital\Core\Kernel\Application;
use Solital\Core\Session\SessionConfiguration;
use Solital\Core\Session\Enum\{SessionCacheLimiterEnum, SessionSaveHandlerEnum};
use Solital\Core\Session\Exception\{SessionCacheLimiterNotFoundException, SessionStorageNotFoundException};
use Solital\Core\Session\Handler\{
    ApcuSessionHandler,
    DumpSessionHandler,
    EncryptedSessionHandler,
    PdoSessionHandler,
};

trait SessionTrait
{
    public static function loadSession(string $sessions_dir, array|false $yaml_config): void
    {
        $session = new SessionConfiguration();

        if ($yaml_config != false) {
            Application::connectionDatabase();
            $pdo = Connection::getInstance();

            if (!empty($yaml_config["name"])) $session->setName($yaml_config["name"]);
            if (!empty($yaml_config["save_handler"])) {
                $handler = match ($yaml_config["save_handler"]) {
                    "files" => SessionSaveHandlerEnum::FILES,
                    "sqlite" => SessionSaveHandlerEnum::SQLITE,
                    "memcached" => SessionSaveHandlerEnum::MEMCACHED,
                    "encrypt" => new EncryptedSessionHandler(),
                    "pdo" => new PdoSessionHandler($pdo), // no need save path method
                    "apcu" => new ApcuSessionHandler(), // no need save path method
                    "dump" => new DumpSessionHandler(), // no need save path method
                    default => throw new SessionStorageNotFoundException("Session storage not found")
                };

                $session->setSaveHandler($handler);
            }

            if (!empty($yaml_config["save_path"])) {
                (
                    !empty($yaml_config["save_handler"]) &&
                    $yaml_config["save_handler"] == "memcached" ||
                    $yaml_config["save_handler"] == "redis"
                ) ?
                    $session->setSavePath($yaml_config["save_path"]) :
                    $session->setSavePath($sessions_dir);
            }

            if (!empty($yaml_config["cache_limiter"])) {
                $limiter = match ($yaml_config["cache_limiter"]) {
                    "public" => SessionCacheLimiterEnum::PUBLIC,
                    "private_no_cache" => SessionCacheLimiterEnum::PRIVATE_NO_EXPIRE,
                    "private" => SessionCacheLimiterEnum::PRIVATE,
                    "nocache" => SessionCacheLimiterEnum::NOCACHE,
                    default => throw new SessionCacheLimiterNotFoundException("Session cache limiter not found")
                };

                $session->setCacheLimiter($limiter);
            }

            if (!empty($yaml_config["cache_expire"])) $session->setCacheExpire($yaml_config["cache_expire"]);
            if (!empty($yaml_config["strict_mode"])) $session->useStrictMode($yaml_config["strict_mode"]);
            if (!empty($yaml_config["gc_max_lifetime"])) $session->setGcMaxlifetime($yaml_config["gc_max_lifetime"]);
            if (!empty($yaml_config["gc_probability"])) $session->setGcProbability($yaml_config["gc_probability"]);
            if (!empty($yaml_config["gc_divisor"])) $session->setGcDivisor($yaml_config["gc_divisor"]);
        }

        $session->start();
    }
}
