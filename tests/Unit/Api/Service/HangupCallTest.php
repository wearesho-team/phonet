<?php

namespace Wearesho\Phonet\Tests\Unit\Api\Service;

use GuzzleHttp\Psr7\Request;

/**
 * Class HangupCallTest
 * @package Wearesho\Phonet\Tests\Unit\Api\Service
 */
class HangupCallTest extends TestCase
{
    protected const UUID = 'test-uuid';

    protected function method(): string
    {
        return 'hangupCall';
    }

    protected function arguments(): array
    {
        return [static::UUID];
    }

    protected function api(): string
    {
        return "rest/calls/active/" . static::UUID . "/hangup";
    }

    public function testSuccessProvide(): void
    {
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse(null)
        );

        $this->invokeMethod();

        $this->checkAuthBody($this->fetchAuthRequest(false));
        $this->checkRequest(
            $this->fetchSentRequest(false)
        );
    }

    public function testSuccessForceProvide(): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::EXPIRED_SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse(null)
        );

        $this->invokeMethod();

        $this->checkAuthBody($this->fetchAuthRequest(true));
        $this->checkRequest(
            $this->fetchSentRequest(true)
        );
    }

    public function testSuccessForceProvideWithCache(): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getSuccessRestResponse(null)
        );

        $this->invokeMethod();

        $this->checkRequest(
            $this->fetchSentRequest(false, true)
        );
    }

    protected function checkRequest(Request $request)
    {
        $this->assertEmpty($request->getBody()->getContents());
        $this->checkMethodGet($request);
        $this->checkCookieHeader($request, static::SESSION_ID);
        $this->checkApi($request, "/{$this->api()}");
        $this->checkCachedResponse($this->getCacheKey());
    }
}
