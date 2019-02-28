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
     * @param string $operatorNumber internal user number (employee of the company) on behalf of which a call occurs.
     * @param string $clientNumber phone number to which to make a call
     *
     * @return string Uuid of made call
     * @throws Exception
     */
    public function makeCall(string $operatorNumber, string $clientNumber): string
    {
        $this->validatePhoneNumber($clientNumber);

        if ($clientNumber[0] !== '+') {
            $clientNumber = "+{$clientNumber}";
        }

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

    /**
     * @param string $number
     *
     * @throws Exception
     */
    protected function validatePhoneNumber(string $number): void
    {
        if (!preg_match('/^\+{0,1}380[0-9]{9}$/', $number)) {
            throw new Exception("Invalid target number format: {$number}");
        }
    }
}
