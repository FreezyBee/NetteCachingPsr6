<?php

use FreezyBee\NetteCachingPsr6\Cache;
use FreezyBee\NetteCachingPsr6\Tests\TestStorage;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/Mock/TestStorage.php';

/**
 * Class CacheTest
 * @testCase
 */
class CacheTest extends TestCase
{
    /**
     * @var TestStorage
     */
    protected $storage;

    /**
     *
     */
    public function setUp()
    {
        $this->storage = new TestStorage;
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
     * @return array
     */
    private function getDefaultDataArray()
    {
        return [chr(0) . '78f825aaa0103319aaa1a30bf4fe3ada' => 'defaultX'];
    }
}

(new CacheTest)->run();
