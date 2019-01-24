<?php

namespace Wearesho\Phonet;

use GuzzleHttp\ClientInterface;

/**
 * Trait ClientTrait
 * @package Wearesho\Phonet
 */
trait ClientTrait
{
    /** @var ClientInterface */
    protected $client;

    public function client(): ClientInterface
    {
        return $this->client;
    }
}
