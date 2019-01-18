<?php

namespace Wearesho\Phonet\Tests\Unit\Authorization;

use Horat1us\Environment\Exception;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet\Authorization\EnvironmentConfig;

/**
 * Class EnvironmentConfigTest
 * @package Wearesho\Phonet\Tests\Unit\Authorization
 */
class EnvironmentConfigTest extends TestCase
{
    protected const DOMAIN = 'test4.phonet.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var EnvironmentConfig */
    protected $fakeEnvironmentConfig;

    protected function setUp(): void
    {
        $this->fakeEnvironmentConfig = new EnvironmentConfig();
    }

    protected function tearDown(): void
    {
        putenv('PHONET_DOMAIN');
        putenv('PHONET_API_KEY');
    }

    public function testSuccessGetDomain(): void
    {
        putenv('PHONET_DOMAIN=' . static::DOMAIN);

        $this->assertEquals(static::DOMAIN, $this->fakeEnvironmentConfig->getDomain());
    }

    public function testFailedGetDomain(): void
    {
        $this->expectException(Exception\Missing::class);
        $this->expectExceptionMessage('Missing environment key PHONET_DOMAIN');

        $this->fakeEnvironmentConfig->getDomain();
    }

    public function testSuccessGetApiKey(): void
    {
        putenv('PHONET_API_KEY=' . static::API_KEY);

        $this->assertEquals(static::API_KEY, $this->fakeEnvironmentConfig->getApiKey());
    }

    public function testFailedGetApiKey(): void
    {
        $this->expectException(Exception\Missing::class);
        $this->expectExceptionMessage('Missing environment key PHONET_API_KEY');

        $this->fakeEnvironmentConfig->getApiKey();
    }
}
