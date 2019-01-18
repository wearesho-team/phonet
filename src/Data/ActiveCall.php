<?php

namespace Wearesho\Phonet\Data;

use Carbon\Carbon;
use Wearesho\Phonet\Enum;

/**
 * Class ActiveCall
 * @package Wearesho\Phonet\Data
 */
class ActiveCall extends BaseCall
{
    /** @var Carbon */
    protected $dialAt;

    /** @var Carbon|null */
    protected $bridgeAt;

    /** @var Enum\LastEvent */
    protected $lastEvent;

    /** @var Collection\Subject|null */
    protected $subjects;

    /** @var string */
    protected $trunkNumber;

    /** @var string */
    protected $trunkName;

    public function __construct(
        string $uuid,
        string $parentUuid,
        Carbon $dialAt,
        ?Carbon $bridgeAt,
        Enum\Direction $direction,
        Enum\LastEvent $lastEvent,
        Employee $employeeCaller,
        ?Employee $employeeCallTaker,
        ?Collection\Subject $subjects,
        string $trunkNumber,
        string $trunkName
    ) {
        $this->dialAt = $dialAt;
        $this->bridgeAt = $bridgeAt;
        $this->lastEvent = $lastEvent;
        $this->subjects = $subjects;
        $this->trunkNumber = $trunkNumber;
        $this->trunkName = $trunkName;

        parent::__construct($uuid, $parentUuid, $direction, $employeeCaller, $employeeCallTaker);
    }

    public function getDialAt(): Carbon
    {
        return $this->dialAt;
    }

    public function getBridgeAt(): ?Carbon
    {
        return $this->bridgeAt;
    }

    public function getLastEvent(): Enum\LastEvent
    {
        return $this->lastEvent;
    }

    public function getSubjects(): ?Collection\Subject
    {
        return $this->subjects;
    }

    public function getTrunkNumber(): string
    {
        return $this->trunkNumber;
    }

    public function getTrunkName(): string
    {
        return $this->trunkName;
    }
}
