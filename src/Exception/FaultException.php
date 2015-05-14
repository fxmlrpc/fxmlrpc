<?php

/*
 * This file is part of the fXmlRpc Client package.
 *
 * (c) Lars Strojny <lstrojny@php.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace fXmlRpc\Client\Exception;

use fXmlRpc\Exception;

/**
 * Thrown in case of an XML RPC Fault
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class FaultException extends \RuntimeException implements Exception
{
    /**
     * @var integer
     */
    private $faultCode;

    /**
     * @var string
     */
    private $faultString;

    /**
     * @param integer $faultCode
     * @param string  $faultString
     */
    public function __construct($faultCode, $faultString)
    {
        $this->faultCode = $faultCode;
        $this->faultString = $faultString;

        $this->message = sprintf('XML RPC fault occured: %d %s', $faultCode, $faultString);
    }

    /**
     * Returns the fault code
     *
     * @return integer
     */
    public function getFaultCode()
    {
        return $this->faultCode;
    }

    /**
     * Returns the fault string
     *
     * @return string
     */
    public function getFaultString()
    {
        return $this->faultString;
    }

    /**
     * @param array $response
     *
     * @return self
     */
    public static function createFromResponse(array $response)
    {
        $faultCode = isset($response['faultCode']) ? $response['faultCode'] : 0;
        $faultString = isset($response['faultString']) ? $response['faultString'] : 'Unknown';

        return new self($faultCode, $faultString);
    }
}
