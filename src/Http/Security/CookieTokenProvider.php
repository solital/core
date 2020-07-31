<?php

namespace Solital\Core\Http\Security;

use Solital\Core\Http\Security\Exceptions\SecurityException;
use Solital\Core\Resource\Session;

class CookieTokenProvider implements TokenProviderInterface
{
    public const CSRF_KEY = 'CSRF-TOKEN';
    private const CSRF_VALIDATE = 'CSRF-VALIDATE';
    protected $token;

    /**
     * CookieTokenProvider constructor.
     * @throws SecurityException
     */
    public function __construct()
    {
        $this->token = $this->getToken();

        if ($this->token === null) {
            $this->token = $this->setToken();
        }
    }

    /**
     * Validate valid CSRF token
     *
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->getToken() == null || Session::has(static::CSRF_KEY) == false || empty($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] != Session::show(static::CSRF_KEY)) {
            return false;
        }

        return true;
    }

    /**
     * Set csrf token
     * @param int $seconds
     * @return string
     */
    public function setToken(int $seconds = 20): string
    {
        if (Session::show(static::CSRF_VALIDATE) < time() || empty(Session::show(static::CSRF_VALIDATE))) {
            Session::new(static::CSRF_VALIDATE, time() + $seconds);
            $token = base64_encode(random_bytes(20));
            Session::new(static::CSRF_KEY, $token);
        }

        return Session::show(static::CSRF_KEY);
    }

    /**
     * Get csrf token
     * @param string|null $defaultValue
     * @return string|null
     */
    public function getToken(?string $defaultValue = null): ?string
    {
        $this->token = ($this->hasToken() === true) ? $_SESSION[static::CSRF_KEY] : null;
        return $this->token ?? $defaultValue;
    }

    /**
     * Returns whether the csrf token has been defined
     * @return bool
     */
    public function hasToken(): bool
    {
        return isset($_SESSION[static::CSRF_KEY]);
    }

}