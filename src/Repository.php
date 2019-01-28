<?php

namespace Wearesho\Phonet;

use Carbon\Carbon;
use GuzzleHttp;

/**
 * Class Repository
 * @package Wearesho\Phonet
 */
class Repository
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
    protected const NUMBER = 'num';
    protected const COMPANY_NAME = 'companyName';
    protected const TRUNK_NUMBER = 'trunkNum';
    protected const TRUNK_NAME = 'trunkName';
    protected const FROM = 'timeFrom';
    protected const TO = 'timeTo';
    protected const LIMIT = 'limit';
    protected const OFFSET = 'offset';
    protected const SUBJECT_NAME = 'otherLegName';
    protected const DISPOSITION = 'disposition';
    protected const TRUNK = 'trunk';
    protected const BILL_SECS = 'billSecs';
    protected const DURATION = 'duration';
    protected const TRANSFER_HISTORY = 'transferHistory';
    protected const AUDIO_REC_URL = 'audioRecUrl';
    protected const SUBJECT_NUMBER = 'otherLegNum';

    /** @var Sender */
    protected $sender;

    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return Data\Collection\ActiveCall
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function activeCalls(): Data\Collection\ActiveCall
    {
        $response = $this->sender->send('rest/calls/active/v3', null);
        $data = \json_decode((string)$response->getBody(), true);

        return new Data\Collection\ActiveCall(\array_map(function (array $call): Data\ActiveCall {
            $bridgeAt = $call[static::BRIDGE_AT];
            $caller = $call[static::CALLER];
            $employeeCallTaker = $call[static::EMPLOYEE_CALL_TAKER];
            $subjects = $call[static::SUBJECT_COLLECTION] ?? [];

            return new Data\ActiveCall(
                $call[static::UUID],
                Carbon::createFromTimestamp($call[static::DIAL_AT]),
                new Enum\Direction($call[static::DIRECTION]),
                new Enum\LastEvent($call[static::LAST_EVENT]),
                new Data\Employee($caller[static::ID], $caller[static::EMPLOYEE_NUMBER], $caller[static::DISPLAY_NAME]),
                $call[static::TRUNK_NUMBER],
                $call[static::TRUNK_NAME],
                $call[static::PARENT_UUID],
                !\is_null($bridgeAt) ? Carbon::createFromTimestamp($bridgeAt) : null,
                !\is_null($employeeCallTaker)
                    ? new Data\Employee(
                        $employeeCallTaker[static::ID],
                        $employeeCallTaker[static::EMPLOYEE_NUMBER],
                        $employeeCallTaker[static::DISPLAY_NAME]
                    )
                    : null,
                $subjects
                    ? new Data\Collection\Subject(\array_map(function (array $subject): Data\Subject {
                        return new Data\Subject(
                            $subject[static::NUMBER],
                            $subject[static::URL],
                            $subject[static::ID],
                            $subject[static::NAME],
                            $subject[static::COMPANY_NAME],
                            isset($subject[static::PRIORITY]) ? $subject[static::PRIORITY] : null
                        );
                    }, $subjects))
                    : null
            );
        }, $data));
    }

    /**
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param Data\Collection\Direction $directions
     * @param int $limit
     * @param int $offset
     *
     * @return Data\Collection\CompleteCall
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function companyCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Data\Collection\Direction $directions,
        int $limit = 50,
        int $offset = 0
    ): Data\Collection\CompleteCall {
        return $this->getCompleteCalls(
            "rest/calls/company.api",
            $from,
            $to,
            $directions,
            $limit,
            $offset
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function missedCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Data\Collection\Direction $directions,
        int $limit = 50,
        int $offset = 0
    ): Data\Collection\CompleteCall {
        return $this->getCompleteCalls(
            "rest/calls/missed.api",
            $from,
            $to,
            $directions,
            $limit,
            $offset
        );
    }

    /**
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param Data\Collection\Direction|null $directions
     * @param int $limit
     * @param int $offset
     *
     * @return Data\Collection\CompleteCall
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function usersCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Data\Collection\Direction $directions = null,
        int $limit = 50,
        int $offset = 0
    ): Data\Collection\CompleteCall {
        return $this->getCompleteCalls(
            "rest/calls/users.api",
            $from,
            $to,
            $directions,
            $limit,
            $offset
        );
    }

    /**
     * @param string $api
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param Data\Collection\Direction $directions
     * @param int $limit
     * @param int $offset
     *
     * @return Data\Collection\CompleteCall
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    protected function getCompleteCalls(
        string $api,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Data\Collection\Direction $directions,
        int $limit,
        int $offset
    ): Data\Collection\CompleteCall {
        $this->validateLimit($limit);
        $this->validateOffset($offset);

        $directions = $directions
            ?
            [
                'directions' => $directions->map(function (Enum\Direction $direction): int {
                    return $direction->getValue();
                })
            ]
            :
            [];

        $response = $this->sender->send($api, \json_encode(\array_merge([
            static::FROM => Carbon::make($from)->timestamp,
            static::TO => Carbon::make($to)->timestamp,
            static::LIMIT => $limit,
            static::OFFSET => $offset,
        ], $directions)));

        return $this->parseCompletedCalls(
            (string)$response->getBody()
        );
    }

    /**
     * @return Data\Collection\Employee
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function users(): Data\Collection\Employee
    {
        $response = $this->sender->send('rest/users', null);
        $data = \json_decode((string)$response->getBody(), true);

        return new Data\Collection\Employee(\array_map(function ($employee): Data\Employee {
            return new Data\Employee(
                $employee[static::ID],
                $employee[static::EMPLOYEE_NUMBER],
                $employee[static::DISPLAY_NAME],
                null,
                $employee[static::EMAIL]
            );
        }, $data));
    }

    protected function parseCompletedCalls(string $data): Data\Collection\CompleteCall
    {
        return new Data\Collection\CompleteCall(\array_map(function (array $call): Data\CompleteCall {
            $caller = $call[static::CALLER];
            $employeeCallTaker = $call[static::EMPLOYEE_CALL_TAKER];

            return new Data\CompleteCall(
                $call[static::UUID],
                new Enum\Direction($call[static::DIRECTION]),
                new Data\Employee(
                    $caller[static::ID],
                    $caller[static::EMPLOYEE_NUMBER],
                    $caller[static::DISPLAY_NAME],
                    $caller[static::TYPE]
                ),
                Carbon::createFromTimestamp($call[static::END_AT]),
                $call[static::DISPOSITION],
                $call[static::BILL_SECS],
                $call[static::DURATION],
                \array_key_exists(static::PARENT_UUID, $call) ? $call[static::PARENT_UUID] : null,
                !\is_null($employeeCallTaker)
                    ? new Data\Employee(
                        $employeeCallTaker[static::ID],
                        $employeeCallTaker[static::EMPLOYEE_NUMBER],
                        $employeeCallTaker[static::DISPLAY_NAME],
                        $employeeCallTaker[static::TYPE]
                    )
                    : null,
                $call[static::SUBJECT_NUMBER],
                $call[static::SUBJECT_NAME],
                $call[static::TRUNK],
                $call[static::TRANSFER_HISTORY],
                $call[static::AUDIO_REC_URL]
            );
        }, \json_decode($data, true)));
    }

    protected function validateLimit(int $limit): void
    {
        if ($limit > 50 || $limit < 1) {
            throw new \InvalidArgumentException("Invalid limit: {$limit}. It must be in range between 1 and 50");
        }
    }

    protected function validateOffset(int $offset): void
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException("Invalid offset: {$offset}. It can not be less then 0");
        }
    }
}
