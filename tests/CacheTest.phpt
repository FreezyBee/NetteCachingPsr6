<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\NetteCachingPsr6\Tests;

use FreezyBee\NetteCachingPsr6\Cache;
use FreezyBee\NetteCachingPsr6\Exception\InvalidArgumentException;
use FreezyBee\NetteCachingPsr6\Tests\Mock\MockStorage;
use Psr\Cache\CacheItemInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * Class CacheTest
 * @testCase
 */
class CacheTest extends TestCase
{
    protected MockStorage $storage;

    /**
     *
     */
    public function setUp(): void
    {
        $this->storage = new MockStorage();
    }

    /**
     *
     */
    public function testUnsuccessGetItem(): void
    {
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key1');
        Assert::false($item->isHit());
    }

    /**
     *
     */
    public function testSuccessGetItem()
    {
        $this->storage->data = $this->getDefaultDataArray();
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key2');
        Assert::true($item->isHit());
        Assert::same('defaultX', $item->get());
        Assert::true($cache->hasItem('key2'));
    }

    /**
     *
     */
    public function testSimpleSave(): void
    {
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key2');
        $item->set('defaultX');
        $cache->save($item);
        Assert::same($this->getDefaultDataArray(), $this->storage->data);
    }

    /**
     *
     */
    public function testDestructSave(): void
    {
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key2');
        $item->set('defaultX');
        $cache->saveDeferred($item);
        unset($cache);
        Assert::equal($this->getDefaultDataArray(), $this->storage->data);
    }

    /**
     *
     */
    public function testNoSave(): void
    {
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key2');
        $item->set('defaultX');
        unset($cache);
        Assert::same([], $this->storage->data);
    }

    /**
     *
     */
    public function testSaveAndGetFromAnotherInstance(): void
    {
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key1');
        $item->set('jumper');
        $cache->save($item);

        $cache2 = new Cache($this->storage);
        $item = $cache2->getItem('key1');
        Assert::true($item->isHit());
        Assert::same('jumper', $item->get());
    }

    /**
     *
     */
    public function testDeleteItem(): void
    {
        $this->storage->data = $this->getDefaultDataArray();
        $cache = new Cache($this->storage);

        $item = $cache->getItem('key2');
        Assert::true($item->isHit());

        $cache->deleteItem('key2');
        $item = $cache->getItem('key2');
        Assert::false($item->isHit());
    }

    /**
     *
     */
    public function testGetAndDeleteItems(): void
    {
        $cache = new Cache($this->storage);

        $data = [
            'key1' => new \stdClass,
            'key2' => [true, 1, 'jj@gmail.com', 3.14]
        ];

        foreach ($data as $key => $value) {
            $item = $cache->getItem($key);
            $item->set($value);
            $cache->save($item);
        }

        unset($cache);

        // init new cache with same data
        $cache2 = new Cache($this->storage);
        $items = $cache2->getItems(['key1', 'key2']);

        foreach ($items as $key => $item) {
            Assert::true($item->isHit());
            Assert::same($data[$key], $item->get());
        }

        // delete and get empty item
        $cache2->deleteItems(['key2']);
        $items = $cache2->getItems(['key2']);

        Assert::count(1, $items);
        Assert::false($items['key2']->isHit());

        $cache2->clear();
        Assert::equal([], $this->storage->data);
    }

    public function testInvalidSave(): void
    {
        $cache = new Cache($this->storage);

        $item = new class implements CacheItemInterface
        {
            public function getKey(): string
            {
                return '';
            }

            public function get(): mixed
            {
                return null;
            }

            public function isHit(): bool
            {
                return false;
            }

            public function set(mixed $value): static
            {
                return $this;
            }

            public function expiresAt(?\DateTimeInterface $expiration): static
            {
                return $this;
            }

            public function expiresAfter(\DateInterval|int|null $time): static
            {
                return $this;
            }
        };

        Assert::false($cache->save($item));
        Assert::false($cache->saveDeferred($item));
    }

    /**
     *
     */
    public function testException(): void
    {
        $cache = new Cache($this->storage);
        Assert::exception(function () use ($cache) {
            $cache->getItems(['']);
        }, InvalidArgumentException::class);

        Assert::exception(function () use ($cache) {
            $cache->getItem('');
        }, InvalidArgumentException::class);

        Assert::exception(function () use ($cache) {
            $cache->getItem('xx@zz');
        }, InvalidArgumentException::class);
    }

    /**
     * @return array
     */
    private function getDefaultDataArray(): array
    {
        return [chr(0) . '78f825aaa0103319aaa1a30bf4fe3ada' => 'defaultX'];
    }
}

(new CacheTest)->run();
