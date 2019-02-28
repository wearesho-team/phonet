<?php

namespace Wearesho\Phonet\Tests\Unit\Api;

use GuzzleHttp;
use Wearesho\Phonet;
use chillerlan\SimpleCache;

/**
 * Trait TestCaseTrait
 * @package Wearesho\Phonet\Tests\Unit\Api
 *
 * @mixin \PHPUnit\Framework\TestCase
 */
trait TestCaseTrait
{
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
        return $this->getResponse(200, null, ['set-cookie' => ['JSESSIONID' => $id]]);
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

    protected function presetCache(string $cacheKey, GuzzleHttp\Cookie\CookieJar $cookieJar): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->set($cacheKey, $cookieJar));
    }

    protected function createCookie(string $id): GuzzleHttp\Cookie\CookieJar
    {
        return GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => $id], $this->config->getDomain());
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
        $this->assertEquals(["JSESSIONID={$needId}"], $request->getHeader('Cookie'));
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
