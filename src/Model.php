<?php

namespace Wearesho\Phonet;

use Carbon\Carbon;
use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Model
 * @package Wearesho\Phonet
 */
abstract class Model
{
    protected const UUID = 'uuid';
    protected const EMPLOYEE_NUMBER = 'ext';
    protected const CALLER = 'leg';
    protected const EMPLOYEE_CALL_TAKER = 'leg2';
    protected const BRIDGE_AT = 'bridgeAt';
    protected const SUBJECT_COLLECTION = 'otherLegs';
    protected const PARENT_UUID = 'parentUuid';
    protected const ID = 'id';
    protected const DIRECTION = 'lgDirection';
    protected const DISPLAY_NAME = 'displayName';
    protected const TYPE = 'type';
    protected const DIAL_AT = 'dialAt';
    protected const LAST_EVENT = 'lastEvent';
    protected const NAME = 'name';
    protected const URL = 'url';
    protected const PRIORITY = 'priority';
    protected const END_AT = 'endAt';
    protected const EMAIL = 'email';
    protected const SUBJECT_NUMBER = 'otherLegNum';
    protected const NUMBER = 'num';
    protected const COMPANY_NAME = 'companyName';
    protected const TRUNK_NUMBER = 'trunkNum';
    protected const TRUNK_NAME = 'trunkName';
    protected const FROM = 'timeFrom';
    protected const TO = 'timeTo';
    protected const CALLER_NUMBER = 'legExt';
    protected const LIMIT = 'limit';
    protected const OFFSET = 'offset';
    protected const SUBJECT_NAME = 'otherLegName';
    protected const DISPOSITION = 'disposition';
    protected const TRUNK = 'trunk';
    protected const BILL_SECS = 'billSecs';
    protected const DURATION = 'duration';
    protected const TRANSFER_HISTORY = 'transferHistory';
    protected const AUDIO_REC_URL = 'audioRecUrl';

    protected const STATUS_FORBIDDEN = 403;

    /** @var ConfigInterface */
    protected $config;

    /**
     * Model constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param GuzzleHttp\Psr7\Request $request
     *
     * @return ResponseInterface
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    protected function send(GuzzleHttp\Psr7\Request $request): ResponseInterface
    {
        $provider = $this->config->provider();
        $headers = [
            GuzzleHttp\RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
        ];

        try {
            return $this->config->client()
                ->send(
                    $request,
                    \array_merge([
                        GuzzleHttp\RequestOptions::COOKIES => $provider->provide($this->config)
                    ], $headers)
                );
        } catch (GuzzleHttp\Exception\ClientException $exception) {
            if ($exception->hasResponse()
                && $exception->getResponse()->getStatusCode() === static::STATUS_FORBIDDEN
                && $provider instanceof Authorization\CacheProviderInterface
            ) {
                return $this->config->client()
                    ->send(
                        $request,
                        \array_merge([
                            GuzzleHttp\RequestOptions::COOKIES => $provider->forceProvide($this->config)
                        ], $headers)
                    );
            }

            throw $exception;
        }
    }

    protected function formUri(string $api): string
    {
        return "https://{$this->config->getDomain()}/{$api}";
    }
}
