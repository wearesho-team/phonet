<?php

namespace Wearesho\Phonet\Tests\Unit\Api\Repository;

use Carbon\Carbon;
use Wearesho\Phonet;

/**
 * Class TestCase
 * @package Wearesho\Phonet\Tests\Unit\Api\Repository
 */
class TestCase extends Phonet\Tests\Unit\Api\TestCase
{
    protected const FROM = '2018-03-12';
    protected const TO = '2019-03-12';

    /** @var Phonet\Repository */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Phonet\Repository($this->sender);
    }

    /**
     * @dataProvider repositoryMethodsProvider
     *
     * @param string $api
     * @param string $method
     * @param array $arguments
     */
    public function testJsonParseExceptionWithCache(string $api, string $method, array $arguments): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getSuccessRestResponse('invalid body')
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionMessage("[$api] return response with body that have content not json");

        $this->repository->{$method}(...$arguments);
    }

    public function repositoryMethodsProvider(): array
    {
        $directions = new Phonet\Call\Direction\Collection([
            Phonet\Call\Direction::OUT()
        ]);

        return [
            ['rest/users', 'users', []],
            ['rest/calls/active/v3', 'activeCalls', []],
            ['rest/calls/company.api', 'companyCalls', [$this->createDateFrom(), $this->createDateTo(), $directions]],
            ['rest/calls/missed.api', 'missedCalls', [$this->createDateFrom(), $this->createDateTo(), $directions]],
            ['rest/calls/users.api', 'usersCalls', [$this->createDateFrom(), $this->createDateTo(), $directions]],
        ];
    }

    protected function parseCompleteCalls(Phonet\Call\Complete\Collection $completeCalls): void
    {
        $this->assertCount(2, $completeCalls);

        /** @var Phonet\Call\Complete $missedCall */
        $missedCall = $completeCalls[0];

        $this->assertEquals("d267486f-a539-45dd-c5f5-e735a5870b80", $missedCall->getParentUuid());
        $this->assertEquals("f457486f-a539-45dd-c5f5-e735a5870b92", $missedCall->getUuid());
        $this->assertEquals(1435319298470, $missedCall->getEndAt()->timestamp);
        $this->assertEquals(Phonet\Call\Direction::INTERNAL(), $missedCall->getDirection());
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
        $this->assertEquals(0, $missedCall->getStatus()->getValue());
        $this->assertEquals(null, $missedCall->getTransferHistory());
        $this->assertEquals(
            "https://podium.betell.com.ua/rest/public/calls/f457486f-a539-45dd-c5f5-e735a5870b92/audio ",
            $missedCall->getAudioRecUrl()
        );
        $this->assertNull($missedCall->getTrunk());
    }

    protected function createDateFrom(): Carbon
    {
        return $this->createDate(static::FROM);
    }

    protected function createDateTo(): Carbon
    {
        return $this->createDate(static::TO);
    }

    protected function createDate(string $date): Carbon
    {
        return Carbon::make($date);
    }

    protected function getCompleteCallsJson(): string
    {
        return $this->getJson('CompleteCalls');
    }

    protected function getJson(string $file): string
    {
        return \file_get_contents(\dirname(__DIR__, 3) . "/Mock/{$file}.json");
    }
}
