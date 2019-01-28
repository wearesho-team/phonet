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
        Carbon $dialAt,
        Enum\Direction $direction,
        Enum\LastEvent $lastEvent,
        Employee $employeeCaller,
        string $trunkNumber,
        string $trunkName,
        string $parentUuid = null,
        Carbon $bridgeAt = null,
        Employee $employeeCallTaker = null,
        Collection\Subject $subjects = null
    ) {
        $this->dialAt = $dialAt;
        $this->bridgeAt = $bridgeAt;
        $this->lastEvent = $lastEvent;
        $this->subjects = $subjects;
        $this->trunkNumber = $trunkNumber;
        $this->trunkName = $trunkName;

        parent::__construct($uuid, $direction, $employeeCaller, $employeeCallTaker, $parentUuid);
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'parentUuid' => $this->parentUuid,
            'dialAt' => $this->dialAt,
            'bridgeAt' => $this->bridgeAt,
            'direction' => $this->direction,
            'lastEvent' => $this->lastEvent,
            'employeeCaller' => $this->employeeCaller,
            'employeeCallTaker' => $this->employeeCallTaker,
            'subjects' => $this->subjects,
            'trunkNumber' => $this->trunkNumber,
            'trunkName' => $this->trunkName,
        ];
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
