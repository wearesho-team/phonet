<?php

namespace Wearesho\Phonet;

use Horat1us\Environment;

/**
 * Class EnvironmentConfig
 * @package Wearesho\Phonet
 */
class EnvironmentConfig extends Environment\Config implements ConfigInterface
{
    public function __construct(string $keyPrefix = 'PHONET_')
    {
        parent::__construct($keyPrefix);
    }

    public function getDomain(): string
    {
        return $this->getEnv('DOMAIN');
    }

    public function getApiKey(): string
    {
        return $this->getEnv('API_KEY');
    }
}
