<?php

namespace Wearesho\Phonet;

/**
 * Trait EnvironmentConfigTrait
 * @package Wearesho\Phonet
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
