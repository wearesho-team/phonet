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

    protected function setUp(): void
    {
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $stack = GuzzleHttp\HandlerStack::create($this->mock);
        $stack->push($history);
        $this->client = new GuzzleHttp\Client(['handler' => $stack,]);
    }

    public function testProvide(): void
    {
        $this->config = new Phonet\Config(
            $this->client,
            new Phonet\Authorization\CacheProvider(
                new SimpleCache\Cache(
                    new SimpleCache\Drivers\MemoryCacheDriver()
                )
            ),
            static::DOMAIN,
            static::API_KEY
        );

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ])
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $cookie = $this->config->provider()->provide($this->config);

        /** @noinspection PhpUnhandledExceptionInspection */
        $duplicatedCookie = $this->config->provider()->provide($this->config);

        $this->assertEquals($cookie, $duplicatedCookie);
        $this->assertCount(1, $this->container, 'Only one HTTP request should be done');
    }

    public function testFailedCache(): void
    {
        $this->config = new Phonet\Config(
            $this->client,
            new Phonet\Authorization\CacheProvider(
                new SimpleCache\Cache(new class extends SimpleCache\Drivers\MemoryCacheDriver
                {
                    public function set(string $key, $value, int $ttl = null): bool
                    {
                        return false;
                    }
                })
            ),
            static::DOMAIN,
            static::API_KEY
        );

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ])
        );

        $this->expectException(Phonet\Authorization\CacheException::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->config->provider()->provide($this->config);
    }

    public function testOverrideWithForceProvide(): void
    {
        $cache = new SimpleCache\Cache(new SimpleCache\Drivers\MemoryCacheDriver());
        $this->config = new Phonet\Config(
            $this->client,
            new Phonet\Authorization\CacheProvider($cache),
            static::DOMAIN,
            static::API_KEY
        );
        /** @noinspection PhpUnhandledExceptionInspection */
        $cache->set(
            "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey()),
            'invalid-data'
        );

        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ])
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $cookie = $this->config->provider()->provide($this->config);

        /** @noinspection PhpUnhandledExceptionInspection */
        $duplicatedCookie = $this->config->provider()->provide($this->config);

        $this->assertEquals($cookie, $duplicatedCookie);
        $this->assertCount(1, $this->container, 'Only one HTTP request should be done');
    }
}
