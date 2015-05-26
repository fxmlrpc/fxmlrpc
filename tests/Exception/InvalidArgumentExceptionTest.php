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

use fXmlRpc\Client\Exception\InvalidArgumentException;

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
class InvalidArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionImplementsExceptionInterface()
    {
        $this->assertInstanceOf('fXmlRpc\Exception', new InvalidArgumentException());
    }

    public function testInvalidExpectedParameter()
    {
        $e = InvalidArgumentException::expectedParameter(1, 'string', false);

        $this->assertEquals(
            'Expected parameter 1 to be of type "string", "boolean" given',
            $e->getMessage()
        );
    }

    public function testInvalidExpectedObjectParameter()
    {
        $e = InvalidArgumentException::expectedParameter(1, 'string', new \stdClass);

        $this->assertEquals(
            'Expected parameter 1 to be of type "string", "object" of type "stdClass" given',
            $e->getMessage()
        );
    }
}
