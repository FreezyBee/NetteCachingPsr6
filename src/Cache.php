<?php

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

declare(strict_types=1);

namespace FreezyBee\NetteCachingPsr6;

use Closure;
use FreezyBee\NetteCachingPsr6\Exception\InvalidArgumentException;
use Nette\Caching\IStorage;
use Nette\Caching\Cache as NetteCache;
use Nette\InvalidArgumentException as NetteArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class Cache implements CacheItemPoolInterface
{
    /** @var NetteCache */
    protected $netteCache;

    /** @var CacheItem[] */
    protected $deferred;

    /** @var Closure */
    protected $createCacheItem;

    /**
     * @param IStorage $storage
     *
     * @param string $namespace
     */
    public function __construct(IStorage $storage, string $namespace = null)
    {
        $this->netteCache = new NetteCache($storage, $namespace);

        $this->createCacheItem = Closure::bind(
            function (string $key, $value, bool $isHit) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;
                $item->defaultLifetime = 3600;

                return $item;
            },
            null,
            CacheItem::class
        );
    }

    /**
     * @param string $key
     *
     * @return CacheItem
     *
     * @throws InvalidArgumentException
     */
    public function getItem($key)
    {
        self::validateKey($key);

        $value = $this->netteCache->load($key);

        $f = $this->createCacheItem;

        return $f($key, $value, $value !== null);
    }

    /**
     * @param string[] $keys
     *
     * @return CacheItem[]|array|\Traversable
     *
     * @throws InvalidArgumentException
     */
    public function getItems(array $keys = [])
    {
        foreach ($keys as $key) {
            self::validateKey($key);
        }

        $items = [];
        $rawItems = $this->netteCache->bulkLoad($keys);
        $f = $this->createCacheItem;

        foreach ($rawItems as $key => $value) {
            $items[$key] = $f($key, $value, $value !== null);
        }

        return $items;
    }

    /**
     * @param string $key
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function hasItem($key): bool
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $this->deferred = [];
        $this->netteCache->clean([NetteCache::ALL]);
        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function deleteItem($key)
    {
        return $this->deleteItems([$key]);
    }

    /**
     * @param string[] $keys
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            self::validateKey($key);
        }

        foreach ($keys as $key) {
            unset($this->deferred[$key]);
            $this->netteCache->remove($key);
        }

        return true;
    }

    /**
     * @param CacheItem|CacheItemInterface $item
     *
     * @return bool
     *
     * @throws NetteArgumentException
     */
    public function save(CacheItemInterface $item): bool
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return $this->commit();
    }

    /**
     * @param CacheItemInterface|CacheItem $item
     *
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    /**
     * @return bool
     *
     * @throws NetteArgumentException
     */
    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->netteCache->save($item->getKey(), $item->get(), [NetteCache::EXPIRE => $item->getExpiry()]);
        }

        return true;
    }

    /**
     * @throws NetteArgumentException
     */
    public function __destruct()
    {
        if ($this->deferred) {
            $this->commit();
        }
    }

    /**
     * Validates a cache key according to PSR-6.
     *
     * @param mixed $key The key to validate
     *
     * @throws InvalidArgumentException When $key is not valid.
     */
    public static function validateKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(sprintf(
                'Cache key must be string, "%s" given',
                is_object($key) ? get_class($key) : gettype($key)
            ));
        }
        if (!isset($key[0])) {
            throw new InvalidArgumentException('Cache key length must be greater than zero');
        }
        if (isset($key[strcspn($key, '{}()/\@:')])) {
            throw new InvalidArgumentException(sprintf('Cache key "%s" contains reserved characters {}()/\@:', $key));
        }
    }
}
