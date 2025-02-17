<?php

namespace Custom\EasyCache\Drivers;

use Predis\Client;
use Throwable;

class RedisCacheDriver implements DriverInterface
{
    private Client $client;

    public function __construct(array $config = [])
    {
        $this->client = new Client($config);
    }

    public function get(string $key): ?array
    {
        $value = $this->client->get($key);
        return $value ? ['value' => $value] : null;
    }

    public function set(string $key, $value, ?int $ttl): bool
    {
        try {
            $arTtl = [];
            if ($ttl) {
                if ($ttl instanceof \DateInterval) {
                    $ttl = date_create('@0')->add($ttl)->getTimestamp();
                }
                $arTtl = ['EX', $ttl];
            }
            $this->client->executeRaw(
                array_merge(
                    ['SET', $key, $value],
                    $arTtl
                )
            );
            return true;
        } catch (Throwable $exception) {
            return false;
        }

    }

    public function delete(string $key): bool
    {
        try {
            return $this->client->del($this->client->keys($key));
        } catch (Throwable $exception) {
            return false;
        }
    }
    public function clear(): bool
    {
        try {
            $this->client->del($this->client->keys('*'));
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }
    public function has(string $key): bool
    {
        return $this->client->exists($key);
    }
}