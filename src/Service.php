<?php

namespace Wearesho\Phonet;

use Carbon\Carbon;
use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Service
 * @package Wearesho\Phonet
 */
class Service implements ServiceInterface
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

    /** @var GuzzleHttp\ClientInterface */
    protected $client;

    /** @var Authorization\ConfigInterface */
    protected $config;

    /** @var Authorization\ProviderInterface|Authorization\CacheProviderInterface */
    protected $authProvider;

    public function __construct(
        GuzzleHttp\ClientInterface $client,
        Authorization\ConfigInterface $config,
        Authorization\ProviderInterface $provider
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->authProvider = $provider;
    }

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

    /**
     * {@inheritdoc}
     *
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function activeCalls(): Data\Collection\ActiveCall
    {
        $request = new GuzzleHttp\Psr7\Request('GET', $this->formUri("rest/calls/active/v3"));

        return new Data\Collection\ActiveCall(\array_map(function (array $call): Data\ActiveCall {
            $bridgeAt = $call[static::BRIDGE_AT];
            $caller = $call[static::CALLER];
            $employeeCallTaker = $call[static::EMPLOYEE_CALL_TAKER];
            $subjects = $call[static::SUBJECT_COLLECTION];

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
                new Data\Collection\Subject(\array_map(function (array $subject): Data\Subject {
                    return new Data\Subject(
                        $subject[static::ID],
                        $subject[static::NAME],
                        $subject[static::NUMBER],
                        $subject[static::COMPANY_NAME],
                        $subject[static::URL],
                        isset($subject[static::PRIORITY]) ? $subject[static::PRIORITY] : null
                    );
                }, $subjects)),
                $call[static::TRUNK_NUMBER],
                $call[static::TRUNK_NAME]
            );
        }, \json_decode((string)$this->send($request)->getBody(), true)));
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
        $this->validateLimit($limit);
        $this->validateOffset($offset);

        return $this->parseCompletedCalls(
            (string)$this->send($this->createGetRequest(
                "rest/calls/company.api",
                $from,
                $to,
                $directions,
                $limit,
                $offset
            ))->getBody()
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
        $this->validateLimit($limit);
        $this->validateOffset($offset);

        return $this->parseCompletedCalls(
            (string)$this->send($this->createGetRequest(
                "rest/calls/missed.api",
                $from,
                $to,
                $directions,
                $limit,
                $offset
            ))->getBody()
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
        $this->validateLimit($limit);
        $this->validateOffset($offset);

        return $this->parseCompletedCalls(
            (string)$this->send($this->createGetRequest(
                "rest/calls/users.api",
                $from,
                $to,
                $directions,
                $limit,
                $offset
            ))->getBody()
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

    protected function createGetRequest(
        string $api,
        \DateTimeInterface $from,
        \DateTimeInterface $to,
        ?Data\Collection\Direction $directions,
        int $limit,
        int $offset
    ): GuzzleHttp\Psr7\Request {
        $directions = $directions
            ?
            [
                'directions' => $directions->map(function (Enum\Direction $direction): int {
                    return $direction->getValue();
                })
            ]
            : [];

        return new GuzzleHttp\Psr7\Request(
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
    }

    protected function parseCompletedCalls(string $data): Data\Collection\CompleteCall
    {
        return new Data\Collection\CompleteCall(\array_map(function (array $call): Data\CompleteCall {
            $caller = $call[static::CALLER];
            $employeeCallTaker = $call[static::EMPLOYEE_CALL_TAKER];

            return new Data\CompleteCall(
                $call[static::UUID],
                $call[static::PARENT_UUID],
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

    protected function formUri(string $api): string
    {
        return "https://{$this->config->getDomain()}/{$api}";
    }

    /**
     * @param GuzzleHttp\Psr7\Request $request
     *
     * @return ResponseInterface
     * @throws GuzzleHttp\Exception\GuzzleException
     */
    protected function send(GuzzleHttp\Psr7\Request $request): ResponseInterface
    {
        $headers = [
            GuzzleHttp\RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
        ];

        try {
            return $this->client
                ->send(
                    $request,
                    \array_merge([
                        GuzzleHttp\RequestOptions::COOKIES => $this->authProvider->provide($this->config)
                    ], $headers)
                );
        } catch (GuzzleHttp\Exception\ClientException $exception) {
            if ($exception->hasResponse()
                && $exception->getResponse()->getStatusCode() === static::STATUS_FORBIDDEN
                && $this->authProvider instanceof Authorization\CacheProviderInterface
            ) {
                return $this->client
                    ->send(
                        $request,
                        \array_merge([
                            GuzzleHttp\RequestOptions::COOKIES => $this->authProvider->forceProvide($this->config)
                        ], $headers)
                    );
            }

            throw $exception;
        }
    }

    protected function validateLimit(int $limit): void
    {
        if ($limit > 50 || $limit < 1) {
            throw new \InvalidArgumentException('Limit must be in range between 1 and 50');
        }
    }

    protected function validateOffset(int $offset): void
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException('Offset can not be less then 0');
        }
    }
}
