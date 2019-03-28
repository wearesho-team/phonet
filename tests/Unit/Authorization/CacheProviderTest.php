<?php

namespace Wearesho\Phonet\Tests\Unit\Authorization;

use chillerlan\SimpleCache;
use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet;

/**
 * Class CacheProviderTest
 * @package Wearesho\Phonet\Tests\Unit\Authorization
 */
class CacheProviderTest extends TestCase
{
    protected const DOMAIN = 'test4.domain.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var array */
    protected $container = [];

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var Phonet\ConfigInterface */
    protected $config;

    /** @var GuzzleHttp\ClientInterface */
    protected $client;

    /** @var Phonet\Authorization\CacheProviderInterface */
    protected $provider;

    protected function setUp(): void
    {
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $stack = GuzzleHttp\HandlerStack::create($this->mock);
        $stack->push($history);
        $this->client = new GuzzleHttp\Client(['handler' => $stack,]);
        $this->config = new Phonet\Config(
            static::DOMAIN,
            static::API_KEY
        );
    }

    public function testProvide(): void
    {
        $this->provider = new Phonet\Authorization\CacheProvider(
            new SimpleCache\Cache(
                new SimpleCache\Drivers\MemoryCacheDriver()
            ),
            $this->client
        );

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => 'JSESSIONID=test-id'
            ])
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $cookie = $this->provider->provide($this->config);

        /** @noinspection PhpUnhandledExceptionInspection */
        $duplicatedCookie = $this->provider->provide($this->config);

        $this->assertEquals($cookie, $duplicatedCookie);
        $this->assertCount(1, $this->container, 'Only one HTTP request should be done');
    }

    public function testFailedCache(): void
    {
        $this->provider = new Phonet\Authorization\CacheProvider(
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
                'set-cookie' => 'JSESSIONID=test-id'
            ])
        );

        $this->expectException(Phonet\Authorization\CacheException::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->provider->provide($this->config);
    }

    public function testFailedGetCache(): void
    {
        $cache = $this->createMock(SimpleCache\Cache::class);
        $cache->expects($this->once())
            ->method('get')
            ->willThrowException(new SimpleCache\SimpleCacheInvalidArgumentException());
        $this->provider = new Phonet\Authorization\CacheProvider($cache, $this->client);

        $this->expectException(Phonet\Authorization\CacheException::class);

        $this->provider->provide($this->config);
    }

    public function testFailedIsSetCache(): void
    {
        $cache = $this->createMock(SimpleCache\Cache::class);
        $cache->expects($this->once())
            ->method('set')
            ->willThrowException(new SimpleCache\SimpleCacheInvalidArgumentException());
        $this->provider = new Phonet\Authorization\CacheProvider($cache, $this->client);

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => 'JSESSIONID=test-id'
            ])
        );

        $this->expectException(Phonet\Authorization\CacheException::class);

        $this->provider->forceProvide($this->config);
    }
}
