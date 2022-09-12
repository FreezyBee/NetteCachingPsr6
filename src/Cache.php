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
use Nette\Caching\Cache as NetteCache;
use Nette\Caching\Storage;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class Cache implements CacheItemPoolInterface
{
    /** @var CacheItem[] */
    protected array $deferred = [];
    protected NetteCache $netteCache;
    protected Closure $createCacheItem;

    public function __construct(Storage $storage, string $namespace = null)
    {
        $this->netteCache = new NetteCache($storage, $namespace);

        $this->createCacheItem = Closure::bind(
            function (string $key, $value, bool $isHit) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;

                return $item;
            },
            null,
            CacheItem::class
        );
    }

    public function getItem(string $key): CacheItem
    {
        self::validateKey($key);

        $value = $this->netteCache->load($key);

        $f = $this->createCacheItem;

        return $f($key, $value, $value !== null);
    }

    /**
     * @inheritdoc
     * @return iterable<mixed>
     */
    public function getItems(array $keys = []): iterable
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

    public function hasItem(string $key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function clear(): bool
    {
        $this->deferred = [];
        $this->netteCache->clean([NetteCache::ALL => true]);
        return true;
    }

    public function deleteItem(string $key): bool
    {
        return $this->deleteItems([$key]);
    }

    /**
     * @inheritdoc
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

    public function save(CacheItemInterface $item): bool
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return $this->commit();
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        if (!$item instanceof CacheItem) {
            return false;
        }
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferred as $item) {
            $this->netteCache->save($item->getKey(), $item->get(), [NetteCache::EXPIRE => $item->getExpiry()]);
        }

        return true;
    }

    public function __destruct()
    {
        if ($this->deferred) {
            $this->commit();
        }
    }

    public static function validateKey(string $key): void
    {
        if (!isset($key[0])) {
            throw new InvalidArgumentException('Cache key length must be greater than zero');
        }
        if (isset($key[strcspn($key, '{}()/\@:')])) {
            throw new InvalidArgumentException(sprintf('Cache key "%s" contains reserved characters {}()/\@:', $key));
        }
    }
}
