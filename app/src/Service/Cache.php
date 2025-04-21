<?php

declare(strict_types=1);

namespace s0me0ther\MyStromDashboard\Service;

class Cache
{
    /** @var array<string, mixed> */
    private array $cache = [];

    /**
     * @template T
     *
     * @param class-string<T> $key
     *
     * @return T
     */
    public function get(string $key, callable $createCacheResult): mixed
    {
        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $createCacheResult();
        }

        return $this->cache[$key];
    }
}
