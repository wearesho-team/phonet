<?php

namespace Wearesho\Phonet\Tests\Unit\Authorization;

use chillerlan\SimpleCache;
use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet\Authorization;

/**
 * Class CacheProviderTest
 * @package Wearesho\Phonet\Tests\Unit\Authorization
 */
class CacheProviderTest extends TestCase
{
    protected const DOMAIN = 'test4.domain.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var Authorization\CacheProvider */
    protected $fakeProvider;

    /** @var array */
    protected $container = [];

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var Authorization\ConfigInterface */
    protected $config;

    /** @var GuzzleHttp\ClientInterface */
    protected $client;

    protected function setUp(): void
    {
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $stack = GuzzleHttp\HandlerStack::create($this->mock);
        $stack->push($history);
        $this->client = new GuzzleHttp\Client(['handler' => $stack,]);
        $this->config = new Authorization\Config(static::DOMAIN, static::API_KEY);
    }

    public function testProvide(): void
    {
        $this->fakeProvider = new Authorization\CacheProvider(
            new SimpleCache\Cache(new SimpleCache\Drivers\MemoryCacheDriver()),
            $this->client
        );

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ])
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $cookie = $this->fakeProvider->provide($this->config);

        /** @noinspection PhpUnhandledExceptionInspection */
        $duplicatedCookie = $this->fakeProvider->provide($this->config);

        $this->assertEquals($cookie, $duplicatedCookie);
        $this->assertCount(1, $this->container, 'Only one HTTP request should be done');
    }

    public function testFailedCache(): void
    {
        $this->fakeProvider = new Authorization\CacheProvider(
            new SimpleCache\Cache(new class extends SimpleCache\Drivers\MemoryCacheDriver
            {
                public function set(string $key, $value, int $ttl = null): bool
                {
                    return false;
                }
            }),
            $this->client
        );

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ])
        );

        $this->expectException(Authorization\CacheException::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->fakeProvider->provide($this->config);
    }

    public function testOverrideWithForceProvide(): void
    {
        $cache = new SimpleCache\Cache(new SimpleCache\Drivers\MemoryCacheDriver());
        $cache->set(
            "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey()),
            'invalid-data'
        );
        $this->fakeProvider = new Authorization\CacheProvider(
            $cache,
            $this->client
        );

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ])
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $cookie = $this->fakeProvider->provide($this->config);

        /** @noinspection PhpUnhandledExceptionInspection */
        $duplicatedCookie = $this->fakeProvider->provide($this->config);

        $this->assertEquals($cookie, $duplicatedCookie);
        $this->assertCount(1, $this->container, 'Only one HTTP request should be done');
    }
}
