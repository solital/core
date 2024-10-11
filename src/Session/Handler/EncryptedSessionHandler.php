<?php

namespace Solital\Core\Session\Handler;

use Override;
use SensitiveParameter;

class EncryptedSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
{
    protected string $key;

    protected string $cipher_algo = "AES-256-CBC";

    private string $savePath = '';

    #[Override]
    public function open(string $path, string $name): bool
    {
        $this->savePath = $path;
        $this->key = $this->getKey('KEY_' . $name);

        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }

        return true;
    }

    #[Override]
    public function close(): bool
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    #[Override]
    public function read(#[SensitiveParameter] string $id): string|false
    {
        $filename = $this->savePath . DIRECTORY_SEPARATOR . "sess_" . $id;
        clearstatcache(true, $filename);

        if (file_exists($filename)) {
            $data = (string) file_get_contents($filename);
            return empty($data) ? '' : $this->decrypt($data, $this->key);
        }

        return '';
    }

    #[Override]
    public function write(#[SensitiveParameter] string $id, #[SensitiveParameter] string $data): bool
    {
        $data = $this->encrypt($data, $this->key);
        return file_put_contents(
            $this->savePath . DIRECTORY_SEPARATOR . "sess_" . $id,
            $data
        ) === false ? false : true;
    }

    #[Override]
    public function destroy(#[SensitiveParameter] string $id): bool
    {
        $file = $this->savePath . "/sess_" . $id;
        if (file_exists($file)) unlink($file);
        return true;
    }

    #[\ReturnTypeWillChange]
    #[Override]
    public function gc(int $maxlifetime): int|false
    {
        foreach (glob($this->savePath . DIRECTORY_SEPARATOR . "sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) unlink($file);
        }

        return true;
    }

    #[Override]
    public function updateTimestamp(
        #[SensitiveParameter] string $id,
        #[SensitiveParameter] string $data
    ): bool {
        return $this->write($id, $data);
    }

    #[Override]
    public function validateId(#[SensitiveParameter] string $id): bool
    {
        return '' !== $this->read($id);
    }

    /**
     * decrypt AES 256
     *
     * @param string $edata
     * @param string $password
     *
     * @return string data
     */
    private function decrypt(
        #[SensitiveParameter] string $edata,
        #[SensitiveParameter] string $password
    ): string {
        $data = base64_decode($edata);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);

        $rounds = 3; // depends on key length
        $data00 = $password . $salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];

        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1] . $data00, true);
            $result .= $hash[$i];
        }

        $key = substr($result, 0, 32);
        $iv = substr($result, 32, 16);

        return openssl_decrypt($ct, $this->cipher_algo, $key, true, $iv);
    }

    /**
     * crypt AES 256
     *
     * @param string $data
     * @param string $password
     * 
     * @return string encrypted data
     */
    private function encrypt(
        #[SensitiveParameter] string $data,
        #[SensitiveParameter] string $password
    ): string {
        // Generate a cryptographically secure random salt using random_bytes()
        $salt = random_bytes(16);

        $salted = '';
        $dx = '';

        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48) {
            $dx = hash('sha256', $dx . $password . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);

        $encrypted_data = openssl_encrypt($data, $this->cipher_algo, $key, true, $iv);
        return base64_encode($salt . $encrypted_data);
    }

    /**
     * Get the encryption and authentication keys from cookie
     *
     * @param string $name
     * 
     * @return string
     */
    private function getKey(#[SensitiveParameter] string $name): string
    {
        if (empty($_COOKIE[$name])) {
            $key = random_bytes(64); // 32 for encryption and 32 for authentication
            $cookieParam = session_get_cookie_params();
            $encKey = base64_encode($key);

            setcookie(
                $name,
                $encKey,
                // if session cookie lifetime > 0 then add to current time
                // otherwise leave it as zero, honoring zero's special meaning
                // expire at browser close.
                ($cookieParam['lifetime'] > 0) ? time() + $cookieParam['lifetime'] : 0,
                $cookieParam['path'],
                $cookieParam['domain'],
                $cookieParam['secure'],
                $cookieParam['httponly']
            );

            $_COOKIE[$name] = $encKey;
        } else {
            $key = base64_decode($_COOKIE[$name]);
        }

        return $key;
    }
}
