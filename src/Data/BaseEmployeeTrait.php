<?php

namespace Wearesho\Phonet\Data;

/**
 * Trait BaseEmployeeTrait
 * @package Wearesho\Phonet\Data
 */
trait BaseEmployeeTrait
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $internalNumber;

    /** @var string */
    protected $displayName;

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'internalNumber' => $this->internalNumber,
            'displayName' => $this->displayName,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getInternalNumber(): string
    {
        return $this->internalNumber;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }
}
