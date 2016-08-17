<?php

namespace Dwsla\Service\Mpx;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Monolog\Logger;
use Psr\Http\Message\RequestInterface;

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
    protected static $version = '4.0.0';
    
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
     * @param  array $headers
     * @param  array $params
     * @return array
     * @throws Exception
     */
    protected function doGet($relativeEndpoint, $headers = [], $params = [])
    {
        $params['headers'] = $headers;
        $response = $this->getClient()->get($relativeEndpoint, $params);
        if ($response->getStatusCode() != 200) {
            throw new Exception('HTTP status ' . $response->getStatusCode());
        }
        $data = json_decode($response->getBody()->getContents(), true);

        return $data;
    }

    /**
     * Make an HTTP POST request
     *
     * @param  string $relativeEndpoint
     * @param  array $headers
     * @param string $body
     * @param  array $params
     * @return array
     */
    protected function doPost($relativeEndpoint, $headers = [], $body = '', $params = [])
    {
        $response = $this->getClient()->post($relativeEndpoint, [
            'headers' => $headers,
            'body' => $body,
            'query' => array_merge([
                'form' => static::$defaultFormat,
                'schema' => static::$defaultSchema,
            ], $params),
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

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
     * @param  Client $client
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
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
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Log a message
     *
     * @param string $message
     * @param int    $level
     * @param array $context
     * @return void
     */
    public function log($message, $level = Logger::INFO, array $context = [])
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
            'base_uri' => static::$baseUrl,
            'headers' => [
                'User-Agent' => sprintf('%s/%s', static::$userAgent, static::$version),
            ],

            // Seems to be ignored. WTH? But we use it as a hack when we build urls to display
            'query' => [
                'form' => $this->getFormat(),
                'schema' => $this->getSchema(),
            ],

            // stack with middleware handlers
            'handler' => $this->buildGuzzleStack(),
        ];
    }

    /**
     * @return HandlerStack
     */
    protected function buildGuzzleStack()
    {
        // New stack
        $stack = new HandlerStack();

        // Set handler for the stack, let Guzzle choose
        $stack->setHandler(\GuzzleHttp\choose_handler());

        // Add Request middleware to the stack that logs the url
        $stack->push(Middleware::mapRequest($this->buildMiddlewareLogRequestUrl()));

        // Return
        return $stack;
    }

    /**
     * @return \Closure
     */
    protected function buildMiddlewareLogRequestUrl()
    {
        $me = $this;
        return function(RequestInterface $request) use ($me) {
            $url = (string) $request->getUri();
            $me->log('Request url = ' . $url);
            return $request;
        };
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
