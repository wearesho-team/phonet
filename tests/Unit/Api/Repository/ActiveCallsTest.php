<?php

namespace Wearesho\Phonet\Tests\Unit\Api\Repository;

use Wearesho\Phonet;

/**
 * Class ActiveCallsTest
 * @package Wearesho\Phonet\Tests\Unit\Api\Repository
 */
class ActiveCallsTest extends TestCase
{
    protected const REST = '/rest/calls/active/v3';

    public function testSuccessProvidedRequest(): Phonet\Data\Collection\ActiveCall
    {
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse($this->getActiveCallsJson())
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $activeCalls = $this->repository->activeCalls();

        $this->checkAuthBody($this->fetchAuthRequest(false));

        $sentRequest = $this->fetchSentRequest(false);

        $this->assertEmpty($sentRequest->getBody()->getContents());
        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, static::REST);
        $this->checkCachedResponse($this->getCacheKey());

        return $activeCalls;
    }

    public function testForceProvideWithExpiredCache(): Phonet\Data\Collection\ActiveCall
    {
        $key = $this->getCacheKey();
        $this->presetCache($key, $this->createCookie(static::EXPIRED_SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse($this->getActiveCallsJson())
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $activeCalls = $this->repository->activeCalls();

        $sentRequest = $this->fetchSentRequest(true);

        $this->assertEmpty($sentRequest->getBody()->getContents());
        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, static::REST);
        $this->checkCachedResponse($key);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->createCookie(static::SESSION_ID), $this->cache->get($key));

        return $activeCalls;
    }

    public function testSuccessForceProvideWithCache(): Phonet\Data\Collection\ActiveCall
    {
        $key = $this->getCacheKey();
        $this->presetCache($key, $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getSuccessRestResponse($this->getActiveCallsJson())
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $activeCalls = $this->repository->activeCalls();

        $sentRequest = $this->fetchSentRequest(false, true);

        $this->assertEmpty($sentRequest->getBody()->getContents());
        $this->checkMethodGet($sentRequest);
        $this->checkCookieHeader($sentRequest, static::SESSION_ID);
        $this->checkApi($sentRequest, static::REST);
        $this->checkCachedResponse($key);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->createCookie(static::SESSION_ID), $this->cache->get($key));

        return $activeCalls;
    }

    /**
     * @depends testSuccessProvidedRequest
     * @depends testForceProvideWithExpiredCache
     * @depends testSuccessForceProvideWithCache
     *
     * @param Phonet\Data\Collection\ActiveCall $activeCalls
     */
    public function testParseActiveCallsResponse(Phonet\Data\Collection\ActiveCall $activeCalls): void
    {
        $this->assertInstanceOf(Phonet\Data\Collection\ActiveCall::class, $activeCalls);
        $this->assertCount(3, $activeCalls);

        /** @var Phonet\Data\ActiveCall $call */
        $call = $activeCalls[0];

        $this->assertEquals('47a968893984475b8c20e29dec144ce3', $call->getUuid());
        $this->assertNull($call->getParentUuid());
        $this->assertEquals(Phonet\Enum\Direction::OUT(), $call->getDirection());
        $this->assertEquals(Phonet\Enum\Event::DIAL(), $call->getLastEvent());
        $this->assertEquals(1431686100, $call->getDialAt()->timestamp);
        $this->assertNull($call->getBridgeAt());
        $this->assertEquals(36, $call->getEmployeeCaller()->getId());
        $this->assertEquals("001", $call->getEmployeeCaller()->getInternalNumber());
        $this->assertEquals("Иван Иванов", $call->getEmployeeCaller()->getDisplayName());
        $this->assertNull($call->getEmployeeCallTaker());
        /** @var Phonet\Data\Subject $subject */
        $subject = $call->getSubjects()[0];
        $this->assertEquals("6137", $subject->getId());
        $this->assertEquals("Telecom company", $subject->getName());
        $this->assertEquals("+380442249895", $subject->getNumber());
        $this->assertNull($subject->getCompany());
        $this->assertNull($subject->getPriority());
        $this->assertEquals(
            "https://self.phonet.com.ua/features/crm/contacts/edit.jsp#/?id=6137",
            $subject->getUri()
        );
        $this->assertEquals('+380442246595', $call->getTrunkNumber());
        $this->assertEquals('+380442246595', $call->getTrunkName());

        /** @var Phonet\Data\ActiveCall $call */
        $call = $activeCalls[1];

        $this->assertEquals('562aa0bd8d9842cd95e4a581443f2e86', $call->getUuid());
        $this->assertNull($call->getParentUuid());
        $this->assertEquals(Phonet\Enum\Direction::IN(), $call->getDirection());
        $this->assertEquals(Phonet\Enum\Event::BRIDGE(), $call->getLastEvent());
        $this->assertEquals(1431686088, $call->getDialAt()->timestamp);
        $this->assertEquals(1431686100, $call->getBridgeAt()->timestamp);
        $this->assertEquals(36, $call->getEmployeeCaller()->getId());
        $this->assertEquals("001", $call->getEmployeeCaller()->getInternalNumber());
        $this->assertEquals("Иван Иванов", $call->getEmployeeCaller()->getDisplayName());
        $this->assertNull($call->getEmployeeCallTaker());
        /** @var Phonet\Data\Subject $subject */
        $subject = $call->getSubjects()[0];
        $expectSubjectId = "6137";
        $expectSubjectName = "Telecom company";
        $expectSubjectNumber = "+380442249895";
        $expectSubjectUri = "https://self.phonet.com.ua/features/crm/contacts/edit.jsp#/?id=6137";
        $this->assertEquals($expectSubjectId, $subject->getId());
        $this->assertEquals($expectSubjectName, $subject->getName());
        $this->assertEquals($expectSubjectNumber, $subject->getNumber());
        $this->assertNull($subject->getCompany());
        $this->assertNull($subject->getPriority());
        $this->assertEquals($expectSubjectUri, $subject->getUri());
        $this->assertEquals(
            [
                'id' => $expectSubjectId,
                'name' => $expectSubjectName,
                'number' => $expectSubjectNumber,
                'company' => null,
                'priority' => null,
                'uri' => $expectSubjectUri
            ],
            $subject->jsonSerialize()
        );
        $this->assertEquals('+380442246595', $call->getTrunkNumber());
        $this->assertEquals('+380442246595', $call->getTrunkName());

        /** @var Phonet\Data\ActiveCall $call */
        $call = $activeCalls[2];
        $this->assertEquals('68333cd7aa94421e89dbc8acfe5027bb', $call->getUuid());
        $this->assertNull($call->getParentUuid());
        $this->assertEquals(Phonet\Enum\Direction::INTERNAL(), $call->getDirection());
        $this->assertEquals(Phonet\Enum\Event::BRIDGE(), $call->getLastEvent());
        $this->assertEquals(1431686001, $call->getDialAt()->timestamp);
        $this->assertEquals(1431686019, $call->getBridgeAt()->timestamp);
        $this->assertEquals(36, $call->getEmployeeCaller()->getId());
        $this->assertEquals("001", $call->getEmployeeCaller()->getInternalNumber());
        $this->assertEquals("Иван Иванов", $call->getEmployeeCaller()->getDisplayName());
        $this->assertEquals(27, $call->getEmployeeCallTaker()->getId());
        $this->assertEquals("002", $call->getEmployeeCallTaker()->getInternalNumber());
        $this->assertEquals("Петр Петров", $call->getEmployeeCallTaker()->getDisplayName());
        $this->assertNull($call->getSubjects());
        $this->assertEquals('+380442246595', $call->getTrunkNumber());
        $this->assertEquals('+380442246595', $call->getTrunkName());
    }

    /**
     * @dataProvider requestExceptionProvider()
     *
     * @param int $badStatusCode
     * @param string $exceptionMessage
     */
    public function testUnexpectedRestException(int $badStatusCode, string $exceptionMessage): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getResponse($badStatusCode, $exceptionMessage)
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionMessage('Force auth provide for api [rest/calls/active/v3] failed');
        $this->expectExceptionCode($badStatusCode);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->repository->activeCalls();
    }

    /**
     * @dataProvider requestExceptionProvider()
     *
     * @param int $badStatusCode
     * @param string $exceptionMessage
     */
    public function testUnexpectedAuthProviderException(int $badStatusCode, string $exceptionMessage): void
    {
        $this->mock->append(
            $this->getResponse($badStatusCode, $exceptionMessage)
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->expectExceptionCode($badStatusCode);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->repository->activeCalls();
    }

    protected function getActiveCallsJson(): string
    {
        return $this->getJson('ActiveCalls');
    }
}
