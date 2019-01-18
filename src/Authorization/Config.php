<?php

namespace Wearesho\Phonet\Authorization;

/**
 * Class Config
 * @package Wearesho\Phonet\Authorization
 */
class Config implements ConfigInterface
{
    use ConfigTrait;

    public function __construct(string $domain, string $apiKey)
    {
        $this->domain = $domain;
        $this->apiKey = $apiKey;
    }
}
