<?php

/*
 * This file is part of the fXmlRpc Client package.
 *
 * (c) Lars Strojny <lstrojny@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fXmlRpc\Client\Tests\Integration;

use fXmlRpc\MulticallClient;

/**
 * @large
 * @group integration
 * @group python
 *
 * @author Lars Strojny <lstrojny@php.net>
 */
class MulticallBuilderIntegrationBasedIntegrationTest extends AbstractIntegrationTest
{
    protected static $enabled = false;

    protected static $endpoint = 'http://127.0.0.1:28000';

    protected static $errorEndpoint = 'http://127.0.0.1:28001';

    protected static $command = 'exec python server.py';

    /**
     * @var mixed
     */
    private $expected;

    /**
     * @var integer
     */
    private $handlerInvoked = 0;

    public function setUp()
    {
        $this->markTestSkipped('Multicall integration tests need to be ported to node.js');
    }

    /** @dataProvider getClients */
    public function testMulticallWithError(MulticallClient $client)
    {
        $this->handlerInvoked = 0;
        $this->expected = [
            [
                'faultCode'   => 1,
                'faultString' => '<type \'exceptions.Exception\'>:method "invalidMethod" is not supported'
            ]
        ];

        $result = $client->multicall()
            ->addCall('invalidMethod')
            ->onError([$this, 'handler'])
            ->execute();

        $this->assertSame(1, $this->handlerInvoked);
        $this->assertSame($this->expected, $result);
    }

    /** @dataProvider getClients */
    public function testSimpleMulticall(MulticallClient $client)
    {
        $this->handlerInvoked = 0;
        $this->expected = [
            [0],
            [1],
            [2],
            [3],
            [4],
        ];

        $result = $client->multicall()
            ->addCall('system.echo', [0])
            ->addCall('system.echo', [1])
            ->addCall('system.echo', [2])
            ->addCall('system.echo', [3])
            ->addCall('system.echo', [4])
            ->onSuccess([$this, 'handler'])
            ->execute();

        $this->assertSame($this->expected, $result);
        $this->assertSame(5, $this->handlerInvoked);
    }

    public function handler($result)
    {
        $this->handlerInvoked++;
        $this->assertSame(current($this->expected), $result);
        next($this->expected);
    }
}
