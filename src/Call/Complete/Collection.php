<?php

namespace Wearesho\Phonet\Call\Complete;

use Wearesho\BaseCollection;
use Wearesho\Phonet\Call;

/**
 * Class Complete
 * @package Wearesho\Phonet\Call\Complete
 */
class Collection extends BaseCollection
{
    public function type(): string
    {
        return Call\Complete::class;
    }
}
