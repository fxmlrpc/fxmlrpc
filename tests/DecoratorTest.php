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

use fXmlRpc\Client\Decorator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class NullDecorator extends Decorator
{
}

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
class DecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $wrapped;

    /**
     * @var Decorator
     */
    private $decorator;

    public function setUp()
    {
        $this->wrapped = $this
            ->getMockBuilder('fXmlRpc\Client')
            ->getMock();

        $this->decorator = new NullDecorator($this->wrapped);
    }

    public function testCallInvokesWrappedInstance()
    {
        $this->wrapped
            ->expects($this->once())
            ->method('call')
            ->with('method', ['arg1', 'arg2'])
            ->will($this->returnValue('response'));

        $this->assertSame('response', $this->decorator->call('method', ['arg1', 'arg2']));
    }

    public function testMulticallMethodWrapped()
    {
        $this->wrapped
            ->expects($this->once())
            ->method('multicall')
            ->will($this->returnValue('m'));

        $this->assertSame('m', $this->decorator->multicall());
    }
}
