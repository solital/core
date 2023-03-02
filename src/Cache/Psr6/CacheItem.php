<?php

namespace Solital\Core\Cache\Psr6;

use Psr\Cache\CacheItemInterface;

/** @phpstan-consistent-constructor */
class CacheItem implements CacheItemInterface
{
    /**
     * @var null|string
     */
    protected ?string $key;
    
    /**
     * @var null
     */
    protected $value = null;
    
    /**
     * @var null
     */
    protected $expires = null;

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
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isHit(): bool
    {
        return !$this->isKeyEmpty() && file_exists(CacheDir::getCacheDir() . $this->key);
    }

    /**
     * @param mixed $key
     * 
     * @return void
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @param mixed $key
     * 
     * @return static
     */
    public function set(mixed $value): static
    {
        $this->value = $value;

        return new static;
    }

    /**
     * @param \DateTimeInterface|null $expiration
     * 
     * @return static
     */
    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        return self::isDateInFuture($expiration) && $this->expires = $expiration->getTimestamp();

        return $this;
    }

    /**
     * @param \DateTimeInterface|null $expiration
     * 
     * @return static
     */
    public function expiresAfter(int|\DateInterval|null $time): static
    {
        $futureDate = date_create()->add(date_interval_create_from_date_string($time));

        return self::isDateInFuture($futureDate) && $this->expires = $futureDate->getTimestamp();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @return mixed
     */
    public function isKeyEmpty()
    {
        return empty($this->key);
    }

    /**
     * @return mixed
     */
    public function isValueEmpty()
    {
        return empty($this->value);
    }

    /**
     * Check if $date is in the future.
     * 
     * @param \DateTime $date
     * 
     * @return mixed
     */
    public static function isDateInFuture(\DateTime $date)
    {
        return $date->getTimestamp() > date_create()->getTimestamp();
    }
}
