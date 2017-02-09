# Greg PHP Cache

[![StyleCI](https://styleci.io/repos/70004563/shield?style=flat)](https://styleci.io/repos/70004563)
[![Build Status](https://travis-ci.org/greg-md/php-cache.svg)](https://travis-ci.org/greg-md/php-cache)
[![Total Downloads](https://poser.pugx.org/greg-md/php-cache/d/total.svg)](https://packagist.org/packages/greg-md/php-cache)
[![Latest Stable Version](https://poser.pugx.org/greg-md/php-cache/v/stable.svg)](https://packagist.org/packages/greg-md/php-cache)
[![Latest Unstable Version](https://poser.pugx.org/greg-md/php-cache/v/unstable.svg)](https://packagist.org/packages/greg-md/php-cache)
[![License](https://poser.pugx.org/greg-md/php-cache/license.svg)](https://packagist.org/packages/greg-md/php-cache)

A better cache manager for web artisans.

# Table of contents:

* [Requirements](#requirements)
* [Supported Drivers](#supported-drivers)
* [How It Works](#how-it-works)
* [Cache Strategy](#cache-strategy)

# Requirements

* PHP Version `^7.1`

# Supported Drivers

- **Array** - Use the PHP array as a storage;
- **Tmp** - Use temporary files as a storage. They are automatically removed when script ends;
- **File** - Use file based storage;
- **Memcached** - Use Memcached driver as a storage;
- **Redis** - Use Redis driver as a storage;
- **Sqlite** - Use Sqlite database as a storage.

# How It Works

Where are two ways of working with cache strategies.
Directly or via a cache manager.

> A cache manager could have many cache strategies and a default one.
> The cache manager implements the same cache strategy and could act as default one if it's defined.

In the next example we will use a cache manager.

**First of all**, you have to initialize a cache manager and register some strategies:

```php
$cache = new \Greg\Cache\CacheManager();

// Register file cache
$cache->registerStrategy('container1', new \Greg\Cache\FileCache(__DIR__ . '/storage'));

// Register sqlite cache
$cache->register('container2', function() {
    $pdo = new \PDO('sqlite:' . __DIR__ . '/storage/container2.sqlite');

    return new \Greg\Cache\SqliteCache($pdo);
}, true);

// Register redis cache
$cache->register('container3', function() {
    $redis = new \Redis();

    $redis->connect('127.0.0.1');

    return new \Greg\Cache\RedisCache($redis);
}, true);
```

**Optionally**, you can define a default strategy to be used by the cache manager.

```php
$cache->defaultStrategy('container2');
```

**Then**, you can **set** or **get** some data:

```php
// Add some data in "container1"
$cache->strategy('container1')->set('foo', 'FOO');

// Add some data in default strategy, which is "container2"
$cache->set('bar', 'BAR');

// Get "bar" value from default strategy.
$value = $cache->get('bar'); // result: BAR
```

# Cache Strategy

If you want, you can create your own strategies.
They should implement the `\Greg\Cache\CacheStrategy` interface.

Below you can find a list of **supported methods**.

* [has](#has) - Determines whether an item is present in the cache;
* [hasMultiple](#hasmultiple) - Determines whether multiple items are present in the cache;
* [get](#get) - Fetch a value from the cache;
* [getMultiple](#getmultiple) - Obtains multiple cache items by their unique keys;
* [set](#set) - Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time;
* [setMultiple](#setmultiple) - Persists a set of `key => value` pairs in the cache, with an optional TTL;
* [setForever](#setforever) - Persists data in the cache, forever, uniquely referenced by a key;
* [setMultipleForever](#setmultipleforever) - Persists a set of `key => value` pairs in the cache, forever;
* [delete](#delete) - Delete an item from the cache by its unique key;
* [deleteMultiple](#deleteMultiple) - Delete multiple items from the cache by their unique keys;
* [clear](#clear) - Clear the storage;
* [fetch](#fetch) - Fetch a value from the cache and persist it if it is not present in that cache;

## has

Determines whether an item is present in the cache.

```php
has(string $key): bool
```

`$key` - The cache item key.

_Example:_

```php
$strategy->has('foo');
```

## hasMultiple

Determines whether an item is present in the cache.

```php
hasMultiple(array $keys): bool
```

`$keys` - The cache items keys.

_Example:_

```php
$strategy->hasMultiple(['foo', 'bar']);
```

## get

Fetch a value from the cache.

```php
get(string $key, mixed $default = null): mixed
```

`$key` - The unique key of this item in the cache;  
`$default` - Default value to return if the key does not exist.

Return the value of the item from the cache, or `$default` in case of cache miss.

_Example:_

```php
$strategy->get('foo');
```

## getMultiple

Obtains multiple cache items by their unique keys.

```php
getMultiple(array $keys, $default = null): mixed
```

`$keys` - A list of keys that can obtained in a single operation;  
`$default` - Default value to return for keys that do not exist.

Return a list of `key => value` pairs. Cache keys that do not exist or are stale will have `$default` as value.

_Example:_

```php
$strategy->get('foo');
```

## set

Persists data in the cache,
uniquely referenced by a key with an optional expiration TTL time.

```php
set(string $key, $value, ?int $ttl = null): $this
```

`$key` - The key of the item to store;  
`$value` - The value of the item to store, must be serializable;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.  

_Example:_

```php
$strategy->set('foo', 'FOO');
```

## setMultiple

Persists a set of `key => value` pairs in the cache, with an optional TTL.

```php
setMultiple(array $values, ?int $ttl = null): $this
```

`$values` - A list of `key => value` pairs for a multiple-set operation;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.  

_Example:_

```php
$strategy->setMultiple(['foo' => 'FOO', 'bar' => 'BAR']);
```

## setForever

Persists data in the cache, forever, uniquely referenced by a key.

```php
setForever(string $key, $value): $this
```

`$key` - The key of the item to store;  
`$value` - The value of the item to store, must be serializable.

_Example:_

```php
$strategy->setForever('foo', 'FOO');
```

## setMultipleForever

Persists a set of `key => value` pairs in the cache, forever.

```php
setMultipleForever(array $values): $this
```

`$values` - A list of `key => value` pairs for a multiple-set operation.

_Example:_

```php
$strategy->setMultipleForever(['foo' => 'FOO', 'bar' => 'BAR']);
```

## delete

Delete an item from the cache by its unique key.

```php
delete(string $key): $this
```

`$key` - The unique cache key of the item to delete.

_Example:_

```php
$strategy->delete('foo');
```

## deleteMultiple

Delete multiple items from the cache by their unique keys.

```php
deleteMultiple(array $keys): $this
```

`$keys` - The unique cache keys of the items to delete.

_Example:_

```php
$strategy->deleteMultiple(['foo', 'bar']);
```

## clear

Wipes clean the entire cache's keys.

```php
clear(): $this
```

_Example:_

```php
$strategy->clear();
```

## fetch

Fetch a value from the cache and persist it if it is not present in that cache.

```php
fetch(string $key, callable $callable, ?int $ttl = null): mixed
```

`$key` - The unique key of this item in the cache;  
`$callable` - The value callable of the item to store when the key is not present in the cache. The value must be serializable;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.  

_Example:_

```php
$strategy->fetch('foo', function() {
    return 'FOO';
});
```

# License

MIT © [Grigorii Duca](http://greg.md)
