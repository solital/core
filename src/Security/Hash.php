<?php

namespace Solital\Core\Security;

use SensitiveParameter;
use Deprecated\Deprecated;
use Solital\Core\Security\Exception\HashException;
use Solital\Core\Resource\Temporal\Temporal;
use Solital\Core\Kernel\{Application, Dotenv, Exceptions\DotenvException};
use SecurePassword\Encrypt\{Encryption, Adapter\OpenSslEncryption, Adapter\SodiumEncryption};

final class Hash
{
    /**
     * @var string
     */
    private static string $decoded;

    /**
     * Get default crypt on `bootstrap.yaml`
     *
     * @param string|null $default If `crypt` key not exists, OpenSSL will be used
     * 
     * @return string
     */
    private static function getCryptConfig(?string $default = null): string
    {
        $crypt = Application::yamlParse("bootstrap.yaml");

        if (array_key_exists("crypt", $crypt)) {
            if ($crypt["crypt"] == "sodium") self::checkSodium();
            return $crypt["crypt"];
        }

        return $default;
    }

    /**
     * Return a Encryption instance
     *
     * @param string $adapter_name
     * @param string $crypt_key
     * 
     * @return Encryption
     */
    private static function encryption(
        string $adapter_name,
        #[SensitiveParameter] string $crypt_key
    ): Encryption {
        if (getenv('APP_HASH') == '' || !Dotenv::isset('APP_HASH')) {
            throw new DotenvException("APP_HASH not found. Execute 'php vinci generate:hash' command");
        }

        $adapter = match ($adapter_name) {
            'openssl' => new OpenSslEncryption($crypt_key),
            'sodium' => new SodiumEncryption($crypt_key)
        };

        return new Encryption($adapter);
    }

