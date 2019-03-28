<?php

namespace Wearesho\Phonet\Tests\Unit\Authorization;

use PHPUnit\Framework\TestCase;
use GuzzleHttp;
use Wearesho\Phonet;

/**
 * Class ProviderTest
 * @package Wearesho\Phonet\Tests\Unit\Authorization
 */
class ProviderTest extends TestCase
{
    protected const DOMAIN = 'test4.domain.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var Phonet\Authorization\Provider */
    protected $fakeProvider;

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var array */
    protected $container;

    /** @var Phonet\ConfigInterface */
    protected $config;

    protected function setUp(): void
    {
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $stack = GuzzleHttp\HandlerStack::create($this->mock);
        $stack->push($history);
        $this->config = new Phonet\Config(
            static::DOMAIN,
            static::API_KEY
        );
        $this->fakeProvider = new Phonet\Authorization\Provider(
            new GuzzleHttp\Client(['handler' => $stack,])
        );
    }

    public function testProvide(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'Set-Cookie' => 'JSESSIONID=test-id'
            ])
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $cookies = $this->fakeProvider->provide($this->config);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[0]['request'];
        $this->assertEquals(
            '{"domain":"' . static::DOMAIN . '","apiKey":"' . static::API_KEY . '"}',
            (string)$sentRequest->getBody()
        );
        $this->assertEquals(
            "https://" . static::DOMAIN . "/rest/security/authorize",
            (string)$sentRequest->getUri()
        );

        $this->assertEquals('JSESSIONID=test-id', $cookies);
    }
}
