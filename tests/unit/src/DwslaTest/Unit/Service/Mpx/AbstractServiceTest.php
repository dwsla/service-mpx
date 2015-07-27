<?php

namespace DwslaTest\Unit\Service\Mpx;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;

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
        $mock = new Mock([
            new Response($code, $headers, Stream::factory(json_encode($bodyData))),
        ]);

        $client = new Client();
        $client->getEmitter()->attach($mock);

        return $client;
    }

}
