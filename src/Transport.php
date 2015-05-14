<?php

/*
 * This file is part of the fXmlRpc Client package.
 *
 * (c) Lars Strojny <lstrojny@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fXmlRpc\Client;

use fXmlRpc\Exception\AbstractTransportException;

/**
 * This interface is implemented by transports to provide means to send
 * requests over the wire.
 *
 * @author Lars Strojny <lstrojny@php.net>
 */
interface Transport
{
    /**
     * Sends XML/RPC request over the wire and return the payload
     *
     * @param  string $endpoint
     * @param  string $payload
     *
     * @return string
     *
     * @throws AbstractTransportException If a transport error occurred
     */
    public function send($endpoint, $payload);
}
