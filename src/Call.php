<?php

namespace Wearesho\Phonet;

use Wearesho\Phonet\Call\Direction;

/**
 * Class Call
 * @package Wearesho\Phonet\Data
 */
abstract class Call implements \JsonSerializable
{
    /** @var string */
    protected $uuid;

    /** @var string|null */
    protected $parentUuid;

    /** @var Direction */
    protected $direction;

    /** @var Employee */
    protected $employeeCaller;

    /** @var Employee|null */
    protected $employeeCallTaker;

    public function __construct(
        string $uuid,
        Direction $direction,
        Employee $employeeCaller,
        Employee $employeeCallTaker = null,
        string $parentUuid = null
    ) {
        $this->uuid = $uuid;
        $this->parentUuid = $parentUuid;
        $this->direction = $direction;
        $this->employeeCaller = $employeeCaller;
        $this->employeeCallTaker = $employeeCallTaker;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getParentUuid(): ?string
    {
        return $this->parentUuid;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }

    public function getEmployeeCaller(): Employee
    {
        return $this->employeeCaller;
    }

    public function getEmployeeCallTaker(): ?Employee
    {
        return $this->employeeCallTaker;
    }
}
