<?php

namespace Custom\EasyCache\Psr16;

use Custom\EasyCache\Drivers\DriverInterface;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class SimpleCache implements CacheInterface
{
    private DriverInterface $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function get($key, $default = null)
    {
        return $this->driver->get($key) ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $ttl = $this->convertTtlToSeconds($ttl);
        return $this->driver->set($key, $value, $ttl);
    }

    private function convertTtlToSeconds($ttl): ?int
    {
        if ($ttl instanceof \DateInterval) {
            $ttl = $ttl->s;
        }
        return $ttl;
    }

    public function delete($key): bool
    {
        try {
            $this->driver->delete($key);
            return true;
        } catch (Throwable $exception) {
            return false;
        }

    }

    public function clear(): bool
    {
        try {
            $this->driver->delete('*');
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $data = $this->driver->get($key);
            if ($data) {
                $result[] = [$key => $data['value']];
            }
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        try {
            foreach ($values as $key => $value) {
                $ttl = $this->convertTtlToSeconds($ttl);
                $this->driver->set($key, $value, $ttl);
            }
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function deleteMultiple($keys): bool
    {
        try {
            foreach ($keys as $key) {
                $this->driver->delete($key);
            }
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function has($key): bool
    {
        return $this->driver->has($key);
    }
}