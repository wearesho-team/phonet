<?php

namespace Wearesho\Phonet\Data;

/**
 * Class Subject
 * @package Wearesho\Phonet\Data
 */
class Subject implements \JsonSerializable
{
    /** @var string|null */
    protected $id;

    /** @var string|null */
    protected $name;

    /** @var string */
    protected $number;

    /** @var string|null */
    protected $company;

    /** @var string */
    protected $uri;

    /** @var string|null */
    protected $priority;

    public function __construct(
        string $number,
        string $uri,
        string $id = null,
        string $name = null,
        string $company = null,
        string $priority = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->number = $number;
        $this->company = $company;
        $this->uri = $uri;
        $this->priority = $priority;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'number' => $this->number,
            'company' => $this->company,
            'uri' => $this->uri,
            'priority' => $this->priority,
        ];
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }
}
