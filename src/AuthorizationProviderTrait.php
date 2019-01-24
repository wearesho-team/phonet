<?php

namespace Wearesho\Phonet;

use Wearesho\Phonet\Authorization\ProviderInterface;

/**
 * Trait AuthorizationProviderTrait
 * @package Wearesho\Phonet
 */
trait AuthorizationProviderTrait
{
    /** @var ProviderInterface */
    protected $provider;

    public function provider(): ProviderInterface
    {
        return $this->provider;
    }
}
