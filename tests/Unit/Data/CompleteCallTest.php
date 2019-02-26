<?php

namespace Wearesho\Phonet\Tests\Unit\Data;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet\Data\CompleteCall;
use Wearesho\Phonet\Data\Employee;
use Wearesho\Phonet\Enum\CompleteCallStatus;
use Wearesho\Phonet\Enum\Direction;

/**
 * Class CompleteCallTest
 * @package Wearesho\Phonet\Tests\Unit\Data
 */
class CompleteCallTest extends TestCase
{
    protected const UUID = 'uuid';
    protected const PARENT_UUID = 'parent-uuid';
    protected const ID = 1;
    protected const INTERNAL_NUMBER = 'internal-number';
    protected const DISPLAY_NAME = 'display-name';
    protected const TYPE = 1;
    protected const EMAIL = 'email';
    protected const SUBJECT_NUMBER = 'subject-number';
    protected const SUBJECT_NAME = 'subject-name';
    protected const DISPOSITION = 10;
    protected const TRUNK = 'trunk';
    protected const BILL_SECS = 10;
    protected const DURATION = 10;
    protected const TRANSFER_HISTORY = 'transfer-history';
    protected const AUDIO_REC_URL = 'audio-rec-url';

    /** @var CompleteCall */
    protected $completeCall;

    protected function setUp(): void
    {
        Carbon::setTestNow(Carbon::now());

        $this->completeCall = new CompleteCall(
            static::UUID,
            Direction::INTERNAL(),
            new Employee(static::ID, static::INTERNAL_NUMBER, static::DISPLAY_NAME, static::TYPE, static::EMAIL),
            Carbon::getTestNow(),
            CompleteCallStatus::TARGET_RESPONDED(),
            static::BILL_SECS,
            static::DURATION,
            static::PARENT_UUID,
            new Employee(static::ID, static::INTERNAL_NUMBER, static::DISPLAY_NAME, static::TYPE, static::EMAIL),
            static::SUBJECT_NUMBER,
            static::SUBJECT_NAME,
            static::TRUNK,
            static::TRANSFER_HISTORY,
            static::AUDIO_REC_URL
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    public function testInstance(): void
    {
        $this->assertInstanceOf(CompleteCall::class, $this->completeCall);
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            [
                'uuid' => static::UUID,
                'parentUuid' => static::PARENT_UUID,
                'direction' => Direction::INTERNAL(),
                'employeeCaller' => new Employee(
                    static::ID,
                    static::INTERNAL_NUMBER,
                    static::DISPLAY_NAME,
                    static::TYPE,
                    static::EMAIL
                ),
                'employeeCallTaker' => new Employee(
                    static::ID,
                    static::INTERNAL_NUMBER,
                    static::DISPLAY_NAME,
                    static::TYPE,
                    static::EMAIL
                ),
                'endAt' => Carbon::getTestNow(),
                'subjectNumber' => static::SUBJECT_NUMBER,
                'subjectName' => static::SUBJECT_NAME,
                'disposition' => CompleteCallStatus::TARGET_RESPONDED(),
                'trunk' => static::TRUNK,
                'billSecs' => static::BILL_SECS,
                'duration' => static::DURATION,
                'transferHistory' => static::TRANSFER_HISTORY,
                'audioRecUrl' => static::AUDIO_REC_URL,
            ],
            $this->completeCall->jsonSerialize()
        );
    }
}
