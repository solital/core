<?php

namespace Solital\Core\Auth;

use SensitiveParameter;
use Solital\Core\Exceptions\NotFoundException;
use Solital\Core\Kernel\Exceptions\DotenvException;
use Solital\Core\Kernel\{Application, Dotenv};
use SecurePassword\{AlgorithmEnum, SecurePassword};

class Password
{
    /**
     * @var null|SecurePassword
     */
    private ?SecurePassword $password = null;

    /**
     * @var mixed
     */
    private mixed $algo;

    /**
     * @var string
     */
    private string $pepper = '';

    /**
     * @var string
     */
    private string $cost;

    /**
     * @var string
     */
    private string $memory_cost;

    /**
     * @var string
     */
    private string $time_cost;

    /**
     * @var string
     */
    private string $threads;

    /**
     * @var int
     */
    private int $wait_microseconds = 250000;

    /**
     * @var string
     */
    private string $crypt_type = 'openssl';

    /**
     * Construct
     */
    public function __construct()
    {
        $this->setConfig();
        $this->password = new SecurePassword([
            'algo' => $this->algo,
            'cost' => (int)$this->cost,
            'memory_cost' => $this->memory_cost,
            'time_cost' => $this->time_cost,
            'threads' => $this->threads
        ]);
        $this->password->setPepper($this->pepper, $this->crypt_type);
    }

    /**
     * Creates a password peppered using the entered password and the secret entry.
     * 
     * @param string $password
     * @param bool $info
     * 
     * @return mixed
     */
    public function create(#[SensitiveParameter] string $password, bool $info = false): mixed
    {
        return ($info == true) ?
            $this->password->createHash($password)->getHashInfo() :
            $this->password->createHash($password)->getHash();
    }

    /**
     * Verify if the hash generated with `createHash` is valid
     * 
     * @param string $password
     * @param string $hash
     * 
     * @return bool
     */
    public function verify(
        #[SensitiveParameter] string $password,
        #[SensitiveParameter] string $hash
    ): bool {
        return $this->password->verifyHash($password, $hash, $this->wait_microseconds);
    }

    /**
     * This function checks to see if the supplied hash implements the algorithm and options provided. If not, it is assumed that the hash needs to be rehashed.
     * 
     * @param string $password
     * @param string $hash
     * 
     * @return mixed
     */
    public function needsRehash(
        #[SensitiveParameter] string $password,
        #[SensitiveParameter] string $hash
    ): mixed {
        return $this->password->needsRehash($password, $hash);
    }

    /**
     * @return Password
     */
    private function setConfig(): Password
    {
        $config = Application::yamlParse('auth.yaml');
        $algo = $config['password']['algorithm'];

        $this->algo = match ($algo) {
            'default' => AlgorithmEnum::DEFAULT,
            'argon2' => AlgorithmEnum::ARGON2I,
            'argon2d' => AlgorithmEnum::ARGON2ID,
            default => throw new NotFoundException('Password: hash algorithm not found')
        };

        if (isset($config['password']['pepper'])) {
            $this->pepper = $config['password']['pepper'];
        } elseif (Dotenv::isset('APP_HASH') == true && getenv('APP_HASH') != false) {
            $this->pepper = getenv('APP_HASH');
        } else {
            if (getenv('APP_HASH') == '' || !Dotenv::isset('APP_HASH'))
                throw new DotenvException("APP_HASH not found. Execute 'php vinci generate:hash' command");
        }

        if (isset($config['password']['crypt_type'])) $this->crypt_type = $config['password']['crypt_type'];
        if (isset($config['password']['wait_microseconds']))
            $this->wait_microseconds = $config['password']['wait_microseconds'];

        $this->cost = $config['password']['cost'];
        $this->memory_cost = $config['password']['memory_cost'];
        $this->time_cost = $config['password']['time_cost'];
        $this->threads = $config['password']['threads'];
        return $this;
    }
}
