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

use fXmlRpc\Client as ClientInterface;

/**
 * Abstract base class for client decorators
 *
 * Extend this base class if you want to decorate functionality of the client
 *
 * @author Lars Strojny <lstrojny@php.net>
 */
abstract class Decorator implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $wrapped;

    /**
     * @param ClientInterface $wrapped
     */
    public function __construct(ClientInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function call($methodName, array $arguments = [])
    {
        return $this->wrapped->call($methodName, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function multicall()
    {
        return $this->wrapped->multicall();
    }
}
