<?php

namespace Wearesho\Phonet\Call\Direction;

use Wearesho\BaseCollection;
use Wearesho\Phonet\Call;

/**
 * Class Collection
 * @package Wearesho\Phonet\Call\Direction
 */
class Collection extends BaseCollection
{
    public function type(): string
    {
        return Call\Direction::class;
    }
}
