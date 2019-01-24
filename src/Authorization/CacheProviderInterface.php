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
     */
    public function forceProvide(ConfigInterface $config): GuzzleHttp\Cookie\CookieJarInterface;
}