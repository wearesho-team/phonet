<?php

namespace Wearesho\Phonet\Authorization;

use GuzzleHttp;
use Psr\SimpleCache\CacheInterface;
use Wearesho\Phonet\ConfigInterface;

/**
 * Class CacheProvider
 * @package Wearesho\Phonet\Authorization
 */
class CacheProvider extends Provider implements CacheProviderInterface
{
    /** @var CacheInterface */
    protected $cache;

    public function __construct(CacheInterface $cache, GuzzleHttp\ClientInterface $client)
    {
        $this->cache = $cache;

        parent::__construct($client);
    }

    /**
     * @param ConfigInterface $config
     *
     * @return GuzzleHttp\Cookie\CookieJarInterface
     * @throws ProviderException
     */
    public function provide(ConfigInterface $config): string
    {
        $key = $this->getCacheKey($config);
        try {
            $cached = $this->cache->get($key);
        } catch (\Psr\SimpleCache\InvalidArgumentException $exception) {
            throw new CacheException($key, null, $exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!$cached) {
            return $this->forceProvide($config);
        }

        return $cached;
    }

    /**
     * @param ConfigInterface $config
     *
     * @return string
     * @throws ProviderException
     */
    public function forceProvide(ConfigInterface $config): string
    {
        $cacheKey = $this->getCacheKey($config);
        $sessionId = parent::provide($config);
        $this->cacheResponse($cacheKey, $sessionId);

        return $sessionId;
    }

    /**
     * @param string $cacheKey
     * @param string $sessionId
     */
    protected function cacheResponse(string $cacheKey, string $sessionId): void
    {
        try {
            $isCacheSet = $this->cache->set($cacheKey, $sessionId);
        } catch (\Psr\SimpleCache\InvalidArgumentException $exception) {
            throw new CacheException(
                $cacheKey,
                $sessionId,
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        if (!$isCacheSet) {
            throw new CacheException($cacheKey, $sessionId);
        }
    }

    protected function getCacheKey(ConfigInterface $config): string
    {
        return "phonet.authorization." . sha1($config->getDomain() . $config->getApiKey());
    }
}
