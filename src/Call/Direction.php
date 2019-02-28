<?php

namespace Wearesho\Phonet\Call;

use MyCLabs\Enum\Enum;

/**
 * Class Direction
 * @package Wearesho\Phonet\Call
 *
 * @method static Direction INTERNAL()
 * @method static Direction OUT()
 * @method static Direction IN()
 * @method static Direction PAUSE_ON()
 * @method static Direction PAUSE_OFF()
 */
final class Direction extends Enum
{
    public const INTERNAL = 1;
    public const OUT = 2;
    public const IN = 4;
    public const PAUSE_ON = 32;
    public const PAUSE_OFF = 64;
}
