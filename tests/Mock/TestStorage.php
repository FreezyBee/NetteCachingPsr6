<?php

namespace FreezyBee\NetteCachingPsr6\Tests;

use Nette\Caching\IStorage;

/**
 * Class TestStorage
 */
class TestStorage implements IStorage
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
        return isset($this->data[$key]) ? $this->data[$key] : null;
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
    public function getData()
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
