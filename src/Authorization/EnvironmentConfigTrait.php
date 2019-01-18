<?php

namespace Wearesho\Phonet\Authorization;

/**
 * Trait EnvironmentConfigTrait
 * @package Wearesho\Phonet\Authorization
 */
trait EnvironmentConfigTrait
{
    public function getDomain(): string
    {
        return $this->getEnv('DOMAIN');
    }

    public function getApiKey(): string
    {
        return $this->getEnv('API_KEY');
    }
}
