<?php

namespace Wearesho\Phonet\Employee;

use Wearesho\BaseCollection;
use Wearesho\Phonet;

/**
 * Class Collection
 * @package Wearesho\Phonet\Employee
 */
class Collection extends BaseCollection
{
    public function type(): string
    {
        return Phonet\Employee::class;
    }
}
