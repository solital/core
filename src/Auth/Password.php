<?php

namespace Solital\Core\Auth;

use Solital\Core\Kernel\Application;
use SecurePassword\{SecurePassword, HashAlgorithm};

class Password
{
    /**
     * @var null|SecurePassword
     */
    private ?SecurePassword $password = null;

    /**
     * @var mixed
     */
    private $algo;

    /**
     * @var string
     */
    private string $pepper;

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

        $this->password->setPepper($this->pepper);
    }

    /**
     * @param string $password
     * @param bool $info
     * 
     * @return mixed
     */
    public function create(string $password, bool $info = false): mixed
    {
        if ($info == true) {
            return $this->password->createHash($password)->getHashInfo();
        } elseif ($info == false) {
            return $this->password->createHash($password)->getHash();
        }
    }

    /**
     * @param string $password
     * @param string $hash
     * 
     * @return mixed
     */
    public function verify(string $password, string $hash): mixed
    {
        return $this->password->verifyHash($password, $hash);
    }

    /**
     * @param string $password
     * @param string $hash
     * 
     * @return mixed
     */
    public function needsRehash(string $password, string $hash): mixed
    {
        return $this->password->needsRehash($password, $hash);
    }

    /**
     * @return Password
     */
    private function setConfig(): Password
    {
        $config = Application::getYamlVariables(5, 'auth.yaml');
        $algo = $config['password']['algorithm'];

        if ($algo == 'default') {
            $this->algo = HashAlgorithm::DEFAULT;
        } else if ($algo == 'argon2') {
            $this->algo = HashAlgorithm::ARGON2I;
        } elseif ($algo == 'argon2d') {
            $this->algo = HashAlgorithm::ARGON2ID;
        }

        $this->pepper = $config['password']['pepper'];
        $this->cost = $config['password']['cost'];
        $this->memory_cost = $config['password']['memory_cost'];
        $this->time_cost = $config['password']['time_cost'];
        $this->threads = $config['password']['threads'];

        return $this;
    }
}
