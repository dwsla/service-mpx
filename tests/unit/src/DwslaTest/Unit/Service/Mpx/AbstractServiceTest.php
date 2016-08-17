<?php

namespace DwslaTest\Unit\Service\Mpx;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

/**
 * An abstract base for MPX Service classes
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
abstract class AbstractServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Create a service with a mocked client
     *
     * @param  int    $code
     * @param  array  $headers
     * @param  array  $bodyData
     * @return Client
     */
    protected function createMockClient($code, array $headers = array(), array $bodyData = array())
    {
        // Create a mock and queue a response.
        $mock = new MockHandler([
            new Response($code, $headers, \GuzzleHttp\json_encode($bodyData)),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return $client;
    }

}
