<?php

namespace Solital\Core\Security;

use Solital\Core\Exceptions\NotFoundException;

class Hash
{
    /**
     * @var string
     */
    private static string $decoded;

    /**
     * @return void
     */
    public static function checkSecrets(): void
    {
        if ($_ENV['FIRST_SECRET'] == "" || $_ENV['SECOND_SECRET'] == "") {
            NotFoundException::notFound(404, "Empty OPENSSL variables", "Check that the FIRST_SECRET and SECOND_SECRET variables have a value defined in the '.env' file", "Hash");
        }
    }

    /**
     * Generates an encrypted key
     * 
     * @param string $value
     * @param string $time
     * 
     * @return string
     */
    public static function encrypt(string $value, string $time = '+1 hour'): string
    {
        self::checkSecrets();

        $date = new \DateTime(date('Y-m-d H:i'));
        $date->modify($time);
        $res = $date->format('Y-m-d H:i');

        $data = [
            'value' => $value,
            'expire_at' => $res
        ];

        $key = openssl_encrypt(json_encode($data), "AES-128-CBC", pack('a16', $_ENV['FIRST_SECRET']), 0, pack('a16', $_ENV['SECOND_SECRET']));
        $key = base64_encode($key);
        $key = str_replace("==", "EQUALS", $key);

        return (string)$key;
    }

    /**
     * Decrypts a key
     * 
     * @param string $key
     * 
     * @return new static
     */
    public static function decrypt(string $key)
    {
        self::checkSecrets();

        if ($key == null || !isset($key)) {
            return null;
        }

        $key = str_replace("EQUALS", "==", $key);
        $decode = base64_decode($key);
        $decode = openssl_decrypt($decode, "AES-128-CBC", pack('a16', $_ENV['FIRST_SECRET']), 0, pack('a16', $_ENV['SECOND_SECRET']));

        self::$decoded = $decode;

        return new static();
    }

    /**
     * Checks the value of the encrypted key
     */
    public function value()
    {
        $json = json_decode(self::$decoded, true);

        return $json['value'];
    }

    /**
     * Checks if the key is still valid
     * @return bool
     */
    public function isValid(): bool
    {
        $json = json_decode(self::$decoded, true);

        if ($json['expire_at'] < date("Y-m-d H:i:s")) {
            return false;
        } else {
            return true;
        }
    }
}
