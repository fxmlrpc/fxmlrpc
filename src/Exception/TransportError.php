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

/**
 * Should be thrown as a wrapper around Exceptions thrown by transports
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class TransportError extends \RuntimeException implements TransportException
{
    /**
     * Creates from an existing exception
     *
     * @param \Exception $e
     *
     * @return self
     */
    public static function createFromException(\Exception $e)
    {
        return new self('Transport error occured: '.$e->getMessage(), $e->getCode(), $e);
    }
}
