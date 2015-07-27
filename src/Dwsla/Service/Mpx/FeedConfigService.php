<?php

namespace Dwsla\Service\Mpx;

/**
 * A service for the MPX FeedConfig API
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
class FeedConfigService extends AbstractService
{
    /**
     * Schema for this endpoint
     *
     * @var string
     */
    public $schema = '2.0.0';

    /**
     * Base url for this endpoint
     *
     * @var string
     */
    protected static $baseUrl = 'http://data.feed.theplatform.com/feed/data/';

    /**
     * Auth token from Authentication endpoint
     *
     * @var string
     */
    protected $token;

    /**
     *
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     *
     * @param  type $options
     * @return type
     */
    public function getEntries($account, $options = [])
    {
        $params = [];

        // add token
        $params['query']['token'] = $this->token;

        // limit on account
        $params['query']['account'] = $account;

        // field limiting?
        if (
                isset($options['fields']) &&
                is_array($options['fields']) &&
                count($options['fields']) > 0
            ){
            $params['query']['fields'] = implode(',', $options['fields']);
        }

        // filtering?
        if (
                isset($options['filter']['field']) &&
                isset($options['filter']['value'])
            ){
            $params['query'][$options['filter']['field']] = $options['filter']['value'];
        }

        // make the call
        $entries = $this->doGet('FeedConfig', [], $params);
        if (!isset($entries['entries'])) {
            throw new \RuntimeException('No "entries" key in FeedConfig results');
        }

        return $entries['entries'];
    }

}
