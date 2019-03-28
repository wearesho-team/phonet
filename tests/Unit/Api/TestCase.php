<?php

namespace Wearesho\Phonet\Tests\Unit\Api;

use GuzzleHttp;
use Wearesho\Phonet;
use chillerlan\SimpleCache;

/**
 * Class TestCase
 * @package Wearesho\Phonet\Tests\Unit\Api
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const REQUEST = 'request';
    protected const DOMAIN = 'test.phonet.com.ua';
    protected const API_KEY = 'test-api-key';
    protected const SESSION_ID = 'test-session-id';
    protected const EXPIRED_SESSION_ID = 'test-expired-session-id';

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
    }

    protected function getSuccessAuthResponse(string $id): GuzzleHttp\Psr7\Response
    {
        return $this->getResponse(200, null, ['Set-Cookie' => $this->createCookie($id)]);
    }

    protected function getForbiddenAuthResponse(): GuzzleHttp\Psr7\Response
    {
        return $this->getResponse(403, 'Forbidden error');
    }

    protected function getSuccessRestResponse(?string $body): GuzzleHttp\Psr7\Response
    {
        return $this->getResponse(200, $body);
    }

    protected function getResponse(int $code, string $body = null, array $headers = []): GuzzleHttp\Psr7\Response
    {
        return new GuzzleHttp\Psr7\Response($code, $headers, $body);
    }

    public function requestExceptionProvider(): array
    {
        return [
            /** Status code | message */
            /** 4xx codes */
            [400, 'Bad request'],
            [404, 'Not found'],
            [405, 'Method not allowed'],
            [406, 'Not acceptable'],
            [407, 'Proxy authentication required'],
            [408, 'Request timeout'],
            [409, 'Conflict'],
            [410, 'Gone'],
            [411, 'Length required'],
            [412, 'Precondition failed'],
            [413, 'Request entity too large'],
            [414, 'Request uri too long'],
            [415, 'Unsupported media type'],
            [416, 'Requested range not satisfiable'],
            [417, 'Expectation failed'],
            [426, 'Upgrade require'],
            [428, 'Precondition required'],
            [429, 'Too many requests'],
            /** 5xx codes */
            [500, 'Internal server error'],
            [501, 'Not implemented'],
            [502, 'Bad gateway'],
            [503, 'Service unavailable'],
            [504, 'Gateway timeout'],
        ];
    }

    protected function getCacheKey(): string
    {
        return "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
    }

    protected function presetCache(string $cacheKey, string $sessionId): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->set($cacheKey, $sessionId));
    }

    protected function createCookie(string $id): string
    {
        return "JSESSIONID={$id}";
    }

    protected function checkAuthBody(GuzzleHttp\Psr7\Request $request): void
    {
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['domain' => static::DOMAIN, 'apiKey' => static::API_KEY]),
            (string)$request->getBody()
        );
    }

    protected function checkApi(GuzzleHttp\Psr7\Request $request, string $api): void
    {
        $this->assertEquals(
            'https://' . static::DOMAIN . $api,
            (string)$request->getUri()
        );
    }

    protected function checkCachedResponse(string $key): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($key));
    }

    protected function checkCookieHeader(GuzzleHttp\Psr7\Request $request, $needId): void
    {
        $this->assertEquals([$this->createCookie($needId)], $request->getHeaders()['Cookie']);
    }

    protected function checkMethodGet(GuzzleHttp\Psr7\Request $request): void
    {
        $this->assertEquals('GET', $request->getMethod());
    }

    protected function fetchAuthRequest(bool $forced): GuzzleHttp\Psr7\Request
    {
        return $this->container[(int)$forced][static::REQUEST];
    }

    protected function fetchSentRequest(bool $forced, bool $withoutAuthRequest = false): GuzzleHttp\Psr7\Request
    {
        return $this->container[(int)$forced + 1 - (int)$withoutAuthRequest][static::REQUEST];
    }

    protected function checkMethodPost(GuzzleHttp\Psr7\Request $request): void
    {
        $this->assertEquals('POST', $request->getMethod());
    }
}
