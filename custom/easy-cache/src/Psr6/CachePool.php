<?php

namespace Custom\EasyCache\Psr6;

use Throwable;
use DateTimeImmutable;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Custom\EasyCache\Drivers\DriverInterface;

class CachePool implements CacheItemPoolInterface
{
    private DriverInterface $driver;
    private array $deferred = [];

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function getItem($key): CacheItem
    {
        $data = $this->driver->get($key);
        $item = new CacheItem($key);

        if ($data) {
            $item->set($data['value'])
                ->expiresAt($data['expire'] ? DateTimeImmutable::createFromFormat('U', $data['expire']) : null);
        }
        return $item;
    }

    public function save(CacheItemInterface $item): bool
    {
        if (!$item instanceof CacheItem) {
            return false;
        }

        $ttl = $item->getExpiresAt() ? $item->getExpiresAt() - time() : null;
        return $this->driver->set($item->getKey(), $item->get(), $ttl);
    }

    public function getItems(array $keys = array()): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->getItem($key);
        }

        return $result;
    }

    public function hasItem($key): bool
    {
        return $this->driver->has($key);
    }

    public function clear(): bool
    {
        try {
            $this->driver->clear();
            return true;
        } catch (Throwable $exception) {
            return false;
        }

    }

    public function deleteItem($key): bool
    {
        try {
            $this->driver->delete($key);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function deleteItems(array $keys): void
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }
    }

    public function saveDeferred(CacheItemInterface $item): void
    {
        $this->deferred[] = $item;
    }

    public function commit(): true
    {
        foreach ($this->deferred as $item) {
            $this->save($item);
        }
        return true;
    }
}
