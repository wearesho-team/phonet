<?php

namespace Wearesho\Phonet;

use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;
use Wearesho\Phonet\Authorization\CacheProviderInterface;

/**
 * Class Sender
 * @package Wearesho\Phonet
 */
class Sender implements RestInterface
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
     * @param string|null $body
     *
     * @return array
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $api, string $body = null): array
    {
        return $this->send('GET', $api, $body);
    }

    /**
     * @param string $api
     * @param string|null $body
     *
     * @return array
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function post(string $api, string $body = null): array
    {
        return $this->send('POST', $api, $body);
    }
    
    /**
     * @param string $method
     * @param string $api
     * @param string $body
     *
     * @return array Json
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $method, string $api, ?string $body): array
    {
        $options = [
            GuzzleHttp\RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
            GuzzleHttp\RequestOptions::BODY => $body
        ];
        $uri = "https://{$this->config->getDomain()}/{$api}";

        try {
            return $this->parseResponse(
                $this->client->request($method, $uri, \array_merge([
                    GuzzleHttp\RequestOptions::COOKIES => $this->provider->provide($this->config)
                ], $options)),
                $api
            );
        } catch (GuzzleHttp\Exception\ClientException $exception) {
            if ($exception->hasResponse()
                && $exception->getResponse()->getStatusCode() === static::STATUS_FORBIDDEN
                && $this->provider instanceof Authorization\CacheProviderInterface
            ) {
                return $this->parseResponse(
                    $this->client->request($method, $uri, \array_merge([
                        GuzzleHttp\RequestOptions::COOKIES => $this->provider->forceProvide($this->config)
                    ], $options)),
                    $api
                );
            }

            throw $exception;
        }
    }

    private function parseResponse(ResponseInterface $response, string $rest): array
    {
        if (\preg_match('/\/' . RestInterface::HANGUP_CALL . '/', $rest)) {
            return [];
        }
        
        $json = \json_decode((string)$response->getBody(), true);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Response body content not json');
        }

        return $json;
    }
}
