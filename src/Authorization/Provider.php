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
    protected const COOKIES = 'Set-Cookie';

    /** @var GuzzleHttp\ClientInterface */
    protected $client;

    public function __construct(GuzzleHttp\ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param ConfigInterface $config
     *
     * @return string
     * @throws ProviderException
     */
    public function provide(ConfigInterface $config): string
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

        return $this->fetchSessionId($response->getHeaders());
    }

    /**
     * @param array $headers
     *
     * @return string
     */
    private function fetchSessionId(array $headers): string
    {
        $cookieHeader = $headers[Provider::COOKIES];
        $cookies = \explode('; ', \array_shift($cookieHeader));

        return \array_shift($cookies);
    }
}
