<?php

use Solital\Core\Course\Course as Course;
use Solital\Core\Kernel\Application;

/**
 * Get current csrf-token
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
 * @param string $method
 * @return string
 */
function spoofing(string $method): string
{
    $method = strtoupper($method);
    return "<input type='hidden' name='_method' value='" . $method . "' readonly />";
}

/**
 * @param $value
 * 
 * @return string
 */
function pass_hash(string $value, bool $info = false): string
{
    return Application::provider('solital-password')->create($value, $info);
}

/**
 * @param string $value
 * @param string $hash
 */
function pass_verify(string $value, string $hash)
{
    return Application::provider('solital-password')->verify($value, $hash);
}