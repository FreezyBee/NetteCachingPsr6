<?php
declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\NetteCachingPsr6\Exception;

use Exception;
use Psr\Cache\CacheException as PsrCacheException;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class CacheException extends Exception implements PsrCacheException
{

}
