<?php

namespace Wearesho\Phonet\Data\Collection;

use Wearesho\BaseCollection;
use Wearesho\Phonet\Enum;

/**
 * Class Direction
 * @package Wearesho\Phonet\Data\Collection
 */
class Direction extends BaseCollection
{
    public function type(): string
    {
        return Enum\Direction::class;
    }
}
