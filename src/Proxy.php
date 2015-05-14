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
 * @author Lars Strojny <lstrojny@php.net>
 */
class Proxy
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $namespaceSeparator = '.';

    /**
     * @var Proxy[string]
     */
    private $proxies = [];

    /**
     * @param ClientInterface $client
     * @param string          $namespaceSeparator
     * @param string          $namespace
     */
    public function __construct(ClientInterface $client, $namespaceSeparator = '.', $namespace = null)
    {
        $this->client = $client;
        $this->namespaceSeparator = $namespaceSeparator;
        $this->namespace = $namespace;
    }

    /**
     * Invokes remote command
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return $this->client->call($this->prependNamespace($method), $parameters);
    }

    /**
     * Returns namespace specific Proxy instance
     *
     * @param string $namespace
     *
     * @return self
     */
    public function __get($namespace)
    {
        $namespace = $this->prependNamespace($namespace);
        if (!isset($this->proxies[$namespace])) {
            $this->proxies[$namespace] = new static($this->client, $this->namespaceSeparator, $namespace);
        }

        return $this->proxies[$namespace];
    }

    /**
     * Prepends namespace if set
     *
     * @param string $string
     *
     * @return string
     */
    protected function prependNamespace($string)
    {
        if ($this->namespace === null) {
            return $string;
        }

        return $this->namespace . $this->namespaceSeparator . $string;
    }
}
