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

/**
 * @large
 * @group integration
 * @group java
 *
 * @author Lars Strojny <lstrojny@php.net>
 */
class JavaCallClientIntegrationTest extends AbstractCallClientIntegrationTest
{
    protected static $endpoint = 'http://127.0.0.1:28080';

    protected static $errorEndpoint = 'http://127.0.0.1:28081/';

    protected static $command = 'exec java -jar server.jar 28080 28081';

    protected $disabledExtensions = ['nil', 'php_curl', 'xmlrpc_header'];
}
