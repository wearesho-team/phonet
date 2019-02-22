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
     * @throws Exception
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
     * @throws Exception
     */
    public function post(string $api, string $body = null): array
    {
        return $this->send('POST', $api, $body);
    }

    /**
     * @param string $method
     * @param string $api
     * @param string|null $body
     *
     * @return array
     * @throws Exception
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
            // Provider can throw ProviderException or CacheException (if it instance of it) so no reasons to catch them
            $cookies = $this->provider->provide($this->config);
            $response = $this->client->request($method, $uri, \array_merge([
                GuzzleHttp\RequestOptions::COOKIES => $cookies
            ], $options));
        } catch (GuzzleHttp\Exception\GuzzleException $exception) {
            // Checking exception with hasResponse() is optional, but for better logic execution it must be here
            // If service return status code 403 and provider can cache response, sender will try auth with force option
            if ($exception instanceof GuzzleHttp\Exception\ClientException
                && $exception->hasResponse()
                && $exception->getResponse()->getStatusCode() === static::STATUS_FORBIDDEN
                && $this->provider instanceof Authorization\CacheProviderInterface
            ) {
                try {
                    // CacheProvider can throw ProviderException or CacheException so no reasons to catch them
                    $cookies = $this->provider->forceProvide($this->config);
                    $response = $this->client->request($method, $uri, \array_merge([
                        GuzzleHttp\RequestOptions::COOKIES => $cookies
                    ], $options));
                } catch (GuzzleHttp\Exception\GuzzleException $exception) {
                    throw new Exception("Force auth provide for api [$api] failed", $exception->getCode(), $exception);
                }
            } else {
                throw new Exception("Api [{$api}] failed", $exception->getCode(), $exception);
            }
        }

        return $this->parseResponse($response, $api);
    }

    private function parseResponse(ResponseInterface $response, string $rest): array
    {
        // In Phonet documentation only `hangup` api (get-method) contain empty body in response
        // So no reason to parse it
        if (\preg_match('/\/' . RestInterface::HANGUP_CALL . '/', $rest)) {
            return [];
        }

        $json = \json_decode((string)$response->getBody(), true);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("[$rest] return response with body that have content not json");
        }

        return $json;
    }
}
