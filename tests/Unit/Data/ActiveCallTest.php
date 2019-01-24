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

    public function testInstance(): Phonet\Data\ActiveCall
    {
        $activeCall = new Phonet\Data\ActiveCall(
            static::UUID,
            static::PARENT_UUID,
            Carbon::make(static::DIAL_AT),
            Carbon::make(static::BRIDGE_AT),
            Phonet\Enum\Direction::IN(),
            Phonet\Enum\LastEvent::HANGUP(),
            new Phonet\Data\Employee(
                static::ID,
                static::INTERNAL_NUMBER,
                static::DISPLAY_NAME,
                static::TYPE,
                static::EMAIL
            ),
            new Phonet\Data\Employee(
                static::ID,
                static::INTERNAL_NUMBER,
                static::DISPLAY_NAME,
                static::TYPE,
                static::EMAIL
            ),
            new Phonet\Data\Collection\Subject([
                new Phonet\Data\Subject(
                    static::SUBJECT_ID,
                    static::NAME,
                    static::NUMBER,
                    static::COMPANY,
                    static::URI,
                    static::PRIORITY
                ),
            ]),
            static::TRUNK_NUMBER,
            static::TRUNK_NAME
        );
        $this->assertInstanceOf(Phonet\Data\ActiveCall::class, $activeCall);

        return $activeCall;
    }

    /**
     * @depends testInstance
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetUuid(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(static::UUID, $activeCall->getUuid());

        return $activeCall;
    }

    /**
     * @depends testGetUuid
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetParentUuid(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(static::PARENT_UUID, $activeCall->getParentUuid());

        return $activeCall;
    }

    /**
     * @depends testGetParentUuid
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetBridgeAt(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(
            Carbon::make(static::BRIDGE_AT),
            Carbon::make($activeCall->getBridgeAt())
        );

        return $activeCall;
    }

    /**
     * @depends testGetBridgeAt
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetDialAt(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(
            Carbon::make(static::DIAL_AT),
            Carbon::make($activeCall->getDialAt())
        );

        return $activeCall;
    }

    /**
     * @depends testGetDialAt
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetDirection(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(Phonet\Enum\Direction::IN(), $activeCall->getDirection());

        return $activeCall;
    }

    /**
     * @depends testGetDirection
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetLastEvent(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(Phonet\Enum\LastEvent::HANGUP(), $activeCall->getLastEvent());

        return $activeCall;
    }

    /**
     * @depends testGetLastEvent
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetEmployeeCaller(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(
            new Phonet\Data\Employee(
                static::ID,
                static::INTERNAL_NUMBER,
                static::DISPLAY_NAME,
                static::TYPE,
                static::EMAIL
            ),
            $activeCall->getEmployeeCaller()
        );

        return $activeCall;
    }

    /**
     * @depends testGetEmployeeCaller
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetEmployeeCallTaker(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(
            new Phonet\Data\Employee(
                static::ID,
                static::INTERNAL_NUMBER,
                static::DISPLAY_NAME,
                static::TYPE,
                static::EMAIL
            ),
            $activeCall->getEmployeeCallTaker()
        );

        return $activeCall;
    }

    /**
     * @depends testGetEmployeeCallTaker
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetSubjects(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(
            new Phonet\Data\Collection\Subject([
                new Phonet\Data\Subject(
                    static::SUBJECT_ID,
                    static::NAME,
                    static::NUMBER,
                    static::COMPANY,
                    static::URI,
                    static::PRIORITY
                ),
            ]),
            $activeCall->getSubjects()
        );

        return $activeCall;
    }

    /**
     * @depends testGetSubjects
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetTrunkNumber(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(static::TRUNK_NUMBER, $activeCall->getTrunkNumber());

        return $activeCall;
    }

    /**
     * @depends testGetTrunkNumber
     *
     * @param Phonet\Data\ActiveCall $activeCall
     *
     * @return Phonet\Data\ActiveCall
     */
    public function testGetTrunkName(Phonet\Data\ActiveCall $activeCall): Phonet\Data\ActiveCall
    {
        $this->assertEquals(static::TRUNK_NAME, $activeCall->getTrunkName());

        return $activeCall;
    }

    /**
     * @depends testGetTrunkName
     *
     * @param Phonet\Data\ActiveCall $activeCall
     */
    public function testJsonSerialize(Phonet\Data\ActiveCall $activeCall): void
    {
        $this->assertEquals(
            [
                'uuid' => static::UUID,
                'parentUuid' => static::PARENT_UUID,
                'dialAt' => Carbon::make(static::DIAL_AT),
                'bridgeAt' => Carbon::make(static::BRIDGE_AT),
                'direction' => Phonet\Enum\Direction::IN(),
                'lastEvent' => Phonet\Enum\LastEvent::HANGUP(),
                'employeeCaller' => new Phonet\Data\Employee(
                    static::ID,
                    static::INTERNAL_NUMBER,
                    static::DISPLAY_NAME,
                    static::TYPE,
                    static::EMAIL
                ),
                'employeeCallTaker' => new Phonet\Data\Employee(
                    static::ID,
                    static::INTERNAL_NUMBER,
                    static::DISPLAY_NAME,
                    static::TYPE,
                    static::EMAIL
                ),
                'subjects' => new Phonet\Data\Collection\Subject([
                    new Phonet\Data\Subject(
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
            $activeCall->jsonSerialize()
        );
    }
}
