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

    /** @var CacheException */
    protected $fakeCacheException;

    protected function setUp(): void
    {
        $this->fakeCacheException = new CacheException(
            static::CACHE_KEY,
            new CookieJar()
        );
    }

    public function testGetCacheKey(): void
    {
        $this->assertEquals(static::CACHE_KEY, $this->fakeCacheException->getCacheKey());
    }

    public function testGetCookieJar(): void
    {
        $this->assertEquals(new CookieJar(), $this->fakeCacheException->getCookieJar());
    }

    public function testToString(): void
    {
        $this->assertStringContainsString(
            "Failed saving cookieJar into cache with key: " . static::CACHE_KEY,
            (string)$this->fakeCacheException
        );
    }
}
