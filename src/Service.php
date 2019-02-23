<?php

namespace Wearesho\Phonet;

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
     * @param string $operatorNumber
     * @param string $clientNumber
     *
     * @return string Uuid of made call
     * @throws Exception
     */
    public function makeCall(string $operatorNumber, string $clientNumber): string
    {
        return $this->sender->post('rest/user/makeCall', \json_encode([
            'legExt'=> $operatorNumber,
            'otherLegNum' => $clientNumber,
        ]))['uuid'];
    }

    /**
     * End a call / conversation
     *
     * @param string $uuid
     *
     * @throws Exception
     */
    public function hangupCall(string $uuid): void
    {
        $this->sender->get("rest/calls/active/{$uuid}/" . RestInterface::HANGUP_CALL);
    }
}
