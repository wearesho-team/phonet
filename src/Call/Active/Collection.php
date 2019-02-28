<?php

namespace Wearesho\Phonet\Call\Active;

use Wearesho\BaseCollection;
use Wearesho\Phonet\Call;

/**
 * Class Collection
 * @package Wearesho\Phonet\Call\Active
 */
class Collection extends BaseCollection
{
    public function type(): string
    {
        return Call\Active::class;
    }
}
