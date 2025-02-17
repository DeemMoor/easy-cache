<?php

namespace Custom\EasyCache\Psr6;

use DateTime;
use DateInterval;
use DateTimeInterface;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    private string $key;
    private mixed $value;
    private bool $isHit = false;
    private DateInterval|int|null $expires;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        if ($this->expires !== null && $this->expires < time()) {
            $this->isHit = false;
        }
        return $this->isHit;
    }

    public function set($value): self
    {
        $this->value = $value;
        $this->isHit = true;
        return $this;
    }

    public function expiresAt($expiration): self
    {
        if ($expiration instanceof DateTimeInterface) {
            $this->expires = $expiration->getTimestamp();
        } else {
            $this->expires = $expiration;
        }
        return $this;
    }

    public function expiresAfter($time): self
    {
        if ($time === null) {
            $this->expires = null;
        } elseif ($time instanceof DateInterval) {
            $this->expires = (new DateTime())->add($time)->getTimestamp();
        } else {
            $this->expires = time() + $time;
        }
        return $this;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expires;
    }
}