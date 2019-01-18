<?php

namespace Wearesho\Phonet\Authorization;

use GuzzleHttp;

/**
 * Class Provider
 * @package Wearesho\Phonet\Authorization
 */
class Provider implements ProviderInterface
{
    /** @var GuzzleHttp\ClientInterface */
    protected $client;

    public function __construct(GuzzleHttp\ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param ConfigInterface $config
     *
     * @return GuzzleHttp\Cookie\CookieJarInterface
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function provide(ConfigInterface $config): GuzzleHttp\Cookie\CookieJarInterface
    {
        $domain = $config->getDomain();
        $request = new GuzzleHttp\Psr7\Request(
            'POST',
            "https://{$domain}/rest/security/authorize",
            [
                'Content-Type' => 'application/json'
            ],
            json_encode([
                'domain' => $domain,
                'apiKey' => $config->getApiKey(),
            ])
        );

        $response = $this->client->send($request);

        return GuzzleHttp\Cookie\CookieJar::fromArray($response->getHeader('set-cookie'), $config->getDomain());
    }
}
