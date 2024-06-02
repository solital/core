<?php

/**
 * Generate a uniquid ID
 * 
 * @param int|float $lenght
 * 
 * @return string
 */
function uniqid_real(int|float $lenght = 13): string
{
    // uniqid gives 13 chars, but you could adjust it to your needs.
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new \Exception("no cryptographically secure random function available");
    }

    return substr(bin2hex($bytes), 0, $lenght);
}
