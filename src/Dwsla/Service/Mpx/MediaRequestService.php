<?php

namespace Dwsla\Service\Mpx;

/**
 * A service for the MPX MediaRequest API
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
class MediaRequestService extends AbstractService
{
    /**
     * Schema for this endpoint
     *
     * @var string
     */
    public $schema = '1.2.0';

    /**
     * Base url for this endpoint
     *
     * @var string
     */
    protected static $baseUrl = 'http://mps.theplatform.com/data/MediaRequest';

    /**
     * Default number of entries
     * 
     * @var int
     */
    protected static $defaultLimit = 2000;

    /**
     * @var string
     */
    protected $token;

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
     * Get response entries for a request
     *
     * @param  array $options
     * @return array
     * @throws Exception
     */
    public function getEntries($options = [])
    {
        $params = [];

        if (empty($options['account'])) {
            throw new Exception('Account is required');
        }

        $params['query']['token'] = $this->token;
        
        if (!isset($options['range'])) {
            $options['range'] = '1-' . static::$defaultLimit;
        }

        // Add the form and schema. Ugh...
        $params['query']['form'] = $this->getFormat();
        $params['query']['schema'] = $this->getSchema();

        // Add all the $options to the query
        $params['query'] = array_merge($params['query'], $options);

        // make the call
        $result = $this->doGet('', [], $params);
        
        if (!isset($result['entries'])) {
            
            $msg = !empty($result['isException'])
                    ? $result['description']
                    : 'No "entries" key in MediaRequest results';
            throw new \RuntimeException($msg);
        }

        return array_map([$this, 'massageEntry'], $result['entries']);
    }

    /**
     * Massage a MediaRequest return entry
     * 
     * @param array $entry
     * @return array
     */
    protected function massageEntry($entry)
    {
        return [
            'mediaId' => static::massageMediaId($entry['plrequest$mediaId']),
            'requestCount' => $entry['plrequest$requestCount'],
        ];
    }

    /**
     * Strip out the url prefix to get the integer mpxId
     *
     * @param string $mediaId
     * @return mixed
     */
    protected static function massageMediaId($mediaId)
    {
       $comps = explode('/', $mediaId);
       return end($comps);
    }

}
