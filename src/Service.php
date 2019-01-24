<?php

namespace Wearesho\Phonet;

use GuzzleHttp;

/**
 * Class Service
 * @package Wearesho\Phonet
 */
class Service extends Model
{
    /** @var ConfigInterface */
    protected $config;

    /**
     * {@inheritdoc}
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function makeCall(string $callerNumber, string $callTakerNumber): string
    {
        $uri = $this->formUri('rest/user/makeCall');
        $credentials = [
            static::CALLER_NUMBER => $callerNumber,
            static::SUBJECT_NUMBER => $callTakerNumber,
        ];
        $request = new GuzzleHttp\Psr7\Request('POST', $uri, [], \json_encode($credentials));

        $response = $this->send($request);

        return \json_decode((string)$response->getBody(), true)[static::UUID];
    }
}
