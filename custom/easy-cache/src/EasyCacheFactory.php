<?php

namespace Custom\EasyCache;

use Custom\EasyCache\Drivers\FileCacheDriver;
use Custom\EasyCache\Drivers\RedisCacheDriver;
use Custom\EasyCache\Drivers\MemcachedCacheDriver;

class EasyCacheFactory
{
    public static function createPsr6Cache(string $driverType, array $config): Psr6\CachePool
    {
        $driver = self::createDriver($driverType, $config);
        return new Psr6\CachePool($driver);
    }

    public static function createPsr16Cache(string $driverType, array $config): Psr16\SimpleCache
    {
        $driver = self::createDriver($driverType, $config);
        return new Psr16\SimpleCache($driver);
    }

    private static function createDriver(string $type, array $config): MemcachedCacheDriver|FileCacheDriver|RedisCacheDriver
    {
        return match ($type) {
            'file' => new FileCacheDriver($config['path'] ?? '/tmp/easy_cache'),
            'redis' => new RedisCacheDriver($config),
            'memcached' => new MemcachedCacheDriver($config),
            default => throw new \InvalidArgumentException("Invalid driver: $type")
        };
    }
}