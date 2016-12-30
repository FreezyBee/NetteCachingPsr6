<?php

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\NetteCachingPsr6;

use FreezyBee\NetteCachingPsr6\Exception\InvalidArgumentException;
use Nette\Caching\IStorage;
use Nette\Caching\Cache as NCache;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class Cache
 * @package FreezyBee\NetteCachingPsr6
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class Cache implements CacheItemPoolInterface
{
    /** @var NCache */
    protected $nCache;

    /** @var CacheItem[] */
    protected $deferred;

    /** @var \Closure */
    protected $createCacheItem;

    /**
     * @param IStorage $storage
     * @param string $namespace
     */
    public function __construct(IStorage $storage, $namespace = NULL)
    {
        $this->nCache = new NCache($storage, $namespace);

        $this->createCacheItem = \Closure::bind(
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
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        if ($this->deferred) {
            $this->commit();
        }

        self::validateKey($key);

        $value = $this->nCache->load($key);

        $f = $this->createCacheItem;
        return $f($key, $value, $value !== null);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        foreach ($keys as $key) {
            self::validateKey($key);
        }

        $items = [];
        $rawItems = $this->nCache->bulkLoad($keys);
        $f = $this->createCacheItem;

        foreach ($rawItems as $key => $value) {
            $items[$key] = $f($key, $value, $value !== null);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return $this->getItem($key)->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->deferred = [];
        $this->nCache->clean(NCache::ALL);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        return $this->deleteItems([$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            self::validateKey($key);
        }

        foreach ($keys as $key) {
            unset($this->deferred[$key]);
            $this->nCache->remove($key);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        foreach ($this->deferred as $item) {
            $this->nCache->save($item->getKey(), $item->get(), [NCache::EXPIRE => $item->getExpiry()]);
        }

        return true;
    }

    public function __destruct()
    {
        var_dump('__desctruct');
        if ($this->deferred) {
            $this->commit();
        }
    }

    /**
     * Validates a cache key according to PSR-6.
     * @param string $key The key to validate
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
