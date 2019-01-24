<?php

namespace Wearesho\Phonet\Authorization;

use GuzzleHttp;
use Wearesho\Phonet\ConfigInterface;

/**
 * Class Provider
 * @package Wearesho\Phonet\Authorization
 */
class Provider implements ProviderInterface
{
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

        $response = $config->client()->send($request);

        return GuzzleHttp\Cookie\CookieJar::fromArray($response->getHeader('set-cookie'), $config->getDomain());
    }
}
