<?php

namespace Wearesho\Phonet;

/**
 * Trait ConfigTrait
 * @package Wearesho\Phonet
 */
trait ConfigTrait
{
    /** @var string */
    protected $domain;

    /** @var string */
    protected $apiKey;

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
