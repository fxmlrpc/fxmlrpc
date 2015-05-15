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
use fXmlRpc\Client\MulticallBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
class MulticallBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var MulticallBuilder
     */
    private $multicallBuilder;

    public function setUp()
    {
        $this->client = $this->getMock('fXmlRpc\Client');
        $this->multicallBuilder = new MulticallBuilder($this->client);
    }

    public function testRetrievingMulticallResult()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                [
                    [
                        ['methodName' => 'method1', 'params' => ['arg11', 'arg12']],
                        ['methodName' => 'method2', 'params' => ['arg21', 'arg22']],
                    ]
                ]
            )
            ->will($this->returnValue(['return1', 'return2']));

        $result = $this->multicallBuilder
            ->addCall('method1', ['arg11', 'arg12'])
            ->addCall('method2', ['arg21', 'arg22'])
            ->execute();

        $this->assertSame(['return1', 'return2'], $result);
    }

    public function testIndividualSuccessHandlers()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                [
                    [
                        ['methodName' => 'method1', 'params' => ['arg11', 'arg12']],
                        ['methodName' => 'method2', 'params' => ['arg21', 'arg22']],
                        ['methodName' => 'method3', 'params' => ['arg31', 'arg32']],
                    ]
                ]
            )
            ->will($this->returnValue(['return1', 'return2', ['faultCode' => 100]]));

        $handlerResults = [];
        $handler = function ($result) use (&$handlerResults) {
            $handlerResults[] = $result;
        };
        $results = $this->multicallBuilder
            ->addCall('method1', ['arg11', 'arg12'])
            ->addCall('method2', ['arg21', 'arg22'], $handler)
            ->addCall('method3', ['arg31', 'arg32'], $handler)
            ->execute();

        $this->assertSame(['return1', 'return2', ['faultCode' => 100]], $results);
        $this->assertSame(['return2', ['faultCode' => 100]], $handlerResults);
    }

    public function testIndividualErrorHandler()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                [
                    [
                        ['methodName' => 'method1', 'params' => ['arg11', 'arg12']],
                        ['methodName' => 'method2', 'params' => ['arg21', 'arg22']],
                    ]
                ]
            )
            ->will($this->returnValue([['faultCode' => 100], ['faultCode' => 200]]));

        $handlerResults = [];
        $successHandler = function() {
            throw new \Exception('Should not be called');
        };
        $errorHandler = function ($result) use (&$handlerResults) {
            $handlerResults[] = $result;
        };
        $results = $this->multicallBuilder
            ->addCall('method1', ['arg11', 'arg12'], $successHandler, $errorHandler)
            ->addCall('method2', ['arg21', 'arg22'], null, $errorHandler)
            ->execute();

        $this->assertSame([['faultCode' => 100], ['faultCode' => 200]], $results);
        $this->assertSame($results, $handlerResults);
    }

    public function testGlobalSuccessHandler()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                [
                    [
                        ['methodName' => 'method1', 'params' => ['arg11', 'arg12']],
                        ['methodName' => 'method2', 'params' => ['arg21', 'arg22']],
                    ]
                ]
            )
            ->will($this->returnValue(['return1', ['faultCode' => 200]]));

        $individualHandlerResults = [];
        $individualHandler = function ($result) use (&$individualHandlerResults) {
            $individualHandlerResults[] = $result;
        };
        $globalHandlerResults = [];
        $globalHandler = function ($result) use (&$globalHandlerResults) {
            $globalHandlerResults[] = $result;
        };
        $results = $this->multicallBuilder
            ->addCall('method1', ['arg11', 'arg12'], $individualHandler)
            ->addCall('method2', ['arg21', 'arg22'], $individualHandler)
            ->onSuccess($globalHandler)
            ->execute();

        $this->assertSame(['return1', ['faultCode' => 200]], $results);
        $this->assertSame($results, $individualHandlerResults);
        $this->assertSame($results, $globalHandlerResults);
    }

    public function testGlobalErrorHandler()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                [
                    [
                        ['methodName' => 'method1', 'params' => ['arg11', 'arg12']],
                        ['methodName' => 'method2', 'params' => ['arg21', 'arg22']],
                    ]
                ]
            )
            ->will($this->returnValue(['return1', ['faultCode' => 200]]));

        $individualSuccessHandlerResults = [];
        $individualSuccessHandler = function ($result) use (&$individualSuccessHandlerResults) {
            $individualSuccessHandlerResults[] = $result;
        };
        $globalSuccessHandlerResults = [];
        $globalSuccessHandler = function ($result) use (&$globalSuccessHandlerResults) {
            $globalSuccessHandlerResults[] = $result;
        };
        $globalErrorHandlerResults = [];
        $globalErrorHandler = function ($result) use (&$globalErrorHandlerResults) {
            $globalErrorHandlerResults[] = $result;
        };
        $results = $this->multicallBuilder
            ->addCall('method1', ['arg11', 'arg12'], $individualSuccessHandler)
            ->addCall('method2', ['arg21', 'arg22'], $individualSuccessHandler)
            ->onSuccess($globalSuccessHandler)
            ->onError($globalErrorHandler)
            ->execute();

        $this->assertSame(['return1', ['faultCode' => 200]], $results);
        $this->assertSame($results, $individualSuccessHandlerResults);
        $this->assertSame(['return1'], $globalSuccessHandlerResults);
        $this->assertSame([['faultCode' => 200]], $globalErrorHandlerResults);
    }

    public function testInvalidMethodType()
    {
        $this->setExpectedException(
            'fXmlRpc\Client\Exception\InvalidArgumentException',
            'Expected parameter 1 to be of type "string", "object" of type "stdClass" given'
        );

        $this->multicallBuilder->addCall(new \stdClass());
    }
}
