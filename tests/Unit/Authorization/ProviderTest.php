<?php

namespace Wearesho\Phonet\Tests\Unit\Authorization;

use PHPUnit\Framework\TestCase;
use GuzzleHttp;
use Wearesho\Phonet\Authorization;

/**
 * Class ProviderTest
 * @package Wearesho\Phonet\Tests\Unit\Authorization
 */
class ProviderTest extends TestCase
{
    protected const DOMAIN = 'test4.domain.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var Authorization\Provider */
    protected $fakeProvider;

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var array */
    protected $container;

    protected function setUp(): void
    {
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $stack = GuzzleHttp\HandlerStack::create($this->mock);
        $stack->push($history);
        $this->fakeProvider = new Authorization\Provider(
            new GuzzleHttp\Client(['handler' => $stack,])
        );
    }

    public function testProvide(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ])
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $cookies = $this->fakeProvider->provide(new Authorization\Config(
            static::DOMAIN,
            static::API_KEY
        ));

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

        $this->assertEquals(
            [
                [
                    'Name' => 'JSESSIONID',
                    'Value' => 'test-id',
                    'Domain' => 'test4.domain.com.ua',
                    'Path' => '/',
                    'Max-Age' => null,
                    'Expires' => null,
                    'Secure' => false,
                    'Discard' => true,
                    'HttpOnly' => false,
                ],
            ],
            $cookies->toArray()
        );
    }
}
