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
    public function provide(ConfigInterface $config): GuzzleHttp\Cookie\CookieJarInterface
    {
        $key = $this->getCacheKey($config);
        try {
            $cached = $this->cache->get($key);
        } catch (\Psr\SimpleCache\InvalidArgumentException $exception) {
            throw new CacheException($key, null, $exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!$cached instanceof GuzzleHttp\Cookie\CookieJarInterface) {
            return $this->forceProvide($config);
        }

        return $cached;
    }

    /**
     * @param ConfigInterface $config
     *
     * @return GuzzleHttp\Cookie\CookieJarInterface
     * @throws ProviderException
     */
    public function forceProvide(ConfigInterface $config): GuzzleHttp\Cookie\CookieJarInterface
    {
        $cacheKey = $this->getCacheKey($config);
        $response = parent::provide($config);
        $this->cacheResponse($cacheKey, $response);

        return $response;
    }

    /**
     * @param string $cacheKey
     * @param GuzzleHttp\Cookie\CookieJarInterface $response
     */
    protected function cacheResponse(string $cacheKey, GuzzleHttp\Cookie\CookieJarInterface $response): void
    {
        try {
            $isCacheSet = $this->cache->set($cacheKey, $response);
        } catch (\Psr\SimpleCache\InvalidArgumentException $exception) {
            throw new CacheException($cacheKey, $response, $exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!$isCacheSet) {
            throw new CacheException($cacheKey, $response);
        }
    }

    protected function getCacheKey(ConfigInterface $config): string
    {
        return "phonet.authorization." . sha1($config->getDomain() . $config->getApiKey());
    }
}
