<?php

/*
 * This file is part of the fXmlRpc Client package.
 *
 * (c) Lars Strojny <lstrojny@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fXmlRpc\Client\Exception;

use fXmlRpc\Exception;

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
final class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
    /**
     * @param integer $position
     * @param string  $expected
     * @param mixed   $actualValue
     *
     * @return self
     */
    public static function expectedParameter($position, $expected, $actualValue)
    {
        return new static(
            sprintf(
                'Expected parameter %d to be of type "%s", "%s" given',
                $position,
                $expected,
                is_object($actualValue)
                    ? sprintf('%s" of type "%s', gettype($actualValue), get_class($actualValue))
                    : gettype($actualValue)
            )
        );
    }
}
