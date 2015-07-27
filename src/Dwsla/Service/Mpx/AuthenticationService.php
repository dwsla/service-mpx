<?php

namespace Dwsla\Service\Mpx;

/**
 * Service for the the MPX Authentication API:
 *
 * * signIn
 * * signOut
 * * getTokenCount
 *
 * @see http://help.theplatform.com/display/wsf2/Authentication+endpoint
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
class AuthenticationService extends AbstractService
{
    /**
     * Auth token from MPX Authentication API
     *
     * @var string
     */
    protected $token;

    /**
     * Base url for this API endpoint
     *
     * @var string
     */
    protected static $baseUrl = 'https://identity.auth.theplatform.com/idm/web/Authentication/';

    /**
     * Schema for this API endpoint
     *
     * @var string
     */
    public $schema = '1.0';

    /**
     * Sign-in, set the auth token
     *
     */
    public function signIn($user, $pass)
    {
        $params = [];
        $params['auth'] = [$user, $pass, 'Basic'];

        $data = $this->doGet('signIn', [], $params);
        
        if (empty($data['signInResponse']['token'])) {
            return false;
        }

        $this->token = $data['signInResponse']['token'];

        return true;
    }

    /**
     * Logout from the API
     */
    public function signOut($token = null)
    {
        if (!$token) {
            $token = $this->token;
        }
        if (!$token) {
            // throw new \RuntimeException('No token provided to sign-out from MPX API');
            return true;
        }

        $body = [
            'signOut' => [
                'token' => $token,
            ],
        ];
        try {
            $data = $this->doPost('signOut', [], json_encode($body));
            $this->token = null;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * The auth token provided by the API
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * On destruct, make sure we are signed out. MPX limits tokens.
     */
    public function __destruct()
    {
        if ($this->token) {
            $this->signOut();
        }
    }
}
