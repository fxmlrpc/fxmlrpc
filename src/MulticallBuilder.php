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
use fXmlRpc\Exception\InvalidArgumentException;
use fXmlRpc\MulticallBuilder as MulticallBuilderInterface;

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
final class MulticallBuilder implements MulticallBuilderInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var integer
     */
    private $index = 0;

    /**
     * @var array
     */
    private $calls = [];

    /**
     * @var array
     */
    private $handlers = [];

    /**
     * @var callable
     */
    private $onSuccess;

    /**
     * @var callable
     */
    private $onError;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function addCall($methodName, array $params = [], callable $onSuccess = null, callable $onError = null)
    {
        if (!is_string($methodName)) {
            throw InvalidArgumentException::expectedParameter(1, 'string', $methodName);
        }

        $this->calls[$this->index] = compact('methodName', 'params');
        $this->handlers[$this->index] = compact('onSuccess', 'onError');
        ++$this->index;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onSuccess(callable $onSuccess)
    {
        $this->onSuccess = $onSuccess;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onError(callable $onError)
    {
        $this->onError = $onError;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $results = $this->client->call('system.multicall', [$this->calls]);

        foreach ($results as $index => $result) {
            $this->processResult($this->handlers[$index], $result);
        }

        return $results;
    }

    /**
     * @param array $handler
     * @param mixed $result
     */
    protected function processResult(array $handler, $result)
    {
        $isError = is_array($result) && isset($result['faultCode']);

        $this->invokeHandler($handler['onSuccess'], $handler['onError'], $isError, $result);
        $this->invokeHandler($this->onSuccess, $this->onError, $isError, $result);
    }

    /**
     * @param callable|null $onSuccess
     * @param callable|null $onError
     * @param boolean       $isError
     * @param mixed         $result
     */
    protected function invokeHandler($onSuccess, $onError, $isError, $result)
    {
        if ($isError && $onError !== null) {
            call_user_func($onError, $result);
        } elseif ($onSuccess !== null) {
            call_user_func($onSuccess, $result);
        }
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}
