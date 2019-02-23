<?php

namespace Wearesho\Phonet\Tests\Unit\Api\Service;

use GuzzleHttp;

/**
 * Class MakeCallTest
 * @package Wearesho\Phonet\Tests\Unit\Api\Service
 */
class MakeCallTest extends TestCase
{
    protected const OPERATOR = 'test-operator-number';
    protected const TARGET = 'test-target-number';
    protected const UUID = 'test-uuid';

    protected function method(): string
    {
        return 'makeCall';
    }

    protected function arguments(): array
    {
        return [static::OPERATOR, static::TARGET];
    }

    protected function api(): string
    {
        return 'rest/user/makeCall';
    }

    public function testSuccessProvide(): void
    {
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse(static::UUID)
        );

        $this->invokeMethod();

        $this->checkAuthBody($this->fetchAuthRequest(false));
        $this->checkRequest(
            $this->fetchSentRequest(false)
        );
        $this->checkResponse(
            $this->fetchRestResponse(false)
        );
    }

    public function testSuccessForceProvide(): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::EXPIRED_SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse(static::UUID)
        );

        $this->invokeMethod();

        $this->checkAuthBody($this->fetchAuthRequest(true));
        $this->checkRequest(
            $this->fetchSentRequest(true)
        );
        $this->checkResponse(
            $this->fetchRestResponse(true)
        );
    }

    public function testSuccessForceProvideWithCache(): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getSuccessRestResponse(static::UUID)
        );

        $this->invokeMethod();

        $this->checkRequest(
            $this->fetchSentRequest(false, true)
        );
        $this->checkResponse(
            $this->fetchRestResponse(false, true)
        );
    }

    protected function getSuccessRestResponse(?string $body): GuzzleHttp\Psr7\Response
    {
        return parent::getSuccessRestResponse(\json_encode(['uuid' => $body]));
    }

    protected function checkRequest(GuzzleHttp\Psr7\Request $request): void
    {
        $this->checkMethodPost($request);
        $this->checkCookieHeader($request, static::SESSION_ID);
        $this->checkApi($request, "/{$this->api()}");
        $this->checkCachedResponse($this->getCacheKey());

        $this->assertEquals(
            [
                'legExt' => static::OPERATOR,
                'otherLegNum' => static::TARGET,
            ],
            \json_decode((string)$request->getBody(), true)
        );
    }

    protected function checkResponse(GuzzleHttp\Psr7\Response $response): void
    {
        $this->assertEquals(
            ['uuid' => static::UUID],
            \json_decode((string)$response->getBody(), true)
        );
    }

    protected function fetchRestResponse(bool $forced, bool $withoutAuth = false): GuzzleHttp\Psr7\Response
    {
        return $this->container[(int)$forced + 1 - (int)$withoutAuth]['response'];
    }
}
