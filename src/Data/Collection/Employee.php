<?php

namespace Wearesho\Phonet\Data\Collection;

use Wearesho\BaseCollection;
use Wearesho\Phonet\Data;

/**
 * Class Employee
 * @package Wearesho\Phonet\Data\Collection
 */
class Employee extends BaseCollection
{
    public function type(): string
    {
        return Data\BaseEmployee::class;
    }
}
