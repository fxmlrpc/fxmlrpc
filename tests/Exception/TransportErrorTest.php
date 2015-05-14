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

use fXmlRpc\Client\Exception\TransportError;

/**
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class TransportErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesFromException()
    {
        $e = new \Exception('Test message', 100);

        $transportError = TransportError::createFromException($e);

        $this->assertEquals('Transport error occured: Test message', $transportError->getMessage());
        $this->assertEquals(100, $transportError->getCode());
        $this->assertSame($e, $transportError->getPrevious());
    }
}
