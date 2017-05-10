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
use FreezyBee\NetteCachingPsr6\Tests\Mock\ITestStorage;
use FreezyBee\NetteCachingPsr6\Tests\Mock\TestStorage2;
use FreezyBee\NetteCachingPsr6\Tests\Mock\TestStorage3;
use Nette\Caching\IStorage;
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
    /**
     * @var ITestStorage
     */
    protected $storage;

    /**
     *
     */
    public function setUp()
    {
        // detect version of nette istorage
        $this->storage = (new \ReflectionMethod(IStorage::class, 'read'))->getParameters()[0]->hasType() ?
            new TestStorage3 :
            new TestStorage2;
    }

    /**
     *
     */
    public function testUnsuccessGetItem()
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
        $this->storage->setData($this->getDefaultDataArray());
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key2');
        Assert::true($item->isHit());
        Assert::same('defaultX', $item->get());
        Assert::true($cache->hasItem('key2'));
    }

    /**
     *
     */
    public function testSimpleSave()
    {
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key2');
        $item->set('defaultX');
        $cache->save($item);
        Assert::same($this->getDefaultDataArray(), $this->storage->getData());
    }

    /**
     *
     */
    public function testDestructSave()
    {
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key2');
        $item->set('defaultX');
        $cache->saveDeferred($item);
        unset($cache);
        Assert::equal($this->getDefaultDataArray(), $this->storage->getData());
    }

    /**
     *
     */
    public function testNoSave()
    {
        $cache = new Cache($this->storage);
        $item = $cache->getItem('key2');
        $item->set('defaultX');
        unset($cache);
        Assert::same([], $this->storage->getData());
    }

    /**
     *
     */
    public function testSaveAndGetFromAnotherInstance()
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
    public function testDeleteItem()
    {
        $this->storage->setData($this->getDefaultDataArray());
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
    public function testGetAndDeleteItems()
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
        Assert::equal([], $this->storage->getData());
    }

    public function testInvalidSave()
    {
        $cache = new Cache($this->storage);

        $item = new class implements CacheItemInterface
        {
            public function getKey()
            {
            }

            public function get()
            {
            }

            public function isHit()
            {
            }

            public function set($value)
            {
            }

            public function expiresAt($expiration)
            {
            }

            public function expiresAfter($time)
            {
            }
        };

        Assert::false($cache->save($item));
        Assert::false($cache->saveDeferred($item));
    }

    /**
     *
     */
    public function testException()
    {
        $cache = new Cache($this->storage);
        Assert::exception(function () use ($cache) {
            $cache->getItems([-1]);
        }, InvalidArgumentException::class);

        Assert::exception(function () use ($cache) {
            $cache->getItems([(object) ['hello']]);
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
