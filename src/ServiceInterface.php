<?php

namespace Wearesho\Phonet;

use Wearesho\Phonet\Data;

/**
 * Interface ServiceInterface
 * @package Wearesho\Phonet
 */
interface ServiceInterface
{
    /**
     * Make a call
     *
     * @param string $callerNumber Internal employee number (employee of the company) on behalf of which make a call
     * @param string $callTakerNumber Phone number to which to make a call
     *
     * @return string Uuid token of call
     */
    public function makeCall(string $callerNumber, string $callTakerNumber): string;

    /**
     * Getting a list of active (conversations occur at this moment) calls
     *
     * @return Data\Collection\ActiveCall
     */
    public function activeCalls(): Data\Collection\ActiveCall;

    /**
     * Getting a list of calls made by the company
     *
     * @param \DateTimeInterface $from The beginning of the period for sample calls
     * @param \DateTimeInterface $to End of period for sample calls
     * @param Data\Collection\Direction $directions What directions are you interested in calls
     * @param int $limit Maximum number of calls possible for the sample (can not exceed 50)
     * @param int $offset Sample offset
     *
     * @return Data\Collection\Call
     */
    public function companyCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Data\Collection\Direction $directions,
        int $limit = 50,
        int $offset = 0
    ): Data\Collection\Call;

    /**
     * Getting a list of calls to call back
     *
     * @param \DateTimeInterface $from The beginning of the period for sample calls
     * @param \DateTimeInterface $to End of period for sample calls
     * @param Data\Collection\Direction $directions What directions are you interested in calls
     * @param int $limit Maximum number of calls possible for the sample (can not exceed 50)
     * @param int $offset Sample offset
     *
     * @return mixed
     */
    public function missedCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Data\Collection\Direction $directions,
        int $limit = 50,
        int $offset = 0
    ); // todo: add return type

    /**
     * Getting a list of staff calls made
     *
     * @param \DateTimeInterface $from The beginning of the period for sample calls
     * @param \DateTimeInterface $to End of period for sample calls
     * @param Data\Collection\Direction $directions What directions are you interested in calls
     * @param int $limit Maximum number of calls possible for the sample (can not exceed 50)
     * @param int $offset Sample offset
     *
     * @return mixed
     */
    public function usersCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Data\Collection\Direction $directions = null,
        int $limit = 50,
        int $offset = 0
    ); // todo: add return type

    /**
     * Getting a list of users
     *
     * @return Data\Collection\Employee
     */
    public function users(): Data\Collection\Employee;
}
