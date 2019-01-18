<?php

namespace Wearesho\Phonet\Tests\Unit;

use chillerlan\SimpleCache;
use PHPUnit\Framework\TestCase;
use GuzzleHttp;
use Wearesho\Phonet;

/**
 * Class ServiceTest
 * @package Wearesho\Phonet\Tests\Unit
 */
class ServiceTest extends TestCase
{
    protected const DOMAIN = 'test.phonet.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var array */
    protected $container;

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var Phonet\ServiceInterface */
    protected $service;

    /** @var SimpleCache\Cache */
    protected $cache;

    /** @var Phonet\Authorization\ConfigInterface */
    protected $config;

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
        $this->config = new Phonet\Authorization\Config(static::DOMAIN, static::API_KEY);

        $this->service = new Phonet\Service(
            $client,
            $this->config,
            new Phonet\Authorization\CacheProvider($this->cache, $client)
        );
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
        $responseUuid = $this->service->makeCall($callerNumber, $callTaker);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://'. static::DOMAIN . '/rest/user/makeCall',
            (string)$sentRequest->getUri()
        );
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['legExt' => $callerNumber, 'otherLegNum' => $callTaker]),
            (string)$sentRequest->getBody()
        );
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        $this->cache->has($cacheKey);
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
        $responseUuid = $this->service->makeCall($callerNumber, $callTaker);

        $this->assertEquals($uuid, $responseUuid);
    }
}
