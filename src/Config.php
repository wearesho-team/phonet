<?php

namespace Wearesho\Phonet;

/**
 * Class Config
 * @package Wearesho\Phonet
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
