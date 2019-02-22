<?php

namespace Wearesho\Phonet\Data;

/**
 * Class Subject
 * @package Wearesho\Phonet\Data
 */
class Subject implements SubjectInterface
{
    use SubjectTrait;

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
}
