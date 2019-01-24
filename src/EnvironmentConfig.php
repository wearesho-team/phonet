<?php

namespace Wearesho\Phonet;

use GuzzleHttp\ClientInterface;
use Horat1us\Environment;

/**
 * Class EnvironmentConfig
 * @package Wearesho\Phonet
 */
class EnvironmentConfig extends Environment\Config implements ConfigInterface
{
    use EnvironmentConfigTrait;
    use AuthorizationProviderTrait;
    use ClientTrait;

    public function __construct(
        ClientInterface $client,
        Authorization\ProviderInterface $provider,
        string $keyPrefix = 'PHONET_'
    ) {
        $this->client = $client;
        $this->provider = $provider;

        parent::__construct($keyPrefix);
    }
}
