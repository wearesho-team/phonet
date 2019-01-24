<?php

namespace Wearesho\Phonet;

use GuzzleHttp\ClientInterface;

/**
 * Interface ConfigInterface
 * @package Wearesho\Phonet
 */
interface ConfigInterface
{
    public function getDomain(): string;

    public function getApiKey(): string;

    public function provider(): Authorization\ProviderInterface;

    public function client(): ClientInterface;
}
