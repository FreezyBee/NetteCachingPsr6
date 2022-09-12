<?php

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

declare(strict_types=1);

namespace FreezyBee\NetteCachingPsr6;

use DateInterval;
use DateTime;
use DateTimeInterface;
use FreezyBee\NetteCachingPsr6\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class CacheItem implements CacheItemInterface
{
    protected string $key = '';
    protected mixed $value = null;
    protected bool $isHit = false;
    protected ?int $expiry = null;
    protected int $defaultLifetime = 3600;

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function expiresAt(?DateTimeInterface $expiration): static
    {
        if ($expiration instanceof DateTimeInterface) {
            $this->expiry = (int) $expiration->format('U');
        } else {
            $this->expiry = $this->defaultLifetime > 0 ? time() + $this->defaultLifetime : null;
        }

        return $this;
    }

    public function expiresAfter(int|DateInterval|null $time): static
    {
        if ($time instanceof DateInterval) {
            $date = DateTime::createFromFormat('U', (string) time());
            if ($date === false) {
                throw new InvalidArgumentException('Invalid time');
            }
            $this->expiry = (int) $date->add($time)->format('U');
        } elseif (is_int($time)) {
            $this->expiry = $time + time();
        } else {
            $this->expiry = $this->defaultLifetime > 0 ? time() + $this->defaultLifetime : null;
        }

        return $this;
    }

    public function getExpiry(): ?int
    {
        return $this->expiry;
    }
}
