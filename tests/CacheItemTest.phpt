<?php

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\NetteCachingPsr6\Tests;

use FreezyBee\NetteCachingPsr6\CacheItem;
use FreezyBee\NetteCachingPsr6\Exception\InvalidArgumentException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/Mock/TestStorage.php';

/**
 * Class CacheItemTest
 * @testCase
 */
class CacheItemTest extends TestCase
{
    /**
     * @var CacheItem
     */
    protected $item;

    /**
     *
     */
    public function setUp()
    {
        $this->item = new class extends CacheItem
        {
            protected $key = 'key';
            protected $value = 3.14;
            protected $isHit = false;
            protected $expiry = 100;
            protected $defaultLifetime = 111;
        };
    }

    /**
     *
     */
    public function testGetters()
    {
        $item = $this->item;
        Assert::same('key', $item->getKey());
        Assert::same(3.14, $item->get());
        Assert::same(false, $item->isHit());
        Assert::same(100, $item->getExpiry());
    }

    /**
     *
     */
    public function testSetters()
    {
        $item = $this->item;
        $value = (object)['x' => 'y'];
        $item->set($value);
        Assert::same($value, $item->get());

        $now = new \DateTime;

        $item->expiresAt($now);
        Assert::same((int)$now->format('U'), $item->getExpiry());

        $item->expiresAfter(100);
        Assert::same((int)$now->modify('+100 second')->format('U'), $item->getExpiry());
    }

    /**
     *
     */
    public function testExceptions()
    {
        $item = $this->item;
        Assert::exception(function () use ($item) {
            $item->expiresAt(100);
        }, InvalidArgumentException::class);

        Assert::exception(function () use ($item) {
            $item->expiresAfter(new \DateTime);
        }, InvalidArgumentException::class);
    }
}

(new CacheItemTest)->run();
