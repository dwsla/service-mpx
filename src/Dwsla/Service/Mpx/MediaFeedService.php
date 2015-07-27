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
     * @param string $accountPid
     * @param string $feedPid
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
    public function getCount($addlQueryParams = [])
    {
        $queryParams = array_merge(array(
            'count' => 'true',
            'entries' => 'false',
        ), $addlQueryParams);
        $data = $this->doGet('', [], array(
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
    
    public function getCountSince($since, $addlQueryParams = [])
    {
        if ($since) {
            $addlQueryParams['byUpdated'] = self::buildByUpdatedParamsFromSince($since);
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
    public function getEntries($start = 1, $numEntries = null, array $fields = [], $since = null)
    {
        
        $params = $this->buildGetEntriesParamsArray($start, $numEntries, $since);
        
        
        if (count($fields) > 0) {
            $params['query']['fields'] = implode(',', $fields);
        }
        $data = $this->doGet('', [], $params);
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
     * Get entries for a generic request
     *
     * @param  array $options
     * @return array the 'entries' portion of the return payload
     */
    public function getEntriesGeneric(array $options = [])
    {
        
        $params['query'] = $options;
        $data = $this->doGet('', [], $params);
        if (!isset($data['entries'])) {
            throw new \RuntimeException('No entries. Context = ' . json_encode(array(
                'service'       => 'MediaFeed',
                'acctId'        => $this->accountPid,
                'feedPid'       => $this->feedPid,
                'data'          => $data,
                'options'       => $options,
            )));
        }

        return $data['entries'];
    }
    
    /**
     * Get a single entry from the feed, by id
     * 
     * @param integer $id
     * @param array $fields
     * @return array 
     * @throws \RuntimeException
     * 
     * @return array|null
     */
    public function getSingleEntry($id, $fields = [])
    {
        $params = [];
        $params['query']['byId'] = $id;
        if ($fields) {
            $params['query']['fields'] = $fields;
        }
        $data = $this->doGet('', [], $params);
        if (!isset($data['entries'])) {
            throw new \RuntimeException('No entries. Context = ' . json_encode([
                'service'       => 'MediaFeed',
                'acctId'        => $this->accountPid,
                'feedPid'       => $this->feedPid,
                'data'          => $data,
                'params'        => $params,
                'fields'        => $fields,
            ]));
        }
        return !empty($data['entries'][0]) ? $data['entries'][0] : null;
    }

    /**
     * Build default config for client
     * 
     * This overrides the default base-url by adding the instance-specific
     * acctPid and feedPid
     * 
     * @return array
     */
    protected function buildClientDefaultConfig()
    {
        $options = parent::buildClientDefaultConfig();
        
        $options['base_url'] = implode('/', [
            rtrim(static::$baseUrl, '/'),
            $this->accountPid,
            $this->feedPid,
        ]);
        
        return $options;
    }

    public function buildUrlGetEntries($start = 1, $numEntries = null, array $fields = [], $since = null)
    {
        $params = $this->buildGetEntriesParamsArray($start, $numEntries, $since);
        if (count($fields) > 0) {
            $params['query']['fields'] = implode(',', $fields);
        }
        $request = $this->getClient()->get('', [], $params);

        return $request->getUrl();
    }

    /**
     * Build the url for a single video request from a feed
     * 
     * @param string$acctPid
     * @param string $feedPid
     * @param string $videoId
     * @param array $params
     * @return string
     */
    public function buildUrlGetSingleEntry($videoId, $fields = [])
    {
        $params = [];
        $params['query']['byId'] = $videoId;
        if (count($fields) > 0) {
            $params['query']['fields'] = implode(',', $fields);
        }
        $request = $this->getClient()->get('', [], $params);
        
        return $request->getUrl();
    }
    
    public static function buildRangeParam($start = 1, $numEntries = null)
    {
        if (!$numEntries) {
            $start = null;
        }
        $range = ($start && $numEntries)
            ? sprintf('%s-%s', $start, $start + $numEntries - 1)
            : null;

        return $range;
    }

    /**
     * 
     * @param int|null $start default = 1
     * @param int|null $numEntries default = null
     * @param int|null $since default = null
     * @return array
     */
    protected function buildGetEntriesParamsArray($start = 1, $numEntries = null, $since = null)
    {
        $params = [];
        $range = self::buildRangeParam($start, $numEntries);
        if ($range) {
            $params['query']['range'] = $range;
        }
        if ($since) {
            $params['query']['byUpdated'] = self::buildByUpdatedParamsFromSince($since);
        }        
        return $params;
    }
    
    /**
     * Build byUpdated param using $since unixtime
     * 
     * @param int $since
     */
    public static function buildByUpdatedParamsFromSince($since)
    {
        // MPX docs claim to support ISO8601, but they throw exceptions on 
        // that format. What they really want is ATOM.
        return gmdate(DATE_ATOM, $since) . '~';
    }

    /**
     * Build the url for a feed request
     * 
     * @param string $acctPid
     * @param string $feedPid
     * @param arry $params
     * @return string
     */
    public static function buildFeedUrl($acctPid, $feedPid, $params = [])
    {
        return implode('/', [
            rtrim(static::$baseUrl, '/'),
            $acctPid,
            $feedPid,
        ]) . '?' . http_build_query($params);
    }
    
    /**
     * Given an array of key-value pairs, construct a value usable in the ?byCustomValue query.
     * 
     * Example: Given the array:
     * 
     * $params = [
     *     'k1' => 'v1',
     *     'k2' => 'v2',
     * ];
     * 
     * the byCustomValue query value would be:
     * 
     *     {k1}{v1},{k2},{v2}
     * 
     * @param array $params
     */
    public static function buildCustomValue($params, $prefix = '')
    {
        $out = [];
        foreach ($params as $field => $value) {
            $newField = sprintf($field, $prefix);
            $out[] = sprintf('{%s}{%s}', $newField, $value);
        }
        return implode(',', $out);
    }
}
