<?php

namespace Wearesho\Phonet\Data;

/**
 * Interface BaseEmployeeInterface
 * @package Wearesho\Phonet\Data
 */
interface BaseEmployeeInterface extends \JsonSerializable
{
    public function getId(): int;

    public function getInternalNumber(): string;

    public function getDisplayName(): string;
}
