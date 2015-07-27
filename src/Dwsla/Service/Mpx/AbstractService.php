<?php

namespace Dwsla\Service\Mpx;

use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use Monolog\Logger;

/**
 * Abstract base service for MPX services
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
abstract class AbstractService
{

    /**
     * User agent
     * 
     * @var string
     */
    protected static $userAgent = 'dwsla-mpx';
    
    /**
     * Version
     * 
     * @var string
     */
    protected static $version = '2.1.0';
    
    /**
     * A log object
     *
     * @var Logger
     */
    protected $logger;

    /**
     * HTTP client
     * 
     * @var Client
     */
    protected $client;

    /**
     * Default format for API requests
     *
     * @var string
     */
    protected static $defaultFormat = 'json';

    /**
     * Format for API requests (instance)
     *
     * @var string
     */
    protected $format;

    /**
     * Default schema version for API requests
     *
     * @var string
     */
    protected static $defaultSchema = '1.0.0';

    /**
     * Schema for API requests (instance)
     *
     * @var string
     */
    protected $schema;

    /**
     * Base url for API requests
     *
     * @var string
     */
    protected static $baseUrl = '';

    /**
     * Make an HTTP GET request
     *
     * @param  string $relativeEndpoint
     * @param  array  $headers
     * @param  array  $params
     * @return array
     */
    protected function doGet($relativeEndpoint, $headers = array(), $params = array())
    {
        $client = $this->getClient();
        $request = $client->createRequest('GET', $relativeEndpoint, array_merge([
            'headers' => $headers,
        ], $params));
        $this->log(sprintf('Request url: %s', $request->getUrl()));
        $response = $client->send($request);
        if ($response->getStatusCode() != 200) {
            throw new Exception('HTTP status ' . $response->getStatusCode());
        }
        $data = $response->json();

        return $data;
    }

    /**
     * Make an HTTP POST request
     *
     * @param  string $relativeEndpoint
     * @param  array  $headers
     * @param  array  $params
     * @return array
     */
    protected function doPost($relativeEndpoint, $headers = array(), $body = '', $params = array())
    {
        $client = $this->getClient();
        $request = $client->createRequest('POST', $relativeEndpoint, [
            'headers' => $headers,
            'body' => Stream::factory($body),
            'query' => $params,
        ]);
        $this->log(sprintf('Request url: %s', $request->getUrl()));
        $response = $client->send($request);
        $data = $response->json();

        return $data;
    }

    /**
     * Get the client
     *
     * @return Client
     */
    public function getClient()
    {
        if (null === $this->client) {
            $options = $this->buildClientDefaultConfig();
            $client = new Client($options);
            $this->client = $client;
        }
        return $this->client;
    }

    /**
     * Set client for this service
     *
     * @param  \Guzzle\Http\Client $client
     * @return type
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Set default format for API requests
     *
     * @param  string $format json, xml, etc.
     * @return void
     */
    public static function setDefaultFormat($format)
    {
        static::$defaultFormat = $format;
    }

    /**
     * Get default format for API requests
     *
     * @return string
     */
    public static function getDefaultFormat()
    {
        return static::$defaultFormat;
    }

    /**
     * Set default schema version for API requests
     *
     * @param  string $schema
     * @return void
     */
    public static function setDefaultSchema($schema)
    {
        static::$defaultSchema = $schema;
    }

    /**
     * Get the default schema for version API requests
     *
     * @return string
     */
    public static function getDefaultSchema()
    {
        return static::$defaultSchema;
    }

    /**
     * Get schema version for API requests
     *
     * @return string
     */
    public function getSchema()
    {
        if (null === $this->schema) {
            $this->schema = static::getDefaultSchema();
        }

        return $this->schema;
    }

    /**
     * Set schema version for API requests
     *
     * @param  string $schema
     * @return type
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * Get format for API requests
     *
     * @return string
     */
    public function getFormat()
    {
        if (null === $this->format) {
            $this->format = static::getDefaultFormat();
        }

        return $this->format;
    }

    /**
     * Set format for API requests
     *
     * @param  string $format json, xml, etc.
     * @return type
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    public function buildRequestUrl($relativeEndpoint, array $params = array())
    {
        // assume GET
        return $this->getClient()->get($relativeEndpoint, array(), $params)->getUrl();
    }

    /**
     * Get the logger
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set the logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Log a message
     *
     * @param  string $message
     * @param  int    $level
     * @return void
     */
    public function log($message, $level = Logger::INFO, array $context = array())
    {
        if (!$this->logger) {
            return;
        }

        if (null === $level) {
            $level = Logger::INFO;
        }

        switch ($level) {
            case Logger::DEBUG:
                $this->logger->addDebug($message, $context);
                break;
            case Logger::INFO:
                $this->logger->addInfo($message, $context);
                break;
            case Logger::NOTICE:
                $this->logger->addNotice($message, $context);
                break;
            case Logger::WARNING:
                $this->logger->addWarning($message, $context);
                break;
            case Logger::ERROR:
                $this->logger->addError($message, $context);
                break;
            case Logger::CRITICAL:
                $this->logger->addCritical($message, $context);
                break;
            case Logger::EMERGENCY:
                $this->logger->addEmergency($message, $context);
                break;
            default;
                break;
        }
    }
    
    /**
     * Build default config for client
     * 
     * @return array
     */
    protected function buildClientDefaultConfig()
    {
        return [
            'base_url' => static::$baseUrl,
            'defaults' => [
                'headers' => [
                    'User-Agent' => sprintf('%s/%s', static::$userAgent, static::$version),
                ],
                'query' => [
                    'form' => $this->getFormat(),
                    'schema' => $this->getSchema(),
                ],                    
            ],
        ];        
    }
    
    /**
     * Attempt to proxy log* calls to the logger
     * 
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) == 'log'
                && $this->logger
                && method_exists($this->logger, $name)
        ) {
            return call_user_func_array([$this->logger, $name], $arguments);
        }
        throw new \RuntimeException('Unknown method: ' . $name);
    }
    
}
