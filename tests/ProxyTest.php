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

use fXmlRpc\Client;
use fXmlRpc\Client\Proxy;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var Proxy
     */
    private $proxy;

    public function setUp()
    {
        $this->client = $this->getMockBuilder('fXmlRpc\Client')
            ->getMock();
        $this->proxy = new Proxy($this->client);
    }

    public function testCallingMethod()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with('method', ['arg1', 'arg2'])
            ->will($this->returnValue('VALUE'));

        $this->assertSame('VALUE', $this->proxy->method('arg1', 'arg2'));
    }

    public function testCallingNamespaceMethod()
    {
        $this->client
            ->expects($this->at(0))
            ->method('call')
            ->with('namespace.method', ['arg1', 'arg2'])
            ->will($this->returnValue('namespace method return'));

        $this->client
            ->expects($this->at(1))
            ->method('call')
            ->with('namespace.another_namespace.method', ['arg1', 'arg2'])
            ->will($this->returnValue('another namespace method return first'));

        $this->client
            ->expects($this->at(2))
            ->method('call')
            ->with('namespace.another_namespace.method', ['arg1', 'arg2'])
            ->will($this->returnValue('another namespace method return second'));

        $this->assertSame('namespace method return', $this->proxy->namespace->method('arg1', 'arg2'));
        $this->assertSame('another namespace method return first', $this->proxy->namespace->another_namespace->method('arg1', 'arg2'));
        $this->assertSame('another namespace method return second', $this->proxy->{"namespace.another_namespace.method"}('arg1', 'arg2'));
    }

    public function testCallingNamespaceMethodWithCustomSeparator()
    {
        $proxy = new Proxy($this->client, '_');
        $this->client
            ->expects($this->at(0))
            ->method('call')
            ->with('namespace_method', [1, 2])
            ->will($this->returnValue('namespace method return'));
        $this->client
            ->expects($this->at(1))
            ->method('call')
            ->with('namespace_another_namespace_method', [1, 2])
            ->will($this->returnValue('another namespace method return'));

        $this->assertSame('namespace method return', $proxy->namespace->method(1, 2));
        $this->assertSame('another namespace method return', $proxy->namespace->another_namespace->method(1, 2));
    }

    public function testLazyLoading()
    {
        $this->assertSame($this->proxy->foo, $this->proxy->foo);
    }
}
