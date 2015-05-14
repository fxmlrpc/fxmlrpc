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
use fXmlRpc\Serialization\Parser;
use fXmlRpc\Serialization\Serializer;

/**
 * Main client
 *
 * @author Lars Strojny <lstrojny@php.net>
 */
final class Client implements ClientInterface
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $prependParams = [];

    /**
     * @var array
     */
    private $appendParams = [];

    /**
     * @param string     $uri
     * @param Transport  $transport
     * @param Parser     $parser
     * @param Serializer $serializer
     */
    public function __construct(
        Transport $transport,
        Parser $parser,
        Serializer $serializer,
        $uri = null
    ) {
        $this->transport = $transport;
        $this->parser = $parser;
        $this->serializer = $serializer;
        $this->uri = $uri;
    }

    /**
     * Returns endpoint URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Sets the endpoint URI
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        if (!is_string($uri)) {
            throw InvalidArgumentException::expectedParameter(0, 'string', $uri);
        }

        $this->uri = $uri;
    }

    /**
     * Returns default parameters that are always prepended
     *
     * @return array
     */
    public function getPrependParams()
    {
        return $this->prependParams;
    }

    /**
     * Prepends default parameters that should always be prepended
     *
     * @param array $params
     */
    public function prependParams(array $params)
    {
        $this->prependParams = $params;
    }

    /**
     * Returns default parameters that are always appended
     *
     * @return array
     */
    public function getAppendParams()
    {
        return $this->appendParams;
    }

    /**
     * Appends default parameters that should always be prepended
     *
     * @param array $params
     */
    public function appendParams(array $params)
    {
        $this->appendParams = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function call($methodName, array $params = [])
    {
        if (!is_string($methodName)) {
            throw InvalidArgumentException::expectedParameter(0, 'string', $methodName);
        }

        $params = array_merge($this->prependParams, $params, $this->appendParams);
        $payload = $this->serializer->serialize($methodName, $params);
        $response = $this->transport->send($this->uri, $payload);
        $result = $this->parser->parse($response, $isFault);

        if ($isFault) {
            throw Exception\FaultException::createFromResponse($result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function multicall()
    {
        return new MulticallBuilder($this);
    }
}
