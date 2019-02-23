<?php

namespace Wearesho\Phonet\Authorization;

use GuzzleHttp;
use Wearesho\Phonet\ConfigInterface;

/**
 * Interface CacheProviderInterface
 * @package Wearesho\Phonet\Authorization
 */
interface CacheProviderInterface extends ProviderInterface
{
    /**
     * Force update cached response
     *
     * @param ConfigInterface $config
     *
     * @return GuzzleHttp\Cookie\CookieJarInterface
     * @throws ProviderException
     * @throws CacheException
     */
    public function forceProvide(ConfigInterface $config): GuzzleHttp\Cookie\CookieJarInterface;
}
