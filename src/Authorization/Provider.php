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
     * @throws ProviderException
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
            \json_encode([
                'domain' => $domain,
                'apiKey' => $config->getApiKey(),
            ])
        );

        try {
            $response = $this->client->send($request);
        } catch (GuzzleHttp\Exception\GuzzleException $exception) {
            throw new ProviderException($domain, $exception->getMessage(), $exception->getCode(), $exception);
        }

        return GuzzleHttp\Cookie\CookieJar::fromArray(
            $response->getHeader('set-cookie'),
            $config->getDomain()
        );
    }
}
