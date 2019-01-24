<?php

namespace Wearesho\Phonet\Tests\Unit\Authorization;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet\Authorization\Provider;
use Wearesho\Phonet\Config;

/**
 * Class ConfigTest
 * @package Wearesho\Phonet\Tests\Unit\Authorization
 */
class ConfigTest extends TestCase
{
    protected const DOMAIN = 'test4.phonet.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var Config */
    protected $fakeConfig;

    protected function setUp(): void
    {
        $this->fakeConfig = new Config(new Client(), new Provider(), static::DOMAIN, static::API_KEY);
    }

    public function testGetDomain(): void
    {
        $this->assertEquals(static::DOMAIN, $this->fakeConfig->getDomain());
    }

    public function testGetApiKey(): void
    {
        $this->assertEquals(static::API_KEY, $this->fakeConfig->getApiKey());
    }

    public function testGetClient(): void
    {
        $this->assertEquals(new Client(), $this->fakeConfig->client());
    }

    public function testGetProvider(): void
    {
        $this->assertEquals(new Provider(), $this->fakeConfig->provider());
    }
}
