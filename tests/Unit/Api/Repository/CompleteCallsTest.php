<?php

namespace Wearesho\Phonet\Tests\Unit\Api\Repository;

use Carbon\Carbon;
use Wearesho\Phonet;

/**
 * Class CompleteCallsTest
 * @package Wearesho\Phonet\Tests\Unit\Api\Repository
 */
class CompleteCallsTest extends TestCase
{
    /**
     * @dataProvider dateProviderFailedCompleteCalls
     *
     * @param string $api
     * @param string $method
     */
    public function testSuccessProvided(string $api, string $method): void
    {
        $from = $this->createDateFrom();
        $to = $this->createDateTo();

        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse($this->getCompleteCallsJson())
        );

        $companyCalls = $this->getCompleteCalls($method, $from, $to);

        $this->checkAuthBody($this->fetchAuthRequest(false));

        $sentRequest = $this->fetchSentRequest(false);

        $this->assertEquals(
            [
                'timeFrom' => $from->timestamp,
                'timeTo' => $to->timestamp,
                'limit' => 50,
                'offset' => 0,
                'directions' => [
                    Phonet\Enum\Direction::OUT,
                ]
            ],
            \json_decode($sentRequest->getBody()->getContents(), true)
        );
        $this->assertEmpty($sentRequest->getBody()->getContents());
        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, "/$api");
        $this->checkCachedResponse($this->getCacheKey());

        $this->parseCompleteCalls($companyCalls);
    }

    /**
     * @dataProvider dateProviderFailedCompleteCalls
     *
     * @param string $api
     * @param string $method
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testForceProvideWithExpiredCache(string $api, string $method): void
    {
        $from = $this->createDateFrom();
        $to = $this->createDateTo();
        $key = $this->getCacheKey();
        $this->presetCache($key, $this->createCookie(static::EXPIRED_SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse($this->getCompleteCallsJson())
        );

        $companyCalls = $this->getCompleteCalls($method, $from, $to);

        $this->checkAuthBody($this->fetchAuthRequest(true));

        $sentRequest = $this->fetchSentRequest(true);

        $this->assertEquals(
            [
                'timeFrom' => $from->timestamp,
                'timeTo' => $to->timestamp,
                'limit' => 50,
                'offset' => 0,
                'directions' => [
                    Phonet\Enum\Direction::OUT,
                ]
            ],
            \json_decode($sentRequest->getBody()->getContents(), true)
        );
        $this->assertEmpty($sentRequest->getBody()->getContents());
        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, "/$api");
        $this->checkCachedResponse($key);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->createCookie(static::SESSION_ID), $this->cache->get($key));

        $this->parseCompleteCalls($companyCalls);
    }

    /**
     * @dataProvider completeCallsProvider
     *
     * @param string $api
     * @param string $method
     */
    public function testForceProvideWithCache(string $api, string $method): void
    {
        $from = $this->createDateFrom();
        $to = $this->createDateTo();
        $key = $this->getCacheKey();
        $this->presetCache($key, $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getSuccessRestResponse($this->getCompleteCallsJson())
        );

        $companyCalls = $this->getCompleteCalls($method, $from, $to);

        $sentRequest = $this->fetchSentRequest(false, true);

        $this->assertEquals(
            [
                'timeFrom' => $from->timestamp,
                'timeTo' => $to->timestamp,
                'limit' => 50,
                'offset' => 0,
                'directions' => [
                    Phonet\Enum\Direction::OUT,
                ]
            ],
            \json_decode($sentRequest->getBody()->getContents(), true)
        );
        $this->assertEmpty($sentRequest->getBody()->getContents());
        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, "/$api");
        $this->checkCachedResponse($key);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->createCookie(static::SESSION_ID), $this->cache->get($key));

        $this->parseCompleteCalls($companyCalls);
    }

    public function completeCallsProvider(): array
    {
        return [
            ['rest/calls/company.api', 'companyCalls'],
            ['rest/calls/users.api', 'usersCalls'],
            ['rest/calls/missed.api', 'missedCalls'],
        ];
    }

    /**
     * @dataProvider dateProviderFailedCompleteCalls()
     *
     * @param string $api
     * @param string $method
     * @param int $badStatusCode
     */
    public function testUnexpectedProvideException(string $api, string $method, int $badStatusCode): void
    {
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getResponse($badStatusCode, 'Error')
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionCode($badStatusCode);
        $this->expectExceptionMessage("Api [$api] failed");

        $this->getCompleteCalls($method, $this->createDateFrom(), $this->createDateTo());
    }

    /**
     * @dataProvider dateProviderFailedCompleteCalls()
     *
     * @param string $api
     * @param string $method
     * @param int $badStatusCode
     */
    public function testUnexpectedExceptionForceProvide(string $api, string $method, int $badStatusCode): void
    {
        $key = $this->getCacheKey();
        $this->presetCache($key, $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getResponse($badStatusCode, 'Error')
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionCode($badStatusCode);
        $this->expectExceptionMessage("Api [$api] with force auth failed");

        $this->getCompleteCalls($method, $this->createDateFrom(), $this->createDateTo());
    }

    /**
     * @dataProvider dateProviderFailedCompleteCalls()
     *
     * @param string $api
     * @param string $method
     * @param int $badStatusCode
     */
    public function testUnexpectedExceptionProvideWithCache(string $api, string $method, int $badStatusCode): void
    {
        $key = $this->getCacheKey();
        $this->presetCache($key, $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getResponse($badStatusCode, 'Error')
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionCode($badStatusCode);
        $this->expectExceptionMessage("Api [$api] failed");

        $this->getCompleteCalls($method, $this->createDateFrom(), $this->createDateTo());
    }

    public function dateProviderFailedCompleteCalls(): array
    {
        $apis = $this->completeCallsProvider();
        $exceptions = $this->requestExceptionProvider();

        $cases = [];

        foreach ($apis as $api) {
            $cases = array_merge($cases, array_map(function ($exceptionCase) use ($api) {
                return array_merge($api, [$exceptionCase[0]]);
            }, $exceptions));
        }

        return $cases;
    }

    /**
     * @dataProvider limitProvider
     *
     * @param int $limit
     * @param string $method
     */
    public function testInvalidLimit(int $limit, string $method): void
    {
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid limit: $limit. It must be in range between 1 and 50");

        $this->getCompleteCalls($method, $this->createDateFrom(), $this->createDateTo(), $limit);
    }

    public function limitProvider(): array
    {
        return [
            [-20, 'companyCalls'],
            [0, 'usersCalls'],
            [51, 'missedCalls'],
        ];
    }

    /**
     * @dataProvider offsetProvider
     *
     * @param int $offset
     * @param string $method
     */
    public function testInvalidOffset(int $offset, string $method): void
    {
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid offset: $offset. It can not be less then 0");

        $this->getCompleteCalls($method, $this->createDateFrom(), $this->createDateTo(), 1, $offset);
    }

    public function offsetProvider(): array
    {
        return [
            [-20, 'companyCalls'],
            [-100, 'usersCalls'],
            [-1, 'missedCalls'],
        ];
    }

    /**
     * @param string $method
     * @param Carbon $from
     * @param Carbon $to
     * @param int $limit
     * @param int $offset
     *
     * @return Phonet\Data\Collection\CompleteCall
     */
    protected function getCompleteCalls(
        string $method,
        Carbon $from,
        Carbon $to,
        int $limit = 50,
        int $offset = 0
    ): Phonet\Data\Collection\CompleteCall {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->repository->{$method}(
            $from,
            $to,
            new Phonet\Data\Collection\Direction([
                Phonet\Enum\Direction::OUT(),
            ]),
            $limit,
            $offset
        );
    }
}
