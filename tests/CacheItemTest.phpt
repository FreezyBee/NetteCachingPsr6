<?php
declare(strict_types=1);

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

/**
 * Class CacheItemTest
 * @testCase
 */
class CacheItemTest extends TestCase
{
    protected CacheItem $item;

    /**
     *
     */
    public function setUp(): void
    {
        $this->item = new class extends CacheItem
        {
            protected string $key = 'key';
            protected mixed $value = 3.14;
            protected bool $isHit = false;
            protected ?int $expiry = 100;
            protected int $defaultLifetime = 111;
        };
    }

    /**
     *
     */
    public function testGetters(): void
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
    public function testSetters(): void
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
}

(new CacheItemTest)->run();
