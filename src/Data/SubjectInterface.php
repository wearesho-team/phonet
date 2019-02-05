<?php

namespace Wearesho\Phonet\Data;

/**
 * Interface SubjectInterface
 * @package Wearesho\Phonet\Data
 */
interface SubjectInterface extends \JsonSerializable
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function getNumber(): string;

    public function getCompany(): ?string;

    public function getUri(): string;

    public function getPriority(): ?string;
}
