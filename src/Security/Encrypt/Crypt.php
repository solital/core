<?php

namespace Solital\Core\Security\Encrypt;

class Crypt
{
    /**
     * The cipher method. For a list of available cipher methods, use openssl_get_cipher_methods()
     * 
     * @var string
     */
    private static string $cipher_methods = "AES-256-CBC";

    /**
     * When OPENSSL_RAW_DATA is specified, the returned data is returned as-is.
     * 
     * @var int
     */
    const OPTIONS = OPENSSL_RAW_DATA;

    /**
     * The key
     *
     * Should have been previously generated in a cryptographically safe way, like openssl_random_pseudo_bytes
     * 
     * @var string
     */
    private string $secretKey;

    /**
     * IV - A non-NULL Initialization Vector.
     *
     * Encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
     * 
     * @var string
     */
    private string $iv;

    /**
     * @param string $secretKey
     * @param string|null $iv
     * @param string $cipher_methods
     */
    public function __construct(string $secretKey, string $iv = null)
    {
        $this->secretKey = hash('sha256', $secretKey);
        $this->iv = $iv ?: self::generateIV();
    }

    /**
     * @param string $new_cipher_methods
     * 
     * @return Crypt
     */
    public function setCipherMethod(string $new_cipher_methods): Crypt
    {
        self::$cipher_methods = $new_cipher_methods;
        $methods = openssl_get_cipher_methods();

        if (!in_array(self::$cipher_methods, $methods)) {
            throw new \Exception("Cipher not found");
        }

        return $this;
    }

    /**
     * @param string $value
     * 
     * @return string
     */
    public function encrypt(string $value): string
    {
        $output = openssl_encrypt(
            $value,
            self::$cipher_methods,
            $this->secretKey,
            self::OPTIONS,
            $this->iv
        );

        return base64_encode($output);
    }

    /**
     * @param string $value
     * 
     * @return string
     */
    public function decrypt(string $value): string
    {
        return openssl_decrypt(
            base64_decode($value),
            self::$cipher_methods,
            $this->secretKey,
            self::OPTIONS,
            $this->iv
        );
    }

    /**
     * @return string
     */
    public function iv(): string
    {
        return $this->iv;
    }

    /**
     * Generate IV
     *
     * @return int Returns a string of pseudo-random bytes, with the number of bytes expected by the method AES-256-CBC
     */
    public static function generateIV(): string
    {
        $ivNumBytes = openssl_cipher_iv_length(self::$cipher_methods);
        return openssl_random_pseudo_bytes($ivNumBytes);
    }

    /**
     * Generate a key
     *
     * @param int $length The length of the desired string of bytes. Must be a positive integer.
     *
     * @return int Returns the hexadecimal representation of a binary data
     */
    public static function generateKey($length = 512): string
    {
        $bytes = openssl_random_pseudo_bytes($length);
        return bin2hex($bytes);
    }
}
