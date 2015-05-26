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

use fXmlRpc\Client\Client;
use fXmlRpc\Client\Exception\TransportException;

/**
 * @large
 * @group integration
 * @group node
 *
 * @author Lars Strojny <lstrojny@php.net>
 */
class NodeCallClientIntegrationTest extends AbstractCallClientIntegrationTest
{
    protected static $endpoint = 'http://127.0.0.1:9090/';

    protected static $errorEndpoint = 'http://127.0.0.1:9091/';

    protected static $command = 'exec node server.js';

    /**
     * @dataProvider getClients
     */
    public function testServerNotReachableViaTcpIp(Client $client)
    {
        $client->setUri('http://127.0.0.1:12345/');

        try {
            $client->call('system.failure');
            $this->fail('Exception expected');
        } catch (TransportException $e) {
            $this->assertInstanceOf('fXmlRpc\Client\Exception\TransportException', $e);
            $this->assertInstanceOf('fXmlRpc\Exception', $e);
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertStringStartsWith('Transport error occurred:', $e->getMessage());
            $this->assertSame(0, $e->getCode());
        }
    }

    /**
     * @dataProvider getClients
     */
    public function testServerReturnsInvalidResult(Client $client)
    {
        $this->executeSystemFailureTest($client);
    }
}
