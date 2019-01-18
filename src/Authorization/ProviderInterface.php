<?php

namespace Wearesho\Phonet\Authorization;

use GuzzleHttp\Cookie\CookieJarInterface;

/**
 * Interface ProviderInterface
 * @package Wearesho\Phonet\Authorization
 */
interface ProviderInterface
{
    /**
     * Authorization method to Phonet service
     *
     * @param ConfigInterface $config
     *
     * @return CookieJarInterface Cookie container that contain JSESSIONID that must used for other api requests
     */
    public function provide(ConfigInterface $config): CookieJarInterface;
}
