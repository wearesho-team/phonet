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
     * @return string Cookie string with JSESSIONID
     * @throws ProviderException
     * @throws CacheException
     */
    public function forceProvide(ConfigInterface $config): string;
}
