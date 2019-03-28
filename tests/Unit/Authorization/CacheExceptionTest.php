<?php

namespace Wearesho\Phonet\Tests\Unit\Authorization;

use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet\Authorization\CacheException;

/**
 * Class CacheTest
 * @package Wearesho\Phonet\Tests\Unit\Authorization
 */
class CacheExceptionTest extends TestCase
{
    protected const CACHE_KEY = 'key';
    protected const SESSION_ID = 'id';

    /** @var CacheException */
    protected $fakeCacheException;

    protected function setUp(): void
    {
        $this->fakeCacheException = new CacheException(
            static::CACHE_KEY,
            static::SESSION_ID
        );
    }

    public function testGetCacheKey(): void
    {
        $this->assertEquals(static::CACHE_KEY, $this->fakeCacheException->getCacheKey());
    }

    public function testGetCookieJar(): void
    {
        $this->assertEquals(static::SESSION_ID, $this->fakeCacheException->getSessionId());
    }

    public function testToString(): void
    {
        $this->assertStringContainsString(
            "Failed saving cookieJar into cache with key: " . static::CACHE_KEY,
            (string)$this->fakeCacheException
        );
    }
}
