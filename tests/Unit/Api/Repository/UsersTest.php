<?php

namespace Wearesho\Phonet\Tests\Unit\Api\Repository;

use Wearesho\Phonet;

/**
 * Class UsersTest
 * @package Wearesho\Phonet\Tests\Unit\Api\Repository
 */
class UsersTest extends TestCase
{
    public function testSuccessProvided(): void
    {
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse($this->getUsersJson())
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $users = $this->repository->users();

        $this->parseUsers($users);
        $this->checkAuthBody($this->fetchAuthRequest(false));

        $sentRequest = $this->fetchSentRequest(false);

        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, '/rest/users');
        $this->checkCachedResponse($this->getCacheKey());
    }

    public function testSuccessForceProvided(): void
    {
        $key = $this->getCacheKey();
        $this->presetCache($key, $this->createCookie(static::EXPIRED_SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse($this->getUsersJson())
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $users = $this->repository->users();

        $this->parseUsers($users);
        $this->checkAuthBody($this->fetchAuthRequest(true));

        $sentRequest = $this->fetchSentRequest(true);

        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, '/rest/users');
        $this->checkCachedResponse($this->getCacheKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->createCookie(static::SESSION_ID), $this->cache->get($key));
    }

    public function testSuccessForceProvidedWithCache(): void
    {
        $key = $this->getCacheKey();
        $this->presetCache($key, $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getSuccessRestResponse($this->getUsersJson())
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $users = $this->repository->users();

        $this->parseUsers($users);

        $sentRequest = $this->fetchSentRequest(false, true);

        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, '/rest/users');
        $this->checkCachedResponse($this->getCacheKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->createCookie(static::SESSION_ID), $this->cache->get($key));
    }

    /**
     * @dataProvider requestExceptionProvider
     *
     * @param int $badStatusCode
     * @param $exceptionMessage
     */
    public function testUnexpectedRestException(int $badStatusCode, $exceptionMessage): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getResponse($badStatusCode, $exceptionMessage)
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionMessage('Force auth provide for api [rest/users] failed');
        $this->expectExceptionCode($badStatusCode);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->repository->users();
    }

    /**
     * @dataProvider requestExceptionProvider
     *
     * @param int $badStatusCode
     * @param $exceptionMessage
     */
    public function testUnexpectedAuthException(int $badStatusCode, $exceptionMessage): void
    {
        $this->mock->append(
            $this->getResponse($badStatusCode, $exceptionMessage)
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($badStatusCode);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->repository->users();
    }

    protected function parseUsers(Phonet\Data\Collection\Employee $users): void
    {
        $this->assertCount(2, $users);

        $expectData = [
            [
                'id' => 30,
                'displayName' => 'Иван Иванов',
                'ext' => '901',
                'email' => "ivan.ivanov@phonet.com.ua"
            ],
            [
                "id" => 14,
                "displayName" => "Юрий Юрьев",
                "ext" => "990",
                "email" => "yuriy.yuriev@phonet.com.ua"
            ]
        ];

        /**
         * @var int $key
         * @var Phonet\Data\Employee $user
         */
        foreach ($users as $key => $user) {
            $data = $expectData[$key];

            $this->assertEquals($data['id'], $user->getId());
            $this->assertEquals($data['displayName'], $user->getDisplayName());
            $this->assertEquals($data['ext'], $user->getInternalNumber());
            $this->assertEquals($data['email'], $user->getEmail());
        }
    }

    protected function getUsersJson(): string
    {
        return $this->getJson('Users');
    }
}
