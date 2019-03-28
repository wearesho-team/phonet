<?php

namespace Wearesho\Phonet\Authorization;

use Wearesho\Phonet\ConfigInterface;

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
     * @return string Cookie JSESSIONID that must used for other api requests
     *
     * @throws ProviderException
     */
    public function provide(ConfigInterface $config): string;
}
