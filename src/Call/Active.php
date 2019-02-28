<?php

namespace Wearesho\Phonet\Call;

use Carbon\Carbon;
use Wearesho\Phonet;

/**
 * Class Active
 * @package Wearesho\Phonet\Call
 */
class Active extends Phonet\Call
{
    /** @var Carbon */
    protected $dialAt;

    /** @var Carbon|null */
    protected $bridgeAt;

    /** @var Phonet\Call\Event */
    protected $lastEvent;

    /** @var Phonet\Subject\Collection|null */
    protected $subjects;

    /** @var string */
    protected $trunkNumber;

    /** @var string */
    protected $trunkName;

    public function __construct(
        string $uuid,
        Carbon $dialAt,
        Phonet\Call\Direction $direction,
        Phonet\Call\Event $lastEvent,
        Phonet\Employee $employeeCaller,
        string $trunkNumber,
        string $trunkName,
        string $parentUuid = null,
        Carbon $bridgeAt = null,
        Phonet\Employee $employeeCallTaker = null,
        Phonet\Subject\Collection $subjects = null
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

    public function getLastEvent(): Phonet\Call\Event
    {
        return $this->lastEvent;
    }

    public function getSubjects(): ?Phonet\Subject\Collection
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
