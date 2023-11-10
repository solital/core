<?php

namespace Solital\Core\Cache\Psr6;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    /**
     * @var string
     */
    private string $key;

    /**
     * @var mixed
     */
    private mixed $value;

    /**
     * @var int
     */
    private int $expiration;

    /**
     * @var bool
     */
    private bool $dirty = false;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __construct(string $key, mixed $value)
    {
        $this->key = $key;
        $this->value = $value;
        $this->expiration = $this->getDefaultExpiration();
    }

    /**
     * @return mixed
     */
    private function getDefaultExpiration(): mixed
    {
        return time() + 60 * 60 * 24;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function get(): mixed
    {
        if ($this->isHit() === false) {
            return null;
        }
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isHit(): bool
    {
        if ($this->value === null) {
            return false;
        }
        if ($this->expiration < time()) {
            $this->value = null;
            $this->expiration = $this->getDefaultExpiration();
            $this->dirty();
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value
     * 
     * @return static
     */
    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->expiration = $this->getDefaultExpiration();

        return $this->dirty();
    }

    /**
     * @param \DateTimeInterface|null $expiration
     * @return $this|CacheItem
     */
    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        if ($expiration === null) {
            $this->expiration = $this->getDefaultExpiration();
        } else {
            $this->expiration = $expiration->getTimestamp();
        }

        return $this->dirty();
    }

    /**
     * @param \DateInterval|int|null $time
     * @return $this
     */
    public function expiresAfter(int|\DateInterval|null $time): static
    {
        if ($time === null) {
            $this->expiration = $this->getDefaultExpiration();
        } else if (is_int($time)) {
            $this->expiration = time() + $time;
        } else {
            $this->expiration = (new \DateTimeImmutable())->add($time)->getTimestamp();
        }

        return $this->dirty();
    }

    /**
     * @return CacheItem
     */
    public function dirty(bool $dirty = true): CacheItem
    {
        $this->dirty = $dirty;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDirty(): bool
    {
        return $this->dirty;
    }

    /**
     * @return int
     */
    public function getExpirationTime(): int
    {
        return $this->expiration;
    }
}
