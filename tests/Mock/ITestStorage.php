<?php
declare(strict_types=1);

namespace FreezyBee\NetteCachingPsr6\Tests\Mock;

use Nette\Caching\IStorage;

/**
 * Class TestStorage
 */
interface ITestStorage extends IStorage
{
    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @param array $data
     */
    public function setData(array $data);
}
