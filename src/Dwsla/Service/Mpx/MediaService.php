<?php

namespace Dwsla\Service\Mpx;

use Psr\Http\Message\ResponseInterface;

/**
 * A service for the MPX Media API
 */
class MediaService extends AbstractService
{
    /**
     * Schema for this endpoint
     *
     * @var string
     */
    public $schema = '2.0.0';

    /**
     * @var string
     */
    protected $token;

    /**
     * Base url for this endpoint
     *
     * @var string
     */
    protected static $baseUrl = 'http://data.media.theplatform.com/media/data/Media/feed';

    /**
     * Auth token from MPX
     * 
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Make an HTTP PUT request, returns a $response (!) object
     *
     * This allows implementation details to leak out, but at present the MPX
     * Media service seems to send 200 even when the request fails. So, clients
     * will want the response object so that it can implement its own checks
     * for validity and to retrieve errors.
     *
     * @param  string $relativeEndpoint
     * @param  array $headers
     * @param string $body
     * @param  array $params
     * @return ResponseInterface
     */
    protected function doPut($relativeEndpoint, $headers = [], $body = '', $params = [])
    {
        $response = $this->getClient()->put($relativeEndpoint, [
            'headers' => $headers,
            'query' => array_merge([
                'form' => $this->getFormat(),
                'schema' => $this->getSchema(),
            ], $params['query']),
            'body' => $body,
        ]);
        return $response;
    }
        
    /**
     * Do a plural PUT. Returns a response object.
     * 
     * This allows implementation details to leak out, but at present the MPX 
     * Media service seems to send 200 even when the request fails. So, clients
     * will want the response object so that it can implement its own checks
     * for validity and to retrieve errors.
     *
     * @param array $urlParams
     * @param array $body
     * @return ResponseInterface $response
     */
    public function putPluralJson(array $urlParams, array $body)
    {
        $headers = [];
        $headers['Content-type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        
        $options = [];
        
        // For Content-Type application/json, MPX will suppress httpErrors, always returning 
        // 200, by default. To re-enable HTTP errors, use ?httpError=true
        // @see http://help.theplatform.com/display/wsf2/Handling+data-service+exceptions#Handlingdata-serviceexceptions-SuppressingHTTPerrorcodes
        $options['query'] = array_merge([
            'httpError' => 'true',
            'token' => $this->token,
        ], $urlParams);
        return $this->doPut('', $headers, json_encode($body), $options);
    }
    
    /**
     * Utility function to check a Guzzle response from MPX Media API. 
     * 
     * Implementation leakage. 
     * 
     * Even though the putPluralJson() method above enables httpErrors, it is 
     * actually more robust to check the response body, as we do below.
     * 
     * @param ResponseInterface $response
     * @return boolean
     */
    public static function isResponseSuccessful(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        if (!empty($body['isException'])) {
            return false;
        }
        $responseCode = !empty($body['responseCode']) ? $body['responseCode'] : 200;
        return ($responseCode >= 200 && $responseCode < 300) || $responseCode == 304;
    }

    /**
     * Utility function to get an error message from a failed Guzzle response from
     * MPX Media API.
     * 
     * Implementation leakage.
     * 
     * @param ResponseInterface $response
     * @return string
     */
    public static function getResponseError(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $msg = !empty($body['description']) ? $body['description'] : 'Unknown error';
        $correlationId = !empty($body['correlationId']) ? $body['correlationId'] : 'Unknown correlationId';
        return sprintf('Message: %s. Correlation: %s', $msg, $correlationId);
    }
}
