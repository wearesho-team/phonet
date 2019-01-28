<?php

namespace Wearesho\Phonet\Data\Collection;

use Wearesho\BaseCollection;
use Wearesho\Phonet\Data;

/**
 * Class ActiveCall
 * @package Wearesho\Phonet\Data\Collection
 */
class ActiveCall extends BaseCollection
{
    public function type(): string
    {
        return Data\ActiveCall::class;
    }
}
