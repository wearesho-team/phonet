<?php

namespace Wearesho\Phonet\Authorization;

use Horat1us\Environment;

/**
 * Class EnvironmentConfig
 * @package Wearesho\Phonet\Authorization
 */
class EnvironmentConfig extends Environment\Config implements ConfigInterface
{
    use EnvironmentConfigTrait;

    public function __construct(string $keyPrefix = 'PHONET_')
    {
        parent::__construct($keyPrefix);
    }
}
