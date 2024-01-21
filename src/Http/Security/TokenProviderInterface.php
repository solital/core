<?php

namespace Solital\Core\Http\Security;

interface TokenProviderInterface
{
    /**
     * Validate valid CSRF token
     *
     * @param string $token
     * @return bool
     */
    public function validate(): bool;

    /**
     * Get token token
     *
     * @param string|null $defaultValue
     * @return string|null
     */
    public function getToken(?string $defaultValue = null): ?string;

    /**
     * Set csrf token - default: 30 minutes
     * 
     * @param int $seconds
     * @return string
     */
    public function setToken(int $seconds = 1800): string;

}