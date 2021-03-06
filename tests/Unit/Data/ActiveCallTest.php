<?php

namespace Wearesho\Phonet\Tests\Unit\Data;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Wearesho\Phonet;

/**
 * Class ActiveCallTest
 * @package Wearesho\Phonet\Tests\Unit\Data
 */
class ActiveCallTest extends TestCase
{
    protected const UUID = 'uuid';
    protected const PARENT_UUID = 'parent-uuid';
    protected const DIAL_AT = '2020-03-12';
    protected const BRIDGE_AT = '2020-03-12';
    protected const ID = 10;
    protected const INTERNAL_NUMBER = 'internal-number';
    protected const DISPLAY_NAME = 'display-name';
    protected const TYPE = 1;
    protected const EMAIL = 'email';
    protected const NAME = 'name';
    protected const NUMBER = 'number';
    protected const COMPANY = 'company';
    protected const URI = 'uri';
    protected const PRIORITY = 'priority';
    protected const TRUNK_NUMBER = 'trunk-number';
    protected const TRUNK_NAME = 'trunk-name';
    protected const SUBJECT_ID = 'subject-id';

    /** @var Phonet\Call\Active */
    protected $activeCall;

    protected function setUp(): void
    {
        $this->activeCall = new Phonet\Call\Active(
            static::UUID,
            Carbon::make(static::DIAL_AT),
            Phonet\Call\Direction::IN(),
            Phonet\Call\Event::HANGUP(),
            new Phonet\Employee(
                static::ID,
                static::INTERNAL_NUMBER,
                static::DISPLAY_NAME,
                static::TYPE,
                static::EMAIL
            ),
            static::TRUNK_NUMBER,
            static::TRUNK_NAME,
            static::PARENT_UUID,
            Carbon::make(static::BRIDGE_AT),
            new Phonet\Employee(
                static::ID,
                static::INTERNAL_NUMBER,
                static::DISPLAY_NAME,
                static::TYPE,
                static::EMAIL
            ),
            new Phonet\Subject\Collection([
                new Phonet\Subject(
                    static::SUBJECT_ID,
                    static::NAME,
                    static::NUMBER,
                    static::COMPANY,
                    static::URI,
                    static::PRIORITY
                ),
            ])
        );
    }

    public function testInstance()
    {
        $this->assertInstanceOf(Phonet\Call\Active::class, $this->activeCall);
    }

    /**
     * @depends testInstance
     */
    public function testGetUuid(): void
    {
        $this->assertEquals(static::UUID, $this->activeCall->getUuid());
    }

    /**
     * @depends testGetUuid
     */
    public function testGetParentUuid(): void
    {
        $this->assertEquals(static::PARENT_UUID, $this->activeCall->getParentUuid());
    }

    /**
     * @depends testGetParentUuid
     */
    public function testGetBridgeAt(): void
    {
        $this->assertEquals(
            Carbon::make(static::BRIDGE_AT),
            Carbon::make($this->activeCall->getBridgeAt())
        );
    }

    /**
     * @depends testGetBridgeAt
     */
    public function testGetDialAt(): void
    {
        $this->assertEquals(
            Carbon::make(static::DIAL_AT),
            Carbon::make($this->activeCall->getDialAt())
        );
    }

    /**
     * @depends testGetDialAt
     */
    public function testGetDirection(): void
    {
        $this->assertEquals(Phonet\Call\Direction::IN(), $this->activeCall->getDirection());
    }

    /**
     * @depends testGetDirection
     */
    public function testGetLastEvent(): void
    {
        $this->assertEquals(Phonet\Call\Event::HANGUP(), $this->activeCall->getLastEvent());
    }

    /**
     * @depends testGetLastEvent
     */
    public function testGetEmployeeCaller(): void
    {
        $this->assertEquals(
            new Phonet\Employee(
                static::ID,
                static::INTERNAL_NUMBER,
                static::DISPLAY_NAME,
                static::TYPE,
                static::EMAIL
            ),
            $this->activeCall->getEmployeeCaller()
        );
    }

    /**
     * @depends testGetEmployeeCaller
     */
    public function testGetEmployeeCallTaker(): void
    {
        $this->assertEquals(
            new Phonet\Employee(
                static::ID,
                static::INTERNAL_NUMBER,
                static::DISPLAY_NAME,
                static::TYPE,
                static::EMAIL
            ),
            $this->activeCall->getEmployeeCallTaker()
        );
    }

    /**
     * @depends testGetEmployeeCallTaker
     */
    public function testGetSubjects(): void
    {
        $this->assertEquals(
            new Phonet\Subject\Collection([
                new Phonet\Subject(
                    static::SUBJECT_ID,
                    static::NAME,
                    static::NUMBER,
                    static::COMPANY,
                    static::URI,
                    static::PRIORITY
                ),
            ]),
            $this->activeCall->getSubjects()
        );
    }

    /**
     * @depends testGetSubjects
     */
    public function testGetTrunkNumber(): void
    {
        $this->assertEquals(static::TRUNK_NUMBER, $this->activeCall->getTrunkNumber());
    }

    /**
     * @depends testGetTrunkNumber
     */
    public function testGetTrunkName(): void
    {
        $this->assertEquals(static::TRUNK_NAME, $this->activeCall->getTrunkName());
    }

    /**
     * @depends testGetTrunkName
     */
    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            [
                'uuid' => static::UUID,
                'parentUuid' => static::PARENT_UUID,
                'dialAt' => Carbon::make(static::DIAL_AT),
                'bridgeAt' => Carbon::make(static::BRIDGE_AT),
                'direction' => Phonet\Call\Direction::IN(),
                'lastEvent' => Phonet\Call\Event::HANGUP(),
                'employeeCaller' => new Phonet\Employee(
                    static::ID,
                    static::INTERNAL_NUMBER,
                    static::DISPLAY_NAME,
                    static::TYPE,
                    static::EMAIL
                ),
                'employeeCallTaker' => new Phonet\Employee(
                    static::ID,
                    static::INTERNAL_NUMBER,
                    static::DISPLAY_NAME,
                    static::TYPE,
                    static::EMAIL
                ),
                'subjects' => new Phonet\Subject\Collection([
                    new Phonet\Subject(
                        static::SUBJECT_ID,
                        static::NAME,
                        static::NUMBER,
                        static::COMPANY,
                        static::URI,
                        static::PRIORITY
                    ),
                ]),
                'trunkNumber' => static::TRUNK_NUMBER,
                'trunkName' => static::TRUNK_NAME
            ],
            $this->activeCall->jsonSerialize()
        );
    }
}
