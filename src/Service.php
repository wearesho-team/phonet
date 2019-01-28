<?php

namespace Wearesho\Phonet;

use GuzzleHttp;

/**
 * Class Service
 * @package Wearesho\Phonet
 */
class Service
{
    /** @var Sender */
    protected $sender;

    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
    }

    /**
     * Return uuid of made call
     *
     * @param string $callerNumber
     * @param string $callTakerNumber
     *
     * @return string
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function makeCall(string $callerNumber, string $callTakerNumber): string
    {
        return $this->sender->send('rest/user/makeCall', \json_encode([
            'legExt'=> $callerNumber,
            'otherLegNum' => $callTakerNumber,
        ]))['uuid'];
    }
}
