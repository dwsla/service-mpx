<?php

namespace DwslaTest\Unit\Service\Mpx;

use Dwsla\Service\Mpx\AuthenticationService;
use DwslaTest\Unit\Service\Mpx\AbstractServiceTest as Base;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;

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
     * @return MediaFeedService
     */
    protected function createMockService($code, array $headers = [], array $bodyData = [], $token = null)
    {
        // signIn response
        $signInResponse = new Response($code, $headers, Stream::factory(json_encode($bodyData)));

        // signOut response, auto on __destruct, so need to mock this, too.
        $signOutResponse = new Response(200, [], Stream::factory(json_encode([
            'token' => $token,
        ])));
        
        $mock = new Mock([
            $signInResponse,
            $signOutResponse,
        ]);

        $client = new Client();
        $client->getEmitter()->attach($mock);

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
