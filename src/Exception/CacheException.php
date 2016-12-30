<?php

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\NetteCachingPsr6\Exception;

/**
 * Class CacheException
 * @package FreezyBee\NetteCachingPsr6
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class CacheException extends \Exception implements \Psr\Cache\CacheException
{
}
