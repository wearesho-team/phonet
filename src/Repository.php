<?php

namespace Wearesho\Phonet;

use Carbon\Carbon;
use GuzzleHttp;

/**
 * Class Repository
 * @package Wearesho\Phonet
 */
class Repository extends Model
{
    /**
     * {@inheritdoc}
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function activeCalls(): Data\Collection\ActiveCall
    {
        $request = new GuzzleHttp\Psr7\Request('GET', $this->formUri("rest/calls/active/v3"));

        $json = \json_decode((string)$this->send($request)->getBody(), true);

        return new Data\Collection\ActiveCall(\array_map(function (array $call): Data\ActiveCall {
            $bridgeAt = $call[static::BRIDGE_AT];
            $caller = $call[static::CALLER];
            $employeeCallTaker = $call[static::EMPLOYEE_CALL_TAKER];
            $subjects = $call[static::SUBJECT_COLLECTION] ?? [];

            return new Data\ActiveCall(
                $call[static::UUID],
                $call[static::PARENT_UUID],
                Carbon::createFromTimestamp($call[static::DIAL_AT]),
                !\is_null($bridgeAt) ? Carbon::createFromTimestamp($bridgeAt) : null,
                new Enum\Direction($call[static::DIRECTION]),
                new Enum\LastEvent($call[static::LAST_EVENT]),
                new Data\Employee($caller[static::ID], $caller[static::EMPLOYEE_NUMBER], $caller[static::DISPLAY_NAME]),
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
                            $subject[static::ID],
                            $subject[static::NAME],
                            $subject[static::NUMBER],
                            $subject[static::COMPANY_NAME],
                            $subject[static::URL],
                            isset($subject[static::PRIORITY]) ? $subject[static::PRIORITY] : null
                        );
                    }, $subjects))
                    : null,
                $call[static::TRUNK_NUMBER],
                $call[static::TRUNK_NAME]
            );
        }, $json));
    }

    /**
     * {@inheritdoc}
     *
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
     * {@inheritdoc}
     *
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

        $request = new GuzzleHttp\Psr7\Request(
            'GET',
            $this->formUri($api),
            [],
            \json_encode(\array_merge([
                static::FROM => Carbon::make($from)->timestamp,
                static::TO => Carbon::make($to)->timestamp,
                static::LIMIT => $limit,
                static::OFFSET => $offset,
            ], $directions))
        );

        return $this->parseCompletedCalls(
            (string)$this->send($request)->getBody()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function users(): Data\Collection\Employee
    {
        $uri = $this->formUri('rest/users');
        $request = new GuzzleHttp\Psr7\Request('GET', $uri);

        $response = $this->send($request);

        return new Data\Collection\Employee(\array_map(function ($employee): Data\Employee {
            return new Data\Employee(
                $employee[static::ID],
                $employee[static::EMPLOYEE_NUMBER],
                $employee[static::DISPLAY_NAME],
                null,
                $employee[static::EMAIL]
            );
        }, \json_decode((string)$response->getBody(), true)));
    }

    protected function parseCompletedCalls(string $data): Data\Collection\CompleteCall
    {
        return new Data\Collection\CompleteCall(\array_map(function (array $call): Data\CompleteCall {
            $caller = $call[static::CALLER];
            $employeeCallTaker = $call[static::EMPLOYEE_CALL_TAKER];

            return new Data\CompleteCall(
                $call[static::UUID],
                \array_key_exists(static::PARENT_UUID, $call) ? $call[static::PARENT_UUID] : null,
                new Enum\Direction($call[static::DIRECTION]),
                new Data\Employee(
                    $caller[static::ID],
                    $caller[static::EMPLOYEE_NUMBER],
                    $caller[static::DISPLAY_NAME],
                    $caller[static::TYPE]
                ),
                !\is_null($employeeCallTaker)
                    ? new Data\Employee(
                        $employeeCallTaker[static::ID],
                        $employeeCallTaker[static::EMPLOYEE_NUMBER],
                        $employeeCallTaker[static::DISPLAY_NAME],
                        $employeeCallTaker[static::TYPE]
                    )
                    : null,
                Carbon::createFromTimestamp($call[static::END_AT]),
                $call[static::SUBJECT_NUMBER],
                $call[static::SUBJECT_NAME],
                $call[static::DISPOSITION],
                $call[static::TRUNK],
                $call[static::BILL_SECS],
                $call[static::DURATION],
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
