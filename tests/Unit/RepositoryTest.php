<?php

namespace Wearesho\Phonet\Tests\Unit;

use Carbon\Carbon;
use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet;
use chillerlan\SimpleCache;

/**
 * Class RepositoryTest
 * @package Wearesho\Phonet\Tests\Unit
 */
class RepositoryTest extends TestCase
{
    protected const DOMAIN = 'test.phonet.com.ua';
    protected const API_KEY = 'test-api-key';

    /** @var array */
    protected $container;

    /** @var GuzzleHttp\Handler\MockHandler */
    protected $mock;

    /** @var SimpleCache\Cache */
    protected $cache;

    /** @var Phonet\ConfigInterface */
    protected $config;

    /** @var Phonet\Sender */
    protected $sender;

    /** @var Phonet\Repository */
    protected $repository;

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
        $this->config = new Phonet\Config(
            static::DOMAIN,
            static::API_KEY
        );
        $this->sender = new Phonet\Sender(
            $client,
            $this->config,
            new Phonet\Authorization\CacheProvider($this->cache, $client)
        );
        $this->repository = new Phonet\Repository($this->sender);
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

        /** @var GuzzleHttp\Psr7\Request $authRequest */
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

        /** @var GuzzleHttp\Psr7\Request $authRequest */
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

    /**
     * @dataProvider CompleteCallsProvider
     *
     * @param string $api
     * @param string $method
     */
    public function testSuccessCompleteCalls(string $api, string $method): void
    {
        $this->mockSuccess($this->getCompleteCallsJson());
        $calls = $this->getCompleteCallsByMethod($method);

        /** @var GuzzleHttp\Psr7\Request $authRequest */
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
            'https://' . static::DOMAIN . $api,
            (string)$sentRequest->getUri()
        );

        $this->parseCompleteCalls($calls);
    }

    /**
     * @dataProvider completeCallsProvider
     */
    public function testForceProvideCompleteCalls(string $api, string $method): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->cache->set(
            "phonet.authorization." . sha1($this->config->getDomain() . $this->config->getApiKey()),
            GuzzleHttp\Cookie\CookieJar::fromArray(['JSESSIONID' => 'test-id'], $this->config->getDomain())
        );
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(403, [], 'Some error'),
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $this->getCompleteCallsJson())
        );

        $calls = $this->getCompleteCallsByMethod($method);

        /** @var GuzzleHttp\Psr7\Request $authRequest */
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
            'https://' . static::DOMAIN . $api,
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

        $this->parseCompleteCalls($calls);
    }

    /**
     * @dataProvider completeCallsProvider
     */
    public function testUnexpectedExceptionAfterForceProvide(string $api, string $method): void
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

        $this->getCompleteCallsByMethod($method);

        /** @var GuzzleHttp\Psr7\Request $sentRequest */
        $sentRequest = $this->container[1]['request'];

        $this->assertEquals(
            ["JSESSIONID=test-id"],
            $sentRequest->getHeader('Cookie')
        );
        $this->assertEquals(
            'https://' . static::DOMAIN . $api,
            (string)$sentRequest->getUri()
        );
    }

    /**
     * @dataProvider completeCallsProvider
     */
    public function testAuthorizationException(string $api, string $method): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(404, [], 'Some error')
        );

        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->expectExceptionMessage('Some error');
        $this->expectExceptionCode(404);

        $this->getCompleteCallsByMethod($method);

        /** @var GuzzleHttp\Psr7\Request $request */
        $request = (string)$this->container[1]['request'];

        $this->assertEquals(
            'https://' . static::DOMAIN . $api,
            $request->getUri()
        );
    }

    public function completeCallsProvider(): array
    {
        return [
            ['/rest/calls/company.api', 'companyCalls'],
            ['/rest/calls/users.api', 'usersCalls'],
            ['/rest/calls/missed.api', 'missedCalls'],
        ];
    }

    public function testInvalidResponseBody(): void
    {
        $this->mockSuccess('Not json content');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Response body content not json');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->repository->users();
    }

    /**
     * @dataProvider limitProvider
     */
    public function testInvalidLimit(int $limit, string $method): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']])
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid limit: $limit. It must be in range between 1 and 50");

        $this->getCompleteCallsByMethod($method, $limit);
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
     */
    public function testInvalidOffset(int $offset, string $method): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']])
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid offset: $offset. It can not be less then 0");

        $this->getCompleteCallsByMethod($method, 1, $offset);
    }

    public function offsetProvider(): array
    {
        return [
            [-20, 'companyCalls'],
            [-100, 'usersCalls'],
            [-1, 'missedCalls'],
        ];
    }

    public function testSuccessUsers(): void
    {
        $this->mockSuccess($this->getUsersJson());
        /** @noinspection PhpUnhandledExceptionInspection */
        $users = $this->repository->users();

        $this->parseUsers($users);

        /** @var GuzzleHttp\Psr7\Request $authRequest */
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
            'https://' . static::DOMAIN . '/rest/users',
            (string)$sentRequest->getUri()
        );
    }

    protected function parseCompleteCalls(Phonet\Data\Collection\CompleteCall $completeCalls): void
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

    public function parseUsers(Phonet\Data\Collection\Employee $users): void
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

    protected function getCompleteCallsByMethod(
        string $method,
        $limit = 50,
        $offset = 0
    ): Phonet\Data\Collection\CompleteCall {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->repository->{$method}(
            Carbon::now(),
            Carbon::now()->addMinute(1),
            new Phonet\Data\Collection\Direction([
                Phonet\Enum\Direction::OUT(),
            ]),
            $limit,
            $offset
        );
    }

    protected function mockSuccess(string $data): void
    {
        $this->mock->append(
            new GuzzleHttp\Psr7\Response(200, ['set-cookie' => ['JSESSIONID' => 'test-id']]),
            new GuzzleHttp\Psr7\Response(200, [], $data)
        );
    }

    protected function getCompleteCallsJson(): string
    {
        return $this->getJson('CompleteCalls');
    }

    public function getUsersJson(): string
    {
        return $this->getJson('Users');
    }

    public function getJson(string $file): string
    {
        return \file_get_contents(\dirname(__DIR__) . "/Mock/{$file}.json");
    }
}
