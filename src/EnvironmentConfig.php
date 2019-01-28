<?php

namespace Wearesho\Phonet;

use Horat1us\Environment;

/**
 * Class EnvironmentConfig
 * @package Wearesho\Phonet
 */
class EnvironmentConfig extends Environment\Config implements ConfigInterface
{
    use EnvironmentConfigTrait;

    public function __construct(string $keyPrefix = 'PHONET_')
    {
        parent::__construct($keyPrefix);
    }
}
