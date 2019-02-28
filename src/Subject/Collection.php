<?php

namespace Wearesho\Phonet\Subject;

use Wearesho\BaseCollection;
use Wearesho\Phonet;

/**
 * Class Collection
 * @package Wearesho\Phonet\Subject
 */
class Collection extends BaseCollection
{
    public function type(): string
    {
        return Phonet\Subject::class;
    }
}
