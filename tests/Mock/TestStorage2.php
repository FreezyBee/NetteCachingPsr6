<?php
declare(strict_types=1);

namespace FreezyBee\NetteCachingPsr6\Tests\Mock;

/**
 * Class TestStorage
 */
class TestStorage2 implements ITestStorage
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param $key
     * @return mixed|null
     */
    public function read($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param $key
     * @param $data
     * @param array $dependencies
     */
    public function write($key, $data, array $dependencies)
    {
        $this->data[$key] = $data;
    }

    /**
     * @param $key
     */
    public function lock($key)
    {
    }

    /**
     * @param $key
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * @param array $conditions
     */
    public function clean(array $conditions)
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
