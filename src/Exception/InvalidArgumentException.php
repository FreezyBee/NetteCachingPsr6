<?php

declare(strict_types=1);

/*
 * This file is part of the some package.
 * (c) Jakub Janata <jakubjanata@gmail.com>
 * For the full copyright and license information, please view the LICENSE file.
 */

namespace FreezyBee\NetteCachingPsr6\Exception;

use Psr\Cache\InvalidArgumentException as PsrCacheArgumentException;

/**
 * @author Jakub Janata <jakubjanata@gmail.com>
 */
class InvalidArgumentException extends CacheException implements PsrCacheArgumentException
{
}
