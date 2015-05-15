<?php

/*
 * This file is part of the fXmlRpc Client package.
 *
 * (c) Lars Strojny <lstrojny@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fXmlRpc\Client\Tests;

use fXmlRpc\Client\Client;
use fXmlRpc\Serializer\Parser;
use fXmlRpc\Serialization\Serializer;
use fXmlRpc\Client\Transport;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Serializer|MockObject
     */
    private $serializer;

    /**
     * @var Parser|MockObject
     */
    private $parser;

    /**
     * @var Transport|MockObject
     */
    private $transport;

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->serializer = $this->getMock('fXmlRpc\Serialization\Serializer');
        $this->parser = $this->getMock('fXmlRpc\Serialization\Parser');
        $this->transport = $this->getMock('fXmlRpc\Client\Transport');

        $this->client = new Client($this->transport, $this->parser, $this->serializer, 'http://foo.com');
    }

    public function testSettingAndGettingUri()
    {
        $this->assertSame('http://foo.com', $this->client->getUri());
        $this->client->setUri('http://bar.com');
        $this->assertSame('http://bar.com', $this->client->getUri());
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with('methodName', ['p1', 'p2'])
            ->will($this->returnValue('REQUEST'));
        $this->mockTransport('http://bar.com', 'REQUEST', 'RESPONSE');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with('RESPONSE')
            ->will($this->returnValue('NATIVE VALUE'));

        $this->assertSame('NATIVE VALUE', $this->client->call('methodName', ['p1', 'p2']));
    }

    public function testCallSerializer()
    {
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with('methodName', ['p1', 'p2'])
            ->will($this->returnValue('REQUEST'));
        $this->mockTransport('http://foo.com', 'REQUEST', 'RESPONSE');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with('RESPONSE')
            ->will($this->returnValue('NATIVE VALUE'));

        $this->assertSame('NATIVE VALUE', $this->client->call('methodName', ['p1', 'p2']));
    }

    public function testPrependingDetaultParams()
    {
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with('methodName', ['p0', 'p1', 'p2', 'p3'])
            ->will($this->returnValue('REQUEST'));
        $this->mockTransport('http://foo.com', 'REQUEST', 'RESPONSE');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with('RESPONSE')
            ->will($this->returnValue('NATIVE VALUE'));

        $this->assertSame([], $this->client->getPrependParams());
        $this->client->prependParams(['p0', 'p1']);
        $this->assertSame(['p0', 'p1'], $this->client->getPrependParams());

        $this->assertSame('NATIVE VALUE', $this->client->call('methodName', ['p2', 'p3']));
    }

    public function testAppendingParams()
    {
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with('methodName', ['p0', 'p1', 'p2', 'p3'])
            ->will($this->returnValue('REQUEST'));
        $this->mockTransport('http://foo.com', 'REQUEST', 'RESPONSE');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with('RESPONSE')
            ->will($this->returnValue('NATIVE VALUE'));

        $this->assertSame([], $this->client->getAppendParams());
        $this->client->appendParams(['p2', 'p3']);
        $this->assertSame(['p2', 'p3'], $this->client->getAppendParams());

        $this->assertSame('NATIVE VALUE', $this->client->call('methodName', ['p0', 'p1']));
    }

    public function testInvalidMethodName()
    {
        $this->setExpectedException(
            'fXmlRpc\Client\Exception\InvalidArgumentException',
            'Expected parameter 0 to be of type "string", "object" of type "stdClass" given'
        );
        $this->client->call(new \stdClass());
    }

    public function testInvalidUri()
    {
        $this->setExpectedException(
            'fXmlRpc\Client\Exception\InvalidArgumentException',
            'Expected parameter 0 to be of type "string", "object" of type "stdClass" given'
        );
        $this->client->setUri(new \stdClass());
    }

    public function testMulticallFactory()
    {
        $multicall = $this->client->multicall();
        $this->assertInstanceOf('fXmlRpc\MulticallBuilder', $multicall);
        $this->assertNotSame($multicall, $this->client->multicall());
        $this->assertSame($this->client, $multicall->getClient());
    }

    private function mockTransport($endpoint, $request, $response)
    {
        $this->transport
            ->expects($this->once())
            ->method('send')
            ->with($endpoint, $request)
            ->willReturn($response);
    }
}
