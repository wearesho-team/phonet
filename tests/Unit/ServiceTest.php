<?php

namespace Wearesho\Phonet\Tests\Unit;

use chillerlan\SimpleCache;
use PHPUnit\Framework\TestCase;
use GuzzleHttp;
use Wearesho\Phonet;

/**
 * Class ServiceTest
 * @package Wearesho\Phonet\Tests\Unit
 */
class ServiceTest extends TestCase
{
    protected const DOMAIN = 'test.phonet.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var array */
    protected $container;

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var Phonet\ServiceInterface */
    protected $service;

    /** @var SimpleCache\Cache */
    protected $cache;

    /** @var Phonet\Authorization\ConfigInterface */
    protected $config;

    protected function setUp(): void
    {
        $this->container = [];
        $history = GuzzleHttp\Middleware::history($this->container);
        $this->mock = new GuzzleHttp\Handler\MockHandler();
        $stack = GuzzleHttp\HandlerStack::create($this->mock);
        $stack->push($history);
        $client = new GuzzleHttp\Client([
            'handler' => $stack,
        ]);
        $this->cache = new SimpleCache\Cache(new SimpleCache\Drivers\MemoryCacheDriver());
        $this->config = new Phonet\Authorization\Config(static::DOMAIN, static::API_KEY);

        $this->service = new Phonet\Service(
            $client,
            $this->config,
            new Phonet\Authorization\CacheProvider($this->cache, $client)
        );
    }

    public function testSuccessMakeCall(): void
    {
        $uuid = 'test-uuid';
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(200, [], \json_encode(['uuid' => $uuid]))
        );

        $callerNumber = 'caller';
        $callTaker = 'taker';
        $responseUuid = $this->service->makeCall($callerNumber, $callTaker);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/user/makeCall',
            (string)$sentRequest->getUri()
        );
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['legExt' => $callerNumber, 'otherLegNum' => $callTaker]),
            (string)$sentRequest->getBody()
        );
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        $this->assertTrue($this->cache->has($cacheKey));
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );

        $this->assertEquals(
            $uuid,
            $responseUuid
        );
    }

    public function testForceProvideForMakeCall(): void
    {
        $this->cache->set(
            "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey()),
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain())
        );
        $uuid = 'test-uuid';
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id-2'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(200, [], \json_encode(['uuid' => $uuid]))
        );

        $callerNumber = 'caller';
        $callTaker = 'taker';
        $responseUuid = $this->service->makeCall($callerNumber, $callTaker);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[2]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id-2"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . '/rest/user/makeCall',
            (string)$sentRequest->getUri()
        );
        $this->assertJsonStringEqualsJsonString(
            \json_encode(['legExt' => $callerNumber, 'otherLegNum' => $callTaker]),
            (string)$sentRequest->getBody()
        );
        $cacheKey = "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey());
        $this->assertTrue($this->cache->has($cacheKey));
        $this->assertEquals(
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id-2'], $this->config->getDomain()),
            $this->cache->get($cacheKey)
        );

        $this->assertEquals($uuid, $responseUuid);
    }

    public function testUnexpectedExceptionForMakeCall(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, [
                'set-cookie' => [
                    'JSESSIONID' => 'test-id-2'
                ]
            ]),
            new GuzzleHttp\Psr7\Response(400, [], "Some error")
        );

        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->expectExceptionMessage('Some error');
        $this->expectExceptionCode(400);

        $this->service->makeCall('test-1', 'test-2');
    }

    public function testUnexpectedExceptionWithAuthForMakeCall(): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(404, [], 'Some error')
        );

        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->expectExceptionMessage('Some error');
        $this->expectExceptionCode(404);

        $this->service->makeCall('test-1', 'test-2');
    }

    public function testSuccessActiveCalls(): Phonet\Data\Collection\ActiveCall
    {
        $activeCallsJson = \file_get_contents(\dirname(__DIR__) . '/Mock/ActiveCalls.json');
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $activeCallsJson)
        );

        $activeCalls = $this->service->activeCalls();

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

        $activeCalls = $this->service->activeCalls();

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
        $this->assertTrue($this->cache->has($cacheKey));
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
}
