<?php

namespace Solital\Core\Http\Security;

use Solital\Core\Resource\Session;
use Solital\Core\Http\Security\TokenProviderInterface;

class CookieTokenProvider implements TokenProviderInterface
{
    public const CSRF_KEY = 'CSRF-TOKEN';
    private const CSRF_VALIDATE = 'CSRF-VALIDATE';

    /**
     * @var mixed
     */
    protected mixed $token;

    /**
     * CookieTokenProvider constructor.
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
        if (
            $this->getToken() == null || 
            Session::has(static::CSRF_KEY) == false || 
            empty($_REQUEST['csrf_token']) || 
            $_REQUEST['csrf_token'] != Session::get(static::CSRF_KEY)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Set csrf token
     * @param int $seconds
     * 
     * @return string
     */
    public function setToken(int $seconds = 20): string
    {
        if (Session::get(static::CSRF_VALIDATE) < time() || empty(Session::get(static::CSRF_VALIDATE))) {
            Session::set(static::CSRF_VALIDATE, time() + $seconds);
            $token = base64_encode(random_bytes(20));
            Session::set(static::CSRF_KEY, $token);
        }

        return Session::get(static::CSRF_KEY);
    }

    /**
     * Get csrf token
     * @param string|null $defaultValue
     * 
     * @return string|null
     */
    public function getToken(?string $defaultValue = null): ?string
    {
        $this->token = ($this->hasToken() === true) ? $_SESSION[static::CSRF_KEY] : null;
        return $this->token ?? $defaultValue;
    }

    /**
     * Returns whether the csrf token has been defined
     * 
     * @return bool
     */
    public function hasToken(): bool
    {
        return isset($_SESSION[static::CSRF_KEY]);
    }
}
