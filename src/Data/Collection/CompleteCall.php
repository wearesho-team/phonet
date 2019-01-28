<?php

namespace Wearesho\Phonet\Data\Collection;

use Wearesho\BaseCollection;
use Wearesho\Phonet\Data;

/**
 * Class CompleteCall
 * @package Wearesho\Phonet\Data\Collection
 */
class CompleteCall extends BaseCollection
{
    public function type(): string
    {
        return Data\CompleteCall::class;
    }
}
