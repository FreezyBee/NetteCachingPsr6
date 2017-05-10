<?php
declare(strict_types=1);

namespace FreezyBee\NetteCachingPsr6\Tests\Mock;

/**
 * Class TestStorage
 */
class TestStorage3 implements ITestStorage
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param $key
     * @return mixed|null
     */
    public function read(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $key
     * @param $data
     * @param array $dependencies
     */
    public function write(string $key, $data, array $dependencies): void
    {
        $this->data[$key] = $data;
    }

    /**
     * @param $key
     */
    public function lock(string $key): void
    {
    }

    /**
     * @param $key
     */
    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * @param array $conditions
     */
    public function clean(array $conditions): void
    {
        $this->data = [];
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}
