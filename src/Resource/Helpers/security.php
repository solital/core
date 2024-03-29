<?php

use Solital\Core\Course\Course as Course;
use Solital\Core\Kernel\Application;

/**
 * Get current csrf-token
 * 
 * @return string|null
 */
function csrf_token(int $minutes = 1800): ?string
{
    $baseVerifier = Course::router()->getCsrfVerifier();
    if ($baseVerifier !== null) {
        return "<input type='hidden' name='csrf_token' value='" . $baseVerifier->getTokenProvider()->setToken($minutes) . "'>";
    }

    return null;
}

/**
 * Form Method Spoofing
 * 
 * @param string $method
 * @return string
 */
function spoofing(string $method): string
{
    $method = strtoupper($method);
    return "<input type='hidden' name='_method' value='" . $method . "' readonly />";
}

/**
 * Generate password hash using PHP Secure Password component
 * 
 * @param $value
 * 
 * @return string
 */
function pass_hash(#[\SensitiveParameter] string $value, bool $info = false): string
{
    return Application::provider('solital-password')->create($value, $info);
}

/**
 * Checks the hash generated by the `pass_hash` helper or the PHP Secure Password component
 * 
 * @param string $value
 * @param string $hash
 * 
 * @return bool
 */
function pass_verify(#[\SensitiveParameter] string $value, string $hash): bool
{
    return Application::provider('solital-password')->verify($value, $hash);
}