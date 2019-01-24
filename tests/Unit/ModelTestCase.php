<?php

namespace Wearesho\Phonet\Tests\Unit;

use chillerlan\SimpleCache;
use PHPUnit\Framework\TestCase;
use GuzzleHttp;
use Wearesho\Phonet;

/**
 * Class ModelTestCase
 * @package Wearesho\Phonet\Tests\Unit
 */
abstract class ModelTestCase extends TestCase
{
    protected const DOMAIN = 'test.phonet.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var array */
    protected $container;

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var SimpleCache\Cache */
    protected $cache;

    /** @var Phonet\ConfigInterface */
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
        $this->config = new Phonet\Config(
            $client,
            new Phonet\Authorization\CacheProvider($this->cache),
            static::DOMAIN,
            static::API_KEY
        );
    }
}