    /**
     * Generates an encrypted key
     * 
     * @param string $value
     * @param string $time
     * 
     * @return string
     */
    public static function encrypt(#[SensitiveParameter] string $value, string $time = '+1 hour'): string
    {
        $expire_at = Temporal::createDatetime(date("Y-m-d H:i"))->modify($time)->toFormat("Y-m-d H:i");
        $data = [
            'value' => $value,
            'expire_at' => $expire_at
        ];

        if (
            Dotenv::isset('FIRST_SECRET') == true &&
            Dotenv::isset('SECOND_SECRET') == true &&
            !Dotenv::isset('APP_HASH')
        ) {
            $key = self::legacyOpenSSLEncryption('encrypt', $data);
        } else {
            $key =  self::encryption(
                self::getCryptConfig('openssl'),
                getenv('APP_HASH')
            )->encrypt(json_encode($data));
            $key = str_replace("==", "EQUALS", $key);
        }

        return (string)$key;
    }

    /**
     * Decrypts a key
     * 
     * @param string $key
     * 
     * @return static
     */
    public static function decrypt(#[SensitiveParameter] string $key): static
    {
        if (
            Dotenv::isset('FIRST_SECRET') == true &&
            Dotenv::isset('SECOND_SECRET') == true &&
            !Dotenv::isset('APP_HASH')
        ) {
            $decode = self::legacyOpenSSLEncryption('decrypt', key: $key);
        } else {
            $key = str_replace("EQUALS", "==", $key);
            $decode = self::encryption(self::getCryptConfig('openssl'), getenv('APP_HASH'))->decrypt($key);
        }

        self::$decoded = $decode;
        return new static();
    }

    /**
     * Checks the value of the encrypted key
     * 
     * @return mixed
     */
    public function value(): mixed
    {
        $json = json_decode(self::$decoded, true);
        return $json['value'];
    }

    /**
     * Checks if the key is still valid
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        $json = json_decode(self::$decoded, true);
        if ($json['expire_at'] < date("Y-m-d H:i:s")) return false;
        return true;
    }

    /**
     * Generates a sodium secret key
     * 
     * @return string
     * @deprecated Use `crypt` option in `bootstrap.yaml`
     */
    #[Deprecated("Use `crypt` option in `bootstrap.yaml`", "2024-06-25")]
    public static function getSodiumKey(): string
    {
        self::checkSodium();
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }

    /**
     * Generates an encrypted sodium key
     * 
     * @param string $data
     * @param null|string $key
     * 
     * @return string
     * @deprecated Use `crypt` option in `bootstrap.yaml`
     */
    #[Deprecated("Use `crypt` option in `bootstrap.yaml`", "2024-06-25")]
    public static function sodiumCrypt(
        #[SensitiveParameter] string $data,
        #[SensitiveParameter] ?string $key = null
    ): string {
        self::checkSodium();

        if (is_null($key)) {
            $key = self::getSodiumKey();
        }

        if (
            Dotenv::isset('FIRST_SECRET') == true &&
            Dotenv::isset('SECOND_SECRET') == true &&
            !Dotenv::isset('APP_HASH')
        ) {
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = sodium_crypto_secretbox($data, $nonce, $key);
            return base64_encode($nonce . $ciphertext);
        }

        return self::encryption('sodium', $key)->encrypt($data);
    }

    /**
     * Decrypts a sodium key
     * 
     * @param string $encoded
     * @param null|string $key
     * 
     * @return string|null
     * @deprecated Use `crypt` option in `bootstrap.yaml`
     */
    #[Deprecated("Use `crypt` option in `bootstrap.yaml`", "2024-06-25")]
    public static function sodiumDecrypt(
        #[SensitiveParameter] string $encoded,
        #[SensitiveParameter] ?string $key = null
    ): ?string {
        self::checkSodium();

        if (is_null($key)) {
            $key = self::getSodiumKey();
        }

        if (
            Dotenv::isset('FIRST_SECRET') == true &&
            Dotenv::isset('SECOND_SECRET') == true &&
            !Dotenv::isset('APP_HASH')
        ) {
            $decoded = base64_decode($encoded);
            $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
            $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
            $decoded = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
        } else {
            $decoded = self::encryption('sodium', $key)->decrypt($encoded);
        }

        if ($decoded != "") {
            return $decoded;
        }

        return null;
    }

    /**
     * Check if sodium is installed
     * 
     * @return true
     * @throws HashException
     */
    public static function checkSodium(): true
    {
        if (
            !defined('SODIUM_LIBRARY_MAJOR_VERSION') &&
            !defined('SODIUM_LIBRARY_MINOR_VERSION') &&
            !defined('SODIUM_LIBRARY_VERSION')
        ) {
            throw new HashException("libsodium not installed", 404);
        }

        return true;
    }

    /**
     * Generates a random bytes key
     *
     * @param int $length
     * 
     * @return string
     */
    public static function randomString(int $length = 64): string
    {
        $length = ($length < 4) ? 4 : $length;
        return bin2hex(random_bytes(($length - ($length % 2)) / 2));
    }

    /**
     * Used if APP_HASH not exists in .env file
     *
     * @param string $type
     * @param array|null $data
     * @param string|null $key
     * 
     * @return string
     * @throws HashException
     */
    private static function legacyOpenSSLEncryption(
        string $type,
        #[SensitiveParameter] ?array $data = null,
        ?string $key = null
    ): string {
        if (getenv('FIRST_SECRET') == "" || getenv('SECOND_SECRET') == "") {
            throw new HashException(
                "Empty FIRST_SECRET and/or SECOND_SECRET secrets. Verify '.env' file or use APP_HASH",
                404
            );
        }

        if ($type == 'encrypt') {
            $encode = openssl_encrypt(json_encode($data), "AES-128-CBC", pack('a16', getenv('FIRST_SECRET')), 0, pack('a16', getenv('SECOND_SECRET')));
            $encode = base64_encode($encode);
            $encode = str_replace("==", "EQUALS", $encode);
            return $encode;
        }

        if ($type == 'decrypt') {
            $key = str_replace("EQUALS", "==", $key);
            $decode = base64_decode($key);
            $decode = openssl_decrypt($decode, "AES-128-CBC", pack('a16', getenv('FIRST_SECRET')), 0, pack('a16', getenv('SECOND_SECRET')));
            return $decode;
        }

        return '';
    }
}
