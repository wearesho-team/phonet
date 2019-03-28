<?php

namespace Wearesho\Phonet\Authorization;

/**
 * Class CacheException
 * @package Wearesho\Phonet\Authorization
 */
class CacheException extends \RuntimeException
{
    /** @var string */
    protected $cacheKey;

    /** @var string|null */
    protected $sessionId;

    public function __construct(
        string $cacheKey,
        string $sessionId = null,
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->cacheKey = $cacheKey;
        $this->sessionId = $sessionId;

        parent::__construct($message, $code, $previous);
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function __toString(): string
    {
        return "Failed saving cookieJar into cache with key: " . $this->getCacheKey() . PHP_EOL . parent::__toString();
    }
}
