<?php

namespace Custom\EasyCache\Drivers;

interface DriverInterface
{
    public function get(string $key): ?array;
    public function set(string $key, $value, ?int $ttl): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function has(string $key): bool;
}