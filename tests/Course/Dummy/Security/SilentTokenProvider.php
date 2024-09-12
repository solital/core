<?php

namespace Solital\Test\Course\Dummy\Security;

use Solital\Core\Resource\Session;
use Solital\Core\Http\Security\TokenProviderInterface;

class SilentTokenProvider implements TokenProviderInterface {

    protected $token;
    public const CSRF_KEY = 'CSRF-TOKEN';
    private const CSRF_VALIDATE = 'CSRF-VALIDATE';

    public function __construct()
    {
        $this->refresh();
    }

    /**
     * Refresh existing token
     */
    public function refresh(): void
    {
        $this->token = uniqid('', false);
    }

    /**
     * Validate valid CSRF token
     *
     * @param string $token
     * @return bool
     */
    public function validate(): bool
    {
        if ($this->getToken() == null ||  empty($_REQUEST['csrf_token'])) {
            return false;
        }

        return true;
    }

    /**
     * Get token token
     *
     * @param string|null $defaultValue
     * @return string|null
     */
    public function getToken(?string $defaultValue = null): ?string
    {
        return $this->token ?? $defaultValue;
    }

    /**
     * Set csrf token
     * @param int $seconds
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
}