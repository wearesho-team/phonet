<?php

namespace Wearesho\Phonet\Tests\Unit;

use Carbon\Carbon;
use GuzzleHttp;
use Wearesho\Phonet;

/**
 * Class RepositoryTest
 * @package Wearesho\Phonet\Tests\Unit
 */
class RepositoryTest extends ModelTestCase
{
    /** @var Phonet\Repository */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Phonet\Repository($this->config);
    }

    public function testSuccessActiveCalls(): Phonet\Data\Collection\ActiveCall
    {
        $activeCallsJson = \file_get_contents(\dirname(__DIR__) . '/Mock/ActiveCalls.json');
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $activeCallsJson)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $activeCalls = $this->repository->activeCalls();

        $authRequest = $this->container[0]['request'];
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['domain' => static::DOMAIN, 'apiKey' => static::API_KEY]),
            (string)$authRequest->getBody()
        );

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/calls/active/v3',
            (string)$sentRequest->getUri()
        );

        return $activeCalls;
    }

    public function testForceProvideForActiveCalls(): Phonet\Data\Collection\ActiveCall
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache->set(
            "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey()),
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain())
        );
        $activeCallsJson = \file_get_contents(\dirname(__DIR__) . '/Mock/ActiveCalls.json');
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $activeCallsJson)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $activeCalls = $this->repository->activeCalls();

        $authRequest = $this->container[1]['request'];
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['domain' => static::DOMAIN, 'apiKey' => static::API_KEY]),
            (string)$authRequest->getBody()
        );

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[2]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/calls/active/v3',
            (string)$sentRequest->getUri()
        );
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($cacheKey));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );

        return $activeCalls;
    }

    /**
     * @depends testSuccessActiveCalls
     * @depends testForceProvideForActiveCalls
     *
     * @param Phonet\Data\Collection\ActiveCall $activeCalls
     */
    public function testActiveCalls(Phonet\Data\Collection\ActiveCall $activeCalls): void
    {
        $this->assertInstanceOf(Phonet\Data\Collection\ActiveCall::class, $activeCalls);
        $this->assertCount(3, $activeCalls);

        /** @var Phonet\Data\ActiveCall $call */
        $call = $activeCalls[0];

        $this->assertEquals('47a968893984475b8c20e29dec144ce3', $call->getUuid());
        $this->assertNull($call->getParentUuid());
        $this->assertEquals(Phonet\Enum\Direction::OUT(), $call->getDirection());
        $this->assertEquals(Phonet\Enum\LastEvent::DIAL(), $call->getLastEvent());
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
        $this->assertEquals(Phonet\Enum\LastEvent::BRIDGE(), $call->getLastEvent());
        $this->assertEquals(1431686088, $call->getDialAt()->timestamp);
        $this->assertEquals(1431686100, $call->getBridgeAt()->timestamp);
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
        $call = $activeCalls[2];
        $this->assertEquals('68333cd7aa94421e89dbc8acfe5027bb', $call->getUuid());
        $this->assertNull($call->getParentUuid());
        $this->assertEquals(Phonet\Enum\Direction::INTERNAL(), $call->getDirection());
        $this->assertEquals(Phonet\Enum\LastEvent::BRIDGE(), $call->getLastEvent());
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

    public function testSuccessMissedCalls(): Phonet\Data\Collection\CompleteCall
    {
        $missedCalls = \file_get_contents(\dirname(__DIR__) . '/Mock/MissedCalls.json');
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $missedCalls)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $missedCalls = $this->repository->missedCalls(
            Carbon::now(),
            Carbon::now()->addMinute(1),
            new Phonet\Data\Collection\Direction([
                Phonet\Enum\Direction::OUT(),
            ]),
            10,
            0
        );

        $authRequest = $this->container[0]['request'];
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['domain' => static::DOMAIN, 'apiKey' => static::API_KEY]),
            (string)$authRequest->getBody()
        );

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/calls/missed.api',
            (string)$sentRequest->getUri()
        );

        return $missedCalls;
    }

    public function testForceProvideForSuccessMissedCalls(): Phonet\Data\Collection\CompleteCall
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache->set(
            "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey()),
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain())
        );
        $missedCalls = \file_get_contents(\dirname(__DIR__) . '/Mock/MissedCalls.json');
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $missedCalls)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $missedCalls = $this->repository->missedCalls(
            Carbon::now(),
            Carbon::now()->addMinute(1),
            new Phonet\Data\Collection\Direction([
                Phonet\Enum\Direction::OUT(),
            ]),
            10,
            0
        );

        $authRequest = $this->container[1]['request'];
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['domain' => static::DOMAIN, 'apiKey' => static::API_KEY]),
            (string)$authRequest->getBody()
        );

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[2]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/calls/missed.api',
            (string)$sentRequest->getUri()
        );
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($cacheKey));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );

        return $missedCalls;
    }

    public function testUnexpectedExceptionForMissedCalls(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id-3'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(400, [], "Some error")
        );

        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->expectExceptionMessage('Some error');
        $this->expectExceptionCode(400);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->repository->missedCalls(
            Carbon::now(),
            Carbon::now()->addMinute(1),
            new Phonet\Data\Collection\Direction([
                Phonet\Enum\Direction::OUT(),
            ]),
            10,
            0
        );
    }

    public function testFailedAuthorizationForMissedCalls(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(404, [], 'Some error')
        );

        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->expectExceptionMessage('Some error');
        $this->expectExceptionCode(404);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->repository->missedCalls(
            Carbon::now(),
            Carbon::now()->addMinute(1),
            new Phonet\Data\Collection\Direction([
                Phonet\Enum\Direction::OUT(),
            ]),
            10,
            0
        );
    }

    public function testSuccessCompanyCalls(): Phonet\Data\Collection\CompleteCall
    {
        $companyCalls = \file_get_contents(\dirname(__DIR__) . '/Mock/CompanyCalls.json');
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $companyCalls)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $companyCalls = $this->repository->companyCalls(
            Carbon::now(),
            Carbon::now()->addMinute(1),
            new Phonet\Data\Collection\Direction([
                Phonet\Enum\Direction::OUT(),
            ]),
            10,
            0
        );

        $authRequest = $this->container[0]['request'];
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['domain' => static::DOMAIN, 'apiKey' => static::API_KEY]),
            (string)$authRequest->getBody()
        );

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/calls/company.api',
            (string)$sentRequest->getUri()
        );

        return $companyCalls;
    }

    public function testForceProvideForCompanyCalls(): Phonet\Data\Collection\CompleteCall
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache->set(
            "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey()),
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain())
        );
        $companyCalls = \file_get_contents(\dirname(__DIR__) . '/Mock/CompanyCalls.json');
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $companyCalls)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $companyCalls = $this->repository->companyCalls(
            Carbon::now(),
            Carbon::now()->addMinute(1),
            new Phonet\Data\Collection\Direction([
                Phonet\Enum\Direction::OUT(),
            ]),
            10,
            0
        );

        $authRequest = $this->container[1]['request'];
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['domain' => static::DOMAIN, 'apiKey' => static::API_KEY]),
            (string)$authRequest->getBody()
        );

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[2]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/calls/company.api',
            (string)$sentRequest->getUri()
        );
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertTrue($this->cache->has($cacheKey));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );

        return $companyCalls;
    }

    /**
     * @depends testSuccessMissedCalls
     * @depends testForceProvideForSuccessMissedCalls
     * @depends testSuccessCompanyCalls
     * @depends testForceProvideForCompanyCalls
     *
     * @param Phonet\Data\Collection\CompleteCall $completeCalls
     */
    public function testParseCompleteCalls(Phonet\Data\Collection\CompleteCall $completeCalls): void
    {
        $this->assertCount(2, $completeCalls);

        /** @var Phonet\Data\CompleteCall $missedCall */
        $missedCall = $completeCalls[0];

        $this->assertEquals("d267486f-a539-45dd-c5f5-e735a5870b80", $missedCall->getParentUuid());
        $this->assertEquals("f457486f-a539-45dd-c5f5-e735a5870b92", $missedCall->getUuid());
        $this->assertEquals(1435319298470, $missedCall->getEndAt()->timestamp);
        $this->assertEquals(Phonet\Enum\Direction::INTERNAL(), $missedCall->getDirection());
        $this->assertNull($missedCall->getSubjectName());
        $this->assertNull($missedCall->getSubjectNumber());
        $employeeCaller = $missedCall->getEmployeeCaller();
        $this->assertEquals(36, $employeeCaller->getId());
        $this->assertEquals(1, $employeeCaller->getType());
        $this->assertEquals("Васильев Андрей", $employeeCaller->getDisplayName());
        $this->assertEquals("001", $employeeCaller->getInternalNumber());
        $employeeCallTaker = $missedCall->getEmployeeCallTaker();
        $this->assertEquals(19, $employeeCallTaker->getId());
        $this->assertEquals(1, $employeeCallTaker->getType());
        $this->assertEquals("Operator 4", $employeeCallTaker->getDisplayName());
        $this->assertEquals("004", $employeeCallTaker->getInternalNumber());
        $this->assertEquals(3, $missedCall->getBillSecs());
        $this->assertEquals(4, $missedCall->getDuration());
        $this->assertEquals(0, $missedCall->getDisposition());
        $this->assertEquals(null, $missedCall->getTransferHistory());
        $this->assertEquals(
            "https://podium.betell.com.ua/rest/public/calls/f457486f-a539-45dd-c5f5-e735a5870b92/audio ",
            $missedCall->getAudioRecUrl()
        );
        $this->assertNull($missedCall->getTrunk());
    }
}
