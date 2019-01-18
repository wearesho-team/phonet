<?php

namespace Wearesho\Phonet\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class Direction
 * @package Wearesho\Phonet\Enum
 */
final class Direction extends Enum
{
    public const INTERNAL = 1;
    public const OUT = 2;
    public const IN = 4;
    public const PAUSE_ON = 32;
    public const PAUSE_OFF = 64;
}
