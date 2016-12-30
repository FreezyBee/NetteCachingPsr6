<?php

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\NetteCachingPsr6;

use FreezyBee\NetteCachingPsr6\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class CacheItem implements CacheItemInterface
{
    /** @var string */
    protected $key;

    /** @var mixed */
    protected $value;

    /** @var bool */
    protected $isHit;

    /** @var int */
    protected $expiry;

    /** @var int */
    protected $defaultLifetime;

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        return $this->isHit;
    }

    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        if (null === $expiration) {
            $this->expiry = $this->defaultLifetime > 0 ? time() + $this->defaultLifetime : null;
        } elseif ($expiration instanceof \DateTimeInterface) {
            $this->expiry = (int)$expiration->format('U');
        } else {
            throw new InvalidArgumentException(sprintf(
                'Expiration date must implement DateTimeInterface or be null, "%s" given',
                is_object($expiration) ? get_class($expiration) : gettype($expiration)
            ));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time)
    {
        if (null === $time) {
            $this->expiry = $this->defaultLifetime > 0 ? time() + $this->defaultLifetime : null;
        } elseif ($time instanceof \DateInterval) {
            $this->expiry = (int)\DateTime::createFromFormat('U', time())->add($time)->format('U');
        } elseif (is_int($time)) {
            $this->expiry = $time + time();
        } else {
            throw new InvalidArgumentException(sprintf(
                'Expiration date must be an integer, a DateInterval or null, "%s" given',
                is_object($time) ? get_class($time) : gettype($time)
            ));
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getExpiry()
    {
        return $this->expiry;
    }
}
