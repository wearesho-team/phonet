<?php

namespace Wearesho\Phonet\Tests\Unit;

use GuzzleHttp;
use Wearesho\Phonet;

/**
 * Class ServiceTest
 * @package Wearesho\Phonet\Tests\Unit
 */
class ServiceTest extends ModelTestCase
{
    /** @var Phonet\Service */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Phonet\Service($this->config);
    }

    public function testSuccessMakeCall(): void
    {
        $uuid = 'test-uuid';
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(200, [], \json_encode(['uuid' => $uuid]))
        );

        $callerNumber = 'caller';
        $callTaker = 'taker';
        /** @noinspection PhpUnhandledExceptionInspection */
        $responseUuid = $this->service->makeCall($callerNumber, $callTaker);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/user/makeCall',
            (string)$sentRequest->getUri()
        );
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['legExt' => $callerNumber, 'otherLegNum' => $callTaker]),
            (string)$sentRequest->getBody()
        );
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($cacheKey));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );

        $this->assertEquals(
            $uuid,
            $responseUuid
        );
    }

    public function testForceProvideForMakeCall(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache->set(
            "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey()),
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain())
        );
        $uuid = 'test-uuid';
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id-2'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(200, [], \json_encode(['uuid' => $uuid]))
        );

        $callerNumber = 'caller';
        $callTaker = 'taker';
        /** @noinspection PhpUnhandledExceptionInspection */
        $responseUuid = $this->service->makeCall($callerNumber, $callTaker);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[2]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id-2"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/user/makeCall',
            (string)$sentRequest->getUri()
        );
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['legExt' => $callerNumber, 'otherLegNum' => $callTaker]),
            (string)$sentRequest->getBody()
        );
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($cacheKey));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id-2'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );

        $this->assertEquals($uuid, $responseUuid);
    }

    public function testUnexpectedExceptionForMakeCall(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id-2'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(400, [], "Some error")
        );

        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->expectExceptionMessage('Some error');
        $this->expectExceptionCode(400);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->makeCall('test-1', 'test-2');
    }

    public function testUnexpectedExceptionWithAuthForMakeCall(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(404, [], 'Some error')
        );

        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->expectExceptionMessage('Some error');
        $this->expectExceptionCode(404);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->service->makeCall('test-1', 'test-2');
    }
}
