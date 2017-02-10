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
* [License](#license)
* [Huuuge Quote](#huuuge-quote)

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

// Register a file cache
$cache->registerStrategy('store1', new \Greg\Cache\FileCache(__DIR__ . '/storage'));

// Register a sqlite cache
$cache->register('store2', function() {
    $pdo = new \PDO('sqlite:' . __DIR__ . '/storage/store2.sqlite');

    return new \Greg\Cache\SqliteCache($pdo);
});

// Register a redis cache
$cache->register('store3', function() {
    $redis = new \Redis();

    $redis->connect('127.0.0.1');

    return new \Greg\Cache\RedisCache($redis);
});
```

**Optionally**, you can define a default store to be used by the cache manager.

```php
$cache->setDefaultStoreName('store2');
```

**Then**, you can **set** or **get** some data:

```php
// Add some data in "store1"
$cache->store('store1')->set('foo', 'FOO');

// Add some data in default store, which is "store2"
$cache->set('bar', 'BAR');

// Get "bar" value from default store.
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
* [forever](#forever) - Persists forever data in the cache, uniquely referenced by a key;
* [foreverMultiple](#forevermultiple) - Persists forever a set of `key => value` pairs in the cache;
* [delete](#delete) - Delete an item from the cache by its unique key;
* [deleteMultiple](#deletemultiple) - Delete multiple items from the cache by their unique keys;
* [clear](#clear) - Clear the storage;
* [remember](#remember) - Sometimes you may wish to retrieve an item from the cache, but also store a default value if the requested item doesn't exist;
* [increment](#increment) - Increment a value;
* [decrement](#decrement) - Decrement a value;
* [incrementFloat](#incrementfloat) - Increment a float value;
* [decrementFloat](#decrementfloat) - Decrement a float value;
* [touch](#touch) - Set a new expiration on an item;
* [pull](#pull) - Retrieve and delete an item from the cache;
* [add](#add) - Persists data in the cache if it's not present.

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

## forever

Persists forever data in the cache, uniquely referenced by a key.

```php
forever(string $key, $value): $this
```

`$key` - The key of the item to store;  
`$value` - The value of the item to store, must be serializable.

_Example:_

```php
$strategy->forever('foo', 'FOO');

// or

$strategy->set('foo', 'FOO', 0);
```

## foreverMultiple

Persists forever a set of `key => value` pairs in the cache.

```php
foreverMultiple(array $values): $this
```

`$values` - A list of `key => value` pairs for a multiple-set operation.

_Example:_

```php
$strategy->foreverMultiple(['foo' => 'FOO', 'bar' => 'BAR']);

// or

$strategy->setMultiple(['foo' => 'FOO', 'bar' => 'BAR'], 0);
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

## remember

Sometimes you may wish to retrieve an item from the cache, but also store a default value if the requested item doesn't exist.

```php
remember(string $key, callable $callable, ?int $ttl = null): mixed
```

`$key` - The unique key of this item in the cache;  
`$callable` - The value callable of the item to store when the key is not present in the cache. The value must be serializable;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.

_Example:_

```php
$strategy->remember('foo', function() {
    return 'FOO';
});
```

## increment

Increment a value.

```php
increment(string $key, int $amount = 1, ?int $ttl = null): $this
```

`$key` - The unique key of this item in the cache;  
`$abount` - The amount to increment;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.

_Example:_

```php
$strategy->increment('foo');

$strategy->increment('foo', 10);
```

## decrement

Decrement a value.

```php
decrement(string $key, int $amount = 1, ?int $ttl = null): $this
```

`$key` - The unique key of this item in the cache;  
`$abount` - The amount to decrement;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.

_Example:_

```php
$strategy->decrement('foo');

$strategy->decrement('foo', 10);
```

## incrementFloat

Increment a float value.

```php
incrementFloat(string $key, float $amount = 1.0, ?int $ttl = null): $this
```

`$key` - The unique key of this item in the cache;  
`$abount` - The amount to increment;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.

_Example:_

```php
$strategy->incrementFloat('foo');

$strategy->incrementFloat('foo', 1.5);
```

## decrementFloat

Decrement a float value.

```php
decrementFloat(string $key, float $amount = 1.0, ?int $ttl = null): $this
```

`$key` - The unique key of this item in the cache;  
`$abount` - The amount to decrement;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.

_Example:_

```php
$strategy->decrementFloat('foo');

$strategy->decrementFloat('foo', 1.5);
```

## touch

Set a new expiration on an item.

```php
touch(string $key, ?int $ttl = null): $this
```

`$key` - The unique key of this item in the cache;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.

_Example:_

```php
$strategy->touch('foo', 100);
```

## pull

Retrieve and delete an item from the cache.

```php
pull(string $key, $default = null): mixed
```

`$key` - The unique key of this item in the cache;  
`$default` - Default value to return for keys that do not exist.

Return the value of the item from the cache, or `$default` in case of cache miss.

_Example:_

```php
$strategy->pull('foo'); // return foo value

$strategy->pull('foo'); // return null
```

## add

Persists data in the cache if it's not present.

```php
add(string $key, $value, ?int $ttl = null): $this
```

`$key` - The key of the item to store;  
`$value` - The value of the item to store, must be serializable;  
`$ttl` - Optional. The TTL value of this item. If no value is sent and
            the driver supports TTL then the library may set a default value
            for it or let the driver take care of that.  

Return `true` if the item is actually added to the cache. Otherwise, return `false`.

_Example:_

```php
$strategy->add('foo', 'FOO'); // return true

$strategy->add('foo', 'FOO2'); // return false
```

# License

MIT © [Grigorii Duca](http://greg.md)

# Huuuge Quote

![I fear not the man who has practiced 10,000 programming languages once, but I fear the man who has practiced one programming language 10,000 times. &copy; #horrorsquad](http://greg.md/huuuge-quote-fb.jpg)
