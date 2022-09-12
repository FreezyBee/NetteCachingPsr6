<?php

declare(strict_types=1);

namespace FreezyBee\NetteCachingPsr6\Tests\Mock;

use Nette\Caching\Cache;
use Nette\Caching\Storage;

class MockStorage implements Storage
{
    /** @var array<string, mixed> */
    public array $data = [];

    public function read(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function lock(string $key): void
    {
    }

    /**
     * @param array<mixed> $dependencies
     */
    public function write(string $key, mixed $data, array $dependencies): void
    {
        $this->data[$key] = $data;
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * @param array<mixed> $conditions
     */
    public function clean(array $conditions): void
    {
        if (!empty($conditions[Cache::All])) {
            $this->data = [];
        }
    }
}
