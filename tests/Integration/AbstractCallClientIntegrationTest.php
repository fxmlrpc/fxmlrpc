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

use fXmlRpc\CallClient;
use fXmlRpc\Client\Client;
use fXmlRpc\Client\Exception\HttpException;
use fXmlRpc\Exception\FaultException;
use fXmlRpc\Serialization\Value\Base64;

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
abstract class AbstractCallClientIntegrationTest extends AbstractIntegrationTest
{
    /**
     * @dataProvider getClients
     */
    public function testNil(CallClient $client)
    {
        $result = null;
        $this->assertSame($result, $client->call('system.echoNull', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testArray(CallClient $client)
    {
        $result = range(0, 10);
        $this->assertSame($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testStruct(CallClient $client)
    {
        $result = ['FOO' => 'BAR', 'BAZ' => 'BLA'];
        $this->assertEquals($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testString(CallClient $client)
    {
        $result = 'HELLO WORLD <> & ÜÖÄ';
        $this->assertSame($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testBase64(CallClient $client)
    {
        $expected = Base64::serialize('HELLO WORLD');
        $result = $client->call('system.echo', [$expected]);
        $this->assertSame($expected->getEncoded(), $result->getEncoded());
        $this->assertSame($expected->getDecoded(), $result->getDecoded());
    }

    /**
     * @dataProvider getClients
     */
    public function testInteger(CallClient $client)
    {
        $result = 100;
        $this->assertSame($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testNegativeInteger(CallClient $client)
    {
        $result = -100;
        $this->assertSame($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testFloat(CallClient $client)
    {
        $result = 100.12;
        $this->assertSame($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testNegativeFloat(CallClient $client)
    {
        $result = -100.12;
        $this->assertSame($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testDate(CallClient $client)
    {
        $result = new \DateTime('2011-01-12 23:12:10', new \DateTimeZone('UTC'));
        $this->assertEquals($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testComplexStruct(CallClient $client)
    {
        $result = [
            'el1' => ['one', 'two', 'three'],
            'el2' => ['first' => 'one', 'second' => 'two', 'third' => 'three'],
            'el3' => range(1, 100),
            'el4' => [
                new \DateTime('2011-02-03 20:11:15', new \DateTimeZone('UTC')),
                new \DateTime('2011-02-03 20:11:15', new \DateTimeZone('UTC')),
            ],
            'el5' => 'str',
            'el6' => 1234,
            'el7' => -1234,
            'el8' => 1234.12434,
            'el9' => -1234.3245023,
        ];
        $this->assertEquals($result, $client->call('system.echo', [$result]));
    }

    /**
     * @dataProvider getClients
     */
    public function testFault(CallClient $client)
    {
        try {
            $client->call('system.fault');
            $this->fail('Expected exception');
        } catch (FaultException $e) {
            $this->assertContains('ERROR', $e->getMessage());
            $this->assertContains('ERROR', $e->getFaultString());
            $this->assertSame(0, $e->getCode());
            $this->assertSame(123, $e->getFaultCode());
        }
    }

    protected function executeSystemFailureTest(Client $client)
    {
        $client->setUri(static::$errorEndpoint);

        try {
            $client->call('system.failure');
            $this->fail('Exception expected');
        } catch (HttpException $e) {
            $this->assertInstanceOf('fXmlRpc\Client\Exception\TransportException', $e);
            $this->assertInstanceOf('fXmlRpc\Exception', $e);
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertStringStartsWith('An HTTP error occurred', $e->getMessage());
            $this->assertSame(500, $e->getCode());
        }
    }
}
