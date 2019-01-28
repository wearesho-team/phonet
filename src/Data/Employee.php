<?php

namespace Wearesho\Phonet\Data;

/**
 * Class Employee
 * @package Wearesho\Phonet\Data
 */
class Employee implements \JsonSerializable
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $internalNumber;

    /** @var string */
    protected $displayName;

    /** @var string|null */
    protected $email;

    /** @var int|null */
    protected $type;

    public function __construct(
        int $id,
        string $internalNumber,
        string $displayName,
        int $type = null,
        string $email = null
    ) {
        $this->id = $id;
        $this->internalNumber = $internalNumber;
        $this->displayName = $displayName;
        $this->type = $type;
        $this->email = $email;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'internalNumber' => $this->internalNumber,
            'displayName' => $this->displayName,
            'type' => $this->type,
            'email' => $this->email,
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getType(): ?int
    {
        return $this->type;
    }
}
