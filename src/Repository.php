<?php

namespace Wearesho\Phonet;

use Carbon\Carbon;

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
     * @return Call\Active\Collection
     * @throws Exception
     */
    public function activeCalls(): Call\Active\Collection
    {
        return $this->parseActiveCalls(
            $this->sender->get('rest/calls/active/v3')
        );
    }

    /**
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param Call\Direction\Collection $directions
     * @param int $limit
     * @param int $offset
     *
     * @return Call\Complete\Collection
     * @throws Exception
     */
    public function companyCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Call\Direction\Collection $directions,
        int $limit = 50,
        int $offset = 0
    ): Call\Complete\Collection {
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
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param Call\Direction\Collection $directions
     * @param int $limit
     * @param int $offset
     *
     * @return Call\Complete\Collection
     * @throws Exception
     */
    public function missedCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Call\Direction\Collection $directions,
        int $limit = 50,
        int $offset = 0
    ): Call\Complete\Collection {
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
     * @param Call\Direction\Collection|null $directions
     * @param int $limit
     * @param int $offset
     *
     * @return Call\Complete\Collection
     * @throws Exception
     */
    public function usersCalls(
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Call\Direction\Collection $directions = null,
        int $limit = 50,
        int $offset = 0
    ): Call\Complete\Collection {
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
     * @param Call\Direction\Collection $directions
     * @param int $limit
     * @param int $offset
     *
     * @return Call\Complete\Collection
     * @throws Exception
     */
    protected function getCompleteCalls(
        string $api,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        Call\Direction\Collection $directions,
        int $limit,
        int $offset
    ): Call\Complete\Collection {
        $this->validateLimit($limit);
        $this->validateOffset($offset);

        $directions = $directions
            ?
            [
                'directions' => \array_map(function (Call\Direction $direction): int {
                    return $direction->getValue();
                }, $directions->getArrayCopy())
            ]
            :
            [];

        return $this->parseCompletedCalls(
            $this->sender->get($api, \array_merge([
                static::FROM => Carbon::make($from)->timestamp * 1000,
                static::TO => Carbon::make($to)->timestamp * 1000,
                static::LIMIT => $limit,
                static::OFFSET => $offset,
            ], $directions))
        );
    }

    /**
     * @return Employee\Collection
     * @throws Exception
     */
    public function users(): Employee\Collection
    {
        return new Employee\Collection(\array_map(function ($employee): Employee {
            return new Employee(
                $employee[static::ID],
                $employee[static::EMPLOYEE_NUMBER],
                $employee[static::DISPLAY_NAME],
                null,
                $employee[static::EMAIL]
            );
        }, $this->sender->get('rest/users')));
    }

    protected function parseCompletedCalls(array $data): Call\Complete\Collection
    {
        return new Call\Complete\Collection(\array_map(function (array $call): Call\Complete {
            $caller = $call[static::CALLER];
            $employeeCallTaker = $call[static::EMPLOYEE_CALL_TAKER];

            return new Call\Complete(
                $call[static::UUID],
                new Call\Direction($call[static::DIRECTION]),
                new Employee(
                    (int)$caller[static::ID],
                    (string)$caller[static::EMPLOYEE_NUMBER],
                    (string)$caller[static::DISPLAY_NAME],
                    $caller[static::TYPE]
                ),
                Carbon::createFromTimestampMs($call[static::END_AT]),
                new Call\Complete\Status((int)$call[static::DISPOSITION]),
                $call[static::BILL_SECS],
                $call[static::DURATION],
                \array_key_exists(static::PARENT_UUID, $call) ? $call[static::PARENT_UUID] : null,
                !\is_null($employeeCallTaker)
                    ? new Employee(
                        (int)$employeeCallTaker[static::ID],
                        (string)$employeeCallTaker[static::EMPLOYEE_NUMBER],
                        (string)$employeeCallTaker[static::DISPLAY_NAME],
                        $employeeCallTaker[static::TYPE]
                    )
                    : null,
                $call[static::SUBJECT_NUMBER],
                $call[static::SUBJECT_NAME],
                $call[static::TRUNK],
                $call[static::TRANSFER_HISTORY],
                $call[static::AUDIO_REC_URL]
            );
        }, $data));
    }

    protected function parseActiveCalls(array $data): Call\Active\Collection
    {
        return new Call\Active\Collection(\array_map(function (array $call): Call\Active {
            $bridgeAt = $call[static::BRIDGE_AT];
            $caller = $call[static::CALLER];
            $employeeCallTaker = $call[static::EMPLOYEE_CALL_TAKER];
            $subjects = $call[static::SUBJECT_COLLECTION] ?? [];

            return new Call\Active(
                $call[static::UUID],
                Carbon::createFromTimestamp($call[static::DIAL_AT]),
                new Call\Direction($call[static::DIRECTION]),
                new Call\Event($call[static::LAST_EVENT]),
                new Employee($caller[static::ID], $caller[static::EMPLOYEE_NUMBER], $caller[static::DISPLAY_NAME]),
                $call[static::TRUNK_NUMBER],
                $call[static::TRUNK_NAME],
                $call[static::PARENT_UUID],
                !\is_null($bridgeAt) ? Carbon::createFromTimestamp($bridgeAt) : null,
                !\is_null($employeeCallTaker)
                    ? new Employee(
                        $employeeCallTaker[static::ID],
                        $employeeCallTaker[static::EMPLOYEE_NUMBER],
                        $employeeCallTaker[static::DISPLAY_NAME]
                    )
                    : null,
                $subjects
                    ? new Subject\Collection(\array_map(function (array $subject): Subject {
                        return new Subject(
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
