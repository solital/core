<?php

namespace Solital\Core\Console\ProgressBar;

class Registry
{
    /**
     * Registry
     * 
     * @var array
     */
    private array $registry = [];

    /**
     * Sets the value for a key
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setValue(string $key, mixed $value)
    {
        $this->registry[$key] = $value;
    }

    /**
     * Returns the value associated to a key
     * 
     * @param string $key
     * 
     * @return mixed
     * @throws \RunTimeException
     */
    public function getValue(string $key): mixed
    {
        if (!isset($this->registry[$key])) {
            throw new \RunTimeException('Invalid offset requested');
        }

        return $this->registry[$key];
    }
}
