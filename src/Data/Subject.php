<?php

namespace Wearesho\Phonet\Data;

/**
 * Class Subject
 * @package Wearesho\Phonet\Data
 */
class Subject
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
        ?string $id,
        ?string $name,
        string $number,
        ?string $company,
        string $uri,
        ?string $priority
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->number = $number;
        $this->company = $company;
        $this->uri = $uri;
        $this->priority = $priority;
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
