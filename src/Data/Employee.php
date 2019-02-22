<?php

namespace Wearesho\Phonet\Data;

/**
 * Class Employee
 * @package Wearesho\Phonet\Data
 */
class Employee extends BaseEmployee
{
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
        parent::__construct($id, $internalNumber, $displayName);

        $this->type = $type;
        $this->email = $email;
    }

    public function jsonSerialize(): array
    {
        return \array_merge(parent::jsonSerialize(), [
            'type' => $this->type,
            'email' => $this->email,
        ]);
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
