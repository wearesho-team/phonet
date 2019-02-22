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
     * Start new call
     *
     * @param string $callerNumber
     * @param string $callTakerNumber
     *
     * @return string Uuid of made call
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function makeCall(string $callerNumber, string $callTakerNumber): string
    {
        return $this->sender->post('rest/user/makeCall', \json_encode([
            'legExt'=> $callerNumber,
            'otherLegNum' => $callTakerNumber,
        ]))['uuid'];
    }

    /**
     * End a call / conversation
     *
     * @param string $uuid
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function hangup(string $uuid): void
    {
        $this->sender->get("rest/calls/active/{$uuid}/" . RestInterface::HANGUP_CALL);
    }
}
