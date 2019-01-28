<?php

namespace Wearesho\Phonet\Authorization;

use GuzzleHttp;

/**
 * Class CacheException
 * @package Wearesho\Phonet\Authorization
 */
class CacheException extends \RuntimeException
{
    /** @var string */
    protected $cacheKey;

    /** @var GuzzleHttp\Cookie\CookieJarInterface */
    protected $cookieJar;

    public function __construct(
        string $cacheKey,
        GuzzleHttp\Cookie\CookieJarInterface $cookieJar,
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->cacheKey = $cacheKey;
        $this->cookieJar = $cookieJar;

        parent::__construct($message, $code, $previous);
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function getCookieJar(): GuzzleHttp\Cookie\CookieJarInterface
    {
        return $this->cookieJar;
    }

    public function __toString(): string
    {
        return "Failed saving cookieJar into cache with key: " . $this->getCacheKey() . PHP_EOL . parent::__toString();
    }
}
