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
    public const COOKIE_UNAVAILABLE = -1;

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
            return $this->fetchSessionId(
                $this->client->send($request)->getHeaders()
            );
        } catch (GuzzleHttp\Exception\GuzzleException | CookieException $exception) {
            throw new ProviderException($domain, $exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param array $headers
     *
     * @return string
     * @throws CookieException
     */
    private function fetchSessionId(array $headers): string
    {
        if (!array_key_exists(Provider::COOKIES, $headers)) {
            throw new CookieException(
                $headers,
                'Failed fetch cookies from headers. Available headers: ' . \implode(\array_keys($headers)),
                static::COOKIE_UNAVAILABLE
            );
        }

        $cookieHeader = $headers[Provider::COOKIES];
        $cookies = \explode('; ', \array_shift($cookieHeader));

        return \array_shift($cookies);
    }
}
