<?php

namespace Dwsla\Service\Mpx;

/**
 * A service to request media content from an MPX feed
 * @see http://help.theplatform.com/display/fs3/Feeds+service+guide
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
class MediaFeedService extends AbstractService
{

    /**
     * @var string
     */
    protected static $baseUrl = 'http://feed.theplatform.com/f/';

    /**
     * Schema for API requests (instance)
     *
     * @var string
     */
    protected $schema = "2.0.0";

    /**
     * @var string
     */
    protected $accountPid;

    /**
     * @var string
     */
    protected $feedPid;

    /**
     * Constructor
     *
     * @param string $account
     * @param string $pid
     */
    public function __construct($accountPid, $feedPid)
    {
        $this->accountPid = $accountPid;
        $this->feedPid = $feedPid;
    }

    /**
     * Get a count of the entries in the give feed
     *
     * @return int              the number of entries in the feed
     * @throws ServiceException
     */
    public function getCount($addlQueryParams = array())
    {
        $queryParams = array_merge(array(
            'count' => 'true',
            'entries' => 'false',
        ), $addlQueryParams);
        $data = $this->doGet('', array(), array(
            'query' => $queryParams,
        ));
        if (!isset($data['totalResults'])) {
            $context = array(
                'url' => self::buildFeedUrl($this->accountPid, $this->feedPid, $queryParams),
                'payload' => $data,
            );
            throw new \RuntimeException('Missing totalResults key in remote service return payload. Context = ' . json_encode($context));
        }

        return $data['totalResults'];
    }
    
    public function getCountSince($since)
    {
        $addlQueryParams = array();
        if ($since) {
            // MPX docs claim to support ISO8601, but they throw exceptions on 
            // that format. What they really want is ATOM.
            $atomSince = gmdate(DATE_ATOM, $since);
            $addlQueryParams['byUpdated'] = $atomSince . '~';
        }
        return $this->getCount($addlQueryParams);
    }

    /**
     * Get a window of media entries for a given feed
     *
     * @param  int   $start
     * @param  int   $numEntries
     * @return array the 'entries' portion of the return payload
     */
    public function getEntries($start = 1, $numEntries = null, array $fields = array(), $since = null)
    {
        
        $params = $this->buildGetEntriesParamsArray($start, $numEntries, $since);
        
        
        if (count($fields) > 0) {
            $params['query']['fields'] = implode(',', $fields);
        }
        $data = $this->doGet('', array(), $params);
        if (!isset($data['entries'])) {
            throw new \RuntimeException('No entries. Context = ' . json_encode(array(
                'service'       => 'MediaFeed',
                'acctId'        => $this->accountPid,
                'feedPid'       => $this->feedPid,
                'data'          => $data,
                'start'         => $start,
                'params'        => $params,
                'fields'        => $fields,
                'numEntries'    => $numEntries,
            )));
        }

        return $data['entries'];
    }

    /**
     * Get the client
     *
     * @return Guzzle\Http\Client
     */
    public function getClient()
    {
        if (null == $this->client) {
            $client = parent::getClient();

            $baseUrl = implode('/', array(
                rtrim(static::$baseUrl, '/'),
                $this->accountPid,
                $this->feedPid,
            ));
            $this->log('Setting base url: ' . $baseUrl);
            $client->setBaseUrl($baseUrl);
            $this->client = $client;
        }

        return $this->client;
    }

    public function buildUrlGetEntries($start = 1, $numEntries = null, array $fields = array(), $since = null)
    {
        $params = $this->buildGetEntriesParamsArray($start, $numEntries, $since);
        if (count($fields) > 0) {
            $params['query']['fields'] = implode(',', $fields);
        }
        $request = $this->getClient()->get('', array(), $params);

        return $request->getUrl();
    }

    protected function buildRangeParam($start = 1, $numEntries = null)
    {
        if (!$numEntries) {
            $start = null;
        }
        $params = array();
        $range = ($start && $numEntries)
            ? sprintf('%s-%s', $start, $start + $numEntries - 1)
            : null;

        return $range;
    }

    protected function buildGetEntriesParamsArray($start = 1, $numEntries = null, $since = null)
    {
        $params = array();
        $range = $this->buildRangeParam($start, $numEntries);
        if ($range) {
            $params['query']['range'] = $range;
        }
        if ($since) {
            // MPX docs claim to support ISO8601, but they throw exceptions on 
            // that format. What they really want is ATOM.
            $atomSince = gmdate(DATE_ATOM, $since);
            $params['query']['byUpdated'] = $atomSince . '~';
        }        
        return $params;
    }

    public static function buildFeedUrl($acctPid, $feedPid, $params = array())
    {
        return implode('/', array(
            rtrim(static::$baseUrl, '/'),
            $acctPid,
            $feedPid,
        )) . '?' . http_build_query($params);
    }
}
