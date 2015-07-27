<?php

namespace DwslaTest\Unit\Service\Mpx;

use Dwsla\Service\Mpx\MediaFeedService;
use DwslaTest\Unit\Service\Mpx\AbstractServiceTest as Base;

/**
 * A test of the MPX FeedService class
 *
 * @author David Weinraub <david.weinraub@dws.la>
 */
class MediaFeedTest extends Base
{
    /**
     *
     * @param  int              $code
     * @param  array            $headers
     * @param  array            $bodyData
     * @return MediaFeedService
     */
    protected function createMockService($code, array $headers = [], array $bodyData = [])
    {
        $client = $this->createMockClient($code, $headers, $bodyData);
        $service = new MediaFeedService('some-user-name', 'some-feed-pid');
        $service->setClient($client);

        return $service;
    }

    /**
     * Test getCount() method
     */
    public function testGetCount()
    {
        $count = 5;
        $service = $this->createMockService(200, [], [
            'totalResults' => $count,
        ]);
        $this->assertEquals($count, $service->getCount());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCountThrowsException()
    {
        $service = $this->createMockService(200, [], array(
            'wrongKey' => 5,
        ));
        $count = $service->getCount();
    }

    /**
     * Test getEntries() method
     */
    public function testGetEntries()
    {
        $service = $this->createMockService(200, [], array(
            'entries' => array(
                array(
                    'key-1-1' => 'value-1-1',
                    'key-1-2' => 'value-1-2',
                ),
                array(
                    'key-2-1' => 'value-2-1',
                    'key-2-2' => 'value-2-2',
                ),
            ),
        ));
        $responseData = $service->getEntries();

        $this->assertInternalType('array', $responseData);
        $this->assertCount(2, $responseData);

        $entry = $responseData[0];
        $this->assertInternalType('array', $entry);
        $this->assertArrayHasKey('key-1-1', $entry);
        $this->assertEquals('value-1-1', $entry['key-1-1']);
        $this->assertArrayHasKey('key-1-2', $entry);
        $this->assertEquals('value-1-2', $entry['key-1-2']);

        $entry = $responseData[1];
        $this->assertInternalType('array', $entry);
        $this->assertArrayHasKey('key-2-1', $entry);
        $this->assertEquals('value-2-1', $entry['key-2-1']);
        $this->assertArrayHasKey('key-2-2', $entry);
        $this->assertEquals('value-2-2', $entry['key-2-2']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetEntriesThrowsExceptionOnMissingEntriesKey()
    {
        $service = $this->createMockService(200, [], array(
            'uselessKey' => array(
                'k1' => 'v1',
                'k2' => 'v2',
            ),
            // no 'entries' key!
        ));
        $responseData = $service->getEntries();
    }
}
