<?php

namespace DwslaTest\Unit\Service\Mpx;

use Dwsla\Service\Mpx\AuthenticationService;
use DwslaTest\Unit\Service\Mpx\AbstractServiceTest as Base;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;

/**
 * A test of the MPX Authentication class
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
class AuthenticationTest extends Base
{
    /**
     *
     * @param  int              $code
     * @param  array            $headers
     * @param  array            $bodyData
     * @return AuthenticationService
     */
    protected function createMockService($code, array $headers = [], array $bodyData = [], $token = null)
    {

        // Create a mock and queue a response.
        $mock = new MockHandler([
            new Response($code, $headers, \GuzzleHttp\json_encode($bodyData)),
            new Response(200, [], \GuzzleHttp\json_encode([
                'token' => $token,
            ])),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client([

            // appears to be needed
            'base_uri' => 'http://example.com',


            'handler' => $handler,
        ]);

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

        $service = $this->createMockService(200, [], [
            'signInResponse' => [
                'token' => $token,
            ],
        ], $token);

        $this->assertTrue($service->signIn('valid-user', 'valid-pass'));
        $this->assertEquals($token, $service->getToken());
    }

    /**
     * Test signIn() method with invalid credentials
     */
    public function testSignInWithInvalidCredentials()
    {
        $token = 'my-token';

        $service = $this->createMockService(200, [], [
            'missingSignInResponse' => [
        ]], $token);

        $this->assertFalse($service->signIn('invalid-user', 'invalid-pass'));
    }

}
