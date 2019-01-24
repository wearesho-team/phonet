<?php

namespace Wearesho\Phonet;

use GuzzleHttp\ClientInterface;

/**
 * Class Config
 * @package Wearesho\Phonet
 */
class Config implements ConfigInterface
{
    use ConfigTrait;
    use AuthorizationProviderTrait;
    use ClientTrait;

    public function __construct(
        ClientInterface $client,
        Authorization\ProviderInterface $provider,
        string $domain,
        string $apiKey
    ) {
        $this->client = $client;
        $this->provider = $provider;
        $this->domain = $domain;
        $this->apiKey = $apiKey;
    }
}
