<?php

namespace Wearesho\Phonet\Data;

/**
 * Class BaseEmployee
 * @package Wearesho\Phonet\Data
 */
abstract class BaseEmployee implements \JsonSerializable
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $internalNumber;

    /** @var string */
    protected $displayName;

    /**
     * BaseEmployee constructor.
     *
     * @param int $id
     * @param string $internalNumber
     * @param string $displayName
     */
    public function __construct(int $id, string $internalNumber, string $displayName)
    {
        $this->id = $id;
        $this->internalNumber = $internalNumber;
        $this->displayName = $displayName;
    }

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
