<?php

/*
 * This file is part of the fXmlRpc Client package.
 *
 * (c) Lars Strojny <lstrojny@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fXmlRpc\Client\Tests\Exception;

use fXmlRpc\Client\Exception\FaultException;

/**
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class FaultExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testHasFaultCodeAndString()
    {
        $e = new FaultException(1, 'Fault');

        $this->assertEquals(1, $e->getFaultCode());
        $this->assertEquals('Fault', $e->getFaultString());
        $this->assertEquals('XML RPC fault occured: 1 Fault', $e->getMessage());
    }

    public function testCreateFromResponse()
    {
        $e = FaultException::createFromResponse([
            'faultCode'   => 1,
            'faultString' => 'Fault',
        ]);

        $this->assertEquals(1, $e->getFaultCode());
        $this->assertEquals('Fault', $e->getFaultString());
        $this->assertEquals('XML RPC fault occured: 1 Fault', $e->getMessage());
    }

    public function testCreateFromResponseDefaults()
    {
        $e = FaultException::createFromResponse([]);

        $this->assertEquals(0, $e->getFaultCode());
        $this->assertEquals('Unknown', $e->getFaultString());
        $this->assertEquals('XML RPC fault occured: 0 Unknown', $e->getMessage());
    }
}
