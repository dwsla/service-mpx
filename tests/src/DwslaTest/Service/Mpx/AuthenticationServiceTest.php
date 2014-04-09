<?php

namespace DwslaTest\Service\Mpx;

use Dwsla\Service\Mpx\AuthenticationService;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;

/**
 * A test of the MPX Authentication class
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
class AuthenticationTest extends AbstractServiceTest
{
    /**
     *
     * @param  int              $code
     * @param  array            $headers
     * @param  array            $bodyData
     * @return MediaFeedService
     */
    protected function createMockService($code, array $headers = array(), array $bodyData = array(), $token = null)
    {
        $plugin = new MockPlugin();

        // signIn response
        $response = new Response(200, $headers, json_encode($bodyData));
        $plugin->addResponse($response);

        // signOut response, auto on __destruct, so need to mock this, too.
        $response = new Response(200, array(), json_encode(array(
            'token' => $token,
        )));
        $plugin->addResponse($response);

        $client = new Client();
        $client->addSubscriber($plugin);

        $service = new AuthenticationService();
        $service->setClient($client);

        return $service;
    }

    /**
     * Test signIn() method with valid credentials
     */
    public function testSignInWithValidCredentials()
    {
        $token = 'my-token';

        $service = $this->createMockService(200, array(), array(
            'signInResponse' => array(
                'token' => $token,
            ),
        ), $token);

        $this->assertTrue($service->signIn('valid-user', 'valid-pass'));
        $this->assertEquals($token, $service->getToken());
    }

    /**
     * Test signIn() method with invalid credentials
     */
    public function testSignInWithInvalidCredentials()
    {
        $token = 'my-token';

        $service = $this->createMockService(401, array(), array(
            'missingSignInResponse' => array(
        )), $token);

        $this->assertFalse($service->signIn('invalid-user', 'invalid-pass'));
    }

}
