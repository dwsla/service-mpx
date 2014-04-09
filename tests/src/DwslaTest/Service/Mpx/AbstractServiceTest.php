<?php

namespace DwslaTest\Service\Mpx;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;

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
        $response = new Response($code, $headers, json_encode($bodyData));

        $plugin = new MockPlugin();
        $plugin->addResponse($response);

        $client = new Client();
        $client->addSubscriber($plugin);

        return $client;
    }

}
