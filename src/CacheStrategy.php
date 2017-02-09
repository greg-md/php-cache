<?php

declare(strict_types=1);

namespace Greg\Cache;

interface CacheStrategy
{
    public function has(string $key): bool;

    public function hasMultiple(array $keys): bool;

    public function get(string $key, $default = null);

    public function getMultiple(array $keys, $default = null);

    public function set(string $key, $value, ?int $ttl = null);

    public function setMultiple(array $values, ?int $ttl = null);

    public function setForever(string $key, $value);

    public function setMultipleForever(array $values);

    public function delete(string $key);

    public function deleteMultiple(array $keys);

    public function clear();

    public function remember(string $key, callable $callable, ?int $ttl = null);

    public function increment(string $key, int $amount = 1, ?int $ttl = null);

    public function decrement(string $key, int $amount = 1, ?int $ttl = null);

    public function incrementFloat(string $key, float $amount = 1.0, ?int $ttl = null);

    public function decrementFloat(string $key, float $amount = 1.0, ?int $ttl = null);

    public function touch($key, ?int $ttl = null);
}
