<?php

namespace Dwsla\Integration\Service\Mpx;

use Dwsla\Service\Mpx\MediaFeedService;

/**
 * A live test of the MPX Media Feed service
 */
class MediaFeedServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * An auth service instance
     * 
     * @var MediaFeedService
     */
    protected static $service;
    
    public static function setUpBeforeClass()
    {
        static::$service = static::isEnabled()
                ? new MediaFeedService(DWSLA_SERVICE_MPX_MEDIAFEED_ACCTPID, DWSLA_SERVICE_MPX_MEDIAFEED_FEEDPID)
                : null;
    }

    protected static function isEnabled()
    {
        return defined('DWSLA_SERVICE_MPX_MEDIAFEED_ACCTPID') 
            && DWSLA_SERVICE_MPX_MEDIAFEED_ACCTPID
            && defined('DWSLA_SERVICE_MPX_MEDIAFEED_FEEDPID')
            && DWSLA_SERVICE_MPX_MEDIAFEED_FEEDPID;
    }
    
    public function testGetCount()
    {
        if (!static::$service) {
            return;
        }
        $this->assertGreaterThan(0, static::$service->getCount());
    }    
    
    public function testGetCountSince()
    {
        if (!static::$service) {
            return;
        }
        $this->assertGreaterThan(0, static::$service->getCountSince(strtotime('-1 week')));
    }
    
    public function testQuerystringRange()
    {
        if (!static::$service) {
            return;
        }
        $entries = static::$service->getEntriesGeneric([
            'range' => '1-1',
        ]);
        $this->assertCount(1, $entries);
    }
    
    public function testQuerystringFields()
    {
        if (!static::$service) {
            return;
        }
        $entries = static::$service->getEntriesGeneric([
            'fields' => 'id',
            'range' => '1-1',
        ]);
        $entry = $entries[0];
        $this->assertArrayHasKey('id', $entry);
        $this->assertArrayNotHasKey('guid', $entry);
    }
}
