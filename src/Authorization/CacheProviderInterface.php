<?php

namespace Wearesho\Phonet\Authorization;

use GuzzleHttp;

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
     */
    public function forceProvide(ConfigInterface $config): GuzzleHttp\Cookie\CookieJarInterface;
}
