<?php

namespace Wearesho\Phonet\Data;

/**
 * Class BaseEmployee
 * @package Wearesho\Phonet\Data
 */
abstract class BaseEmployee implements BaseEmployeeInterface
{
    use BaseEmployeeTrait;

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
}
