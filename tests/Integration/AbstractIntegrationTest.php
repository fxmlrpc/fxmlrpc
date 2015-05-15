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

use hmmmath\Fibonacci\FibonacciFactory;
use Symfony\Component\Process\Process;

/**
 * @author Lars Strojny <lstrojny@php.net>
 */
abstract class AbstractIntegrationTest extends AbstractClientBasedIntegrationTest
{
    /**
     * @var boolean
     */
    protected static $enabled = true;

    /**
     * @var command
     */
    protected static $command;

    /**
     * @var Process
     */
    protected static $server;

    /**
     * @var string
     */
    protected static $errorEndpoint;

    /**
     * @var integer
     */
    protected static $restartThreshold = 100;

    /**
     * @var integer
     */
    private static $runCount = 0;

    protected static function startServer()
    {
        if (!static::$enabled) {
            return;
        }

        self::$server = new Process(static::$command . ' &>/dev/null', __DIR__ . '/Fixtures');
        self::$server->start();
        static::pollWait();
    }

    protected static function stopServer()
    {
        if (!static::$enabled) {
            return;
        }

        self::$server->stop();
    }

    public static function setUpBeforeClass()
    {
        static::startServer();
    }

    private static function pollWait()
    {
        $parts = parse_url(static::$endpoint);
        foreach (FibonacciFactory::sequence(50000, 10000, 10) as $offset => $sleepTime) {
            usleep($sleepTime);

            $socket = @fsockopen($parts['host'], $parts['port'], $errorNumber, $errorString, 1);
            if ($socket !== false) {
                fclose($socket);
                return;
            }
        }
    }

    public static function tearDownAfterClass()
    {
        static::stopServer();
    }

    public function setUp()
    {
        if (static::$restartThreshold > 0 && ++self::$runCount !== static::$restartThreshold) {
            return;
        }

        self::$runCount = 0;
        static::stopServer();
        static::startServer();
        static::pollWait();
    }
}
