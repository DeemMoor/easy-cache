<?php

namespace Custom\EasyCache\Drivers;

class FileCacheDriver implements DriverInterface
{
    private string $storagePath;

    public function __construct(string $storagePath = '/tmp/easy_cache') {
        $this->storagePath = rtrim($storagePath, '/');
        if (!file_exists($this->storagePath)) {
            if (!mkdir($concurrentDirectory = $this->storagePath, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
    }

    private function getPath(string $key): string {
        return $this->storagePath . '/' . md5($key);
    }

    public function get(string $key): ?array {
        $file = $this->getPath($key);
        if (!file_exists($file)) return null;

        $data = unserialize(file_get_contents($file), ['allowed_classes' => false]);
        if ($data['expire'] !== null && $data['expire'] < time()) {
            unlink($file);
            return null;
        }
        return $data;
    }

    public function set(string $key, $value, ?int $ttl): bool {
        $data = [
            'value' => $value,
            'expire' => $ttl ? time() + $ttl : null
        ];
        return (bool) file_put_contents($this->getPath($key), serialize($data));
    }

    public function delete(string $key): bool
    {
        $file = $this->getPath($key);
        if (!file_exists($file)) return false;
        return unlink($file);
    }

    public function clear(): bool
    {
        return (bool) array_map('unlink', glob($this->storagePath . '/*'));
    }

    public function has(string $key): bool
    {
        return file_exists($this->getPath($key));
    }
}