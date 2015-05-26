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
use fXmlRpc\Client\Transport\HttpAdapterTransport;
use fXmlRpc\Serialization\Parser\NativeParser;
use fXmlRpc\Serialization\Parser\XmlReaderParser;
use fXmlRpc\Serialization\Serializer\NativeSerializer;
use fXmlRpc\Serialization\Serializer\XmlWriterSerializer;
use Http\Adapter\Guzzle5HttpAdapter;

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
abstract class AbstractClientBasedIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $disabledExtensions = [];

    /**
     * @var integer
     */
    private $pos = 0;

    /**
     * @var array
     */
    private $dependencyGraph = [];

    /**
     * @var string
     */
    protected static $endpoint;

    public function getClients()
    {
        $clients = [];
        $this->generateAllPossibleCombinations(
            [
                $this->getTransports(),
                $this->getParsers(),
                $this->getSerializers(),
            ],
            $clients
        );

        return $clients;
    }

    private function getParsers()
    {
        return [
            new NativeParser(),
            new XmlReaderParser(),
        ];
    }

    private function getSerializers()
    {
        $serializers = [];

        $serializers[] = new NativeSerializer();


        if ($this->extensionEnabled('nil')) {
            $xmlWriterSerializer = new XmlWriterSerializer();
            $xmlWriterSerializer->enableExtension('nil');
            $serializers[] = $xmlWriterSerializer;
        }

        $xmlWriterNilExtensionDisabled = new XmlWriterSerializer();
        $xmlWriterNilExtensionDisabled->disableExtension('nil');
        $serializers[] = $xmlWriterNilExtensionDisabled;

        return $serializers;
    }

    private function getTransports()
    {
        return [
            new HttpAdapterTransport(new Guzzle5HttpAdapter),
        ];
    }

    private function generateAllPossibleCombinations(array $combinations, array &$clients)
    {
        if ($combinations) {
            for ($i = 0; $i < count($combinations[0]); ++$i) {
                $temp = $combinations;
                $this->dependencyGraph[$this->pos] = $combinations[0][$i];
                array_shift($temp);
                $this->pos++;
                $this->generateAllPossibleCombinations($temp, $clients);
            }
        } else {
            $client = new Client(
                $this->dependencyGraph[0],
                $this->dependencyGraph[1],
                $this->dependencyGraph[2],
                static::$endpoint
            );

            $clients[] = [$client, $this->dependencyGraph[0], $this->dependencyGraph[1], $this->dependencyGraph[2]];
        }
        $this->pos--;
    }

    protected function extensionEnabled($extension)
    {
        return !in_array($extension, $this->disabledExtensions, true);
    }
}
