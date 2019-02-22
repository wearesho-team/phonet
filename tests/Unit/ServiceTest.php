<?php

namespace Wearesho\Phonet\Tests\Unit;

use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet;
use chillerlan\SimpleCache;

/**
 * Class ServiceTest
 * @package Wearesho\Phonet\Tests\Unit
 */
class ServiceTest extends TestCase
{
    protected const DOMAIN = 'test.phonet.com.ua';
    protected const API_KEY = 'test-api-key';

    protected const UUID = 'test-uuid';

    /** @var array */
    protected $container;

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var SimpleCache\Cache */
    protected $cache;

    /** @var Phonet\ConfigInterface */
    protected $config;

    /** @var Phonet\Sender */
    protected $sender;

    /** @var Phonet\Service */
    protected $service;

    protected function setUp(): void
    {
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $stack = GuzzleHttp\HandlerStack::create($this->mock);
        $stack->push($history);
        $client = new GuzzleHttp\Client([
            'handler' => $stack,
        ]);
        $this->cache = new SimpleCache\Cache(new SimpleCache\Drivers\MemoryCacheDriver());
        $this->config = new Phonet\Config(
            static::DOMAIN,
            static::API_KEY
        );
        $this->sender = new Phonet\Sender(
            $client,
            $this->config,
            new Phonet\Authorization\CacheProvider($this->cache, $client)
        );
        $this->service = new Phonet\Service($this->sender);
    }

    public function testSuccessMakeCall(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(200, [], \json_encode(['uuid' => static::UUID]))
        );

        $callerNumber = 'caller';
        $callTaker = 'taker';
        /** @noinspection PhpUnhandledExceptionInspection */
        $responseUuid = $this->service->makeCall($callerNumber, $callTaker);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEquals('POST', $sentRequest->getMethod());
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
            static::UUID,
            $responseUuid
        );
    }

    public function testForceProvideForMakeCall(): void
    {
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache->set(
            $cacheKey,
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain())
        );
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id-2'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(200, [], \json_encode(['uuid' => static::UUID]))
        );

        $callerNumber = 'caller';
        $callTaker = 'taker';
        /** @noinspection PhpUnhandledExceptionInspection */
        $responseUuid = $this->service->makeCall($callerNumber, $callTaker);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[2]['request'];

        $this->assertEquals('POST', $sentRequest->getMethod());
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
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($cacheKey));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id-2'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );

        $this->assertEquals(static::UUID, $responseUuid);
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

    public function testSuccessHangup(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(200, [])
        );

        $this->service->hangup(static::UUID);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEmpty((string)$sentRequest->getBody());
        $this->assertEquals('GET', $sentRequest->getMethod());

        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($cacheKey));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );
    }

    public function testForceProvideHangup(): void
    {
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache->set(
            $cacheKey,
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain())
        );
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(200, [])
        );

        $this->service->hangup(static::UUID);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[2]['request'];

        $this->assertEmpty((string)$sentRequest->getBody());
        $this->assertEquals('GET', $sentRequest->getMethod());

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($cacheKey));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );
    }
}
