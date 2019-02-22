<?php

namespace Wearesho\Phonet\Data\Collection;

use Wearesho\BaseCollection;
use Wearesho\Phonet\Data;

/**
 * Class Subject
 * @package Wearesho\Phonet\Data\Collection
 */
class Subject extends BaseCollection
{
    public function type(): string
    {
        return Data\SubjectInterface::class;
    }
}
