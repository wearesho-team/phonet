<?php

namespace Wearesho\Phonet\Authorization;

use GuzzleHttp;
use Psr\SimpleCache\CacheInterface;

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
     * @throws GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function provide(ConfigInterface $config): GuzzleHttp\Cookie\CookieJarInterface
    {
        $cacheKey = $this->getCacheKey($config);

        if (!$this->cache->has($cacheKey)) {
            $response = parent::provide($config);
            $this->cacheResponse($cacheKey, $response);

            return $response;
        }

        $cached = $this->cache->get($cacheKey);

        if (!$cached instanceof GuzzleHttp\Cookie\CookieJarInterface) {
            return $this->forceProvide($config);
        }

        return $cached;
    }

    /**
     * @param ConfigInterface $config
     *
     * @return GuzzleHttp\Cookie\CookieJarInterface
     * @throws GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function cacheResponse(string $cacheKey, GuzzleHttp\Cookie\CookieJarInterface $response)
    {
        $isCacheSet = $this->cache->set($cacheKey, $response);

        if (!$isCacheSet) {
            throw new CacheException($cacheKey, $response);
        }
    }

    protected function getCacheKey(ConfigInterface $config): string
    {
        return "phonet.authorization." . sha1($config->getDomain() . $config->getApiKey());
    }
}
