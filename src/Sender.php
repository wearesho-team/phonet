<?php

namespace Wearesho\Phonet;

use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;
use Wearesho\Phonet\Authorization\CacheProviderInterface;

/**
 * Class Sender
 * @package Wearesho\Phonet
 */
class Sender
{
    protected const STATUS_FORBIDDEN = 403;

    /** @var GuzzleHttp\ClientInterface */
    protected $client;

    /** @var ConfigInterface */
    protected $config;

    /** @var Authorization\ProviderInterface|CacheProviderInterface */
    protected $provider;

    public function __construct(
        GuzzleHttp\ClientInterface $client,
        ConfigInterface $config,
        Authorization\ProviderInterface $provider
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->provider = $provider;
    }

    /**
     * @param string $api
     * @param string $body
     *
     * @return ResponseInterface
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $api, ?string $body): ResponseInterface
    {
        $options = [
            GuzzleHttp\RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
            GuzzleHttp\RequestOptions::BODY => $body
        ];
        $uri = "https://{$this->config->getDomain()}/{$api}";

        try {
            return $this->client->request('POST', $uri, \array_merge([
                GuzzleHttp\RequestOptions::COOKIES => $this->provider->provide($this->config)
            ], $options));
        } catch (GuzzleHttp\Exception\ClientException $exception) {
            if ($exception->hasResponse()
                && $exception->getResponse()->getStatusCode() === static::STATUS_FORBIDDEN
                && $this->provider instanceof Authorization\CacheProviderInterface
            ) {
                return $this->client->request('POST', $uri, \array_merge([
                    GuzzleHttp\RequestOptions::COOKIES => $this->provider->forceProvide($this->config)
                ], $options));
            }

            throw $exception;
        }
    }
}
