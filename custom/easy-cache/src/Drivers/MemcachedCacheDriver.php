<?php

namespace Custom\EasyCache\Drivers;

use Memcached;
use Throwable;

class MemcachedCacheDriver implements DriverInterface
{
    private Memcached $memcached;

    public function __construct(string $host = '127.0.0.1', int $port = 11211) {
        $this->memcached = new Memcached();
        $this->memcached->addServer($host, $port);
    }

    public function get(string $key): ?array {
        $value = $this->memcached->get($key);
        return $value ? ['value' => $value] : null;
    }

    public function set(string $key, $value, ?int $ttl): bool {
        return $this->memcached->set($key, $value, $ttl ?: 0);
    }

    public function delete(string $key): bool
    {
        try {
            $this->memcached->delete($key);
            return true;
        } catch (Throwable $exception) {
            return false;
        }

    }

    public function clear(): bool
    {
        try {
            $this->memcached->flush();
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function has(string $key): bool
    {
        return (bool)$this->memcached->get($key);
    }
}