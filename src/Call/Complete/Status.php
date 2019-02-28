<?php

namespace Wearesho\Phonet\Call\Complete;

use MyCLabs\Enum\Enum;

/**
 * Class Status
 *
 * This status exists only for complete calls
 *
 * @package Wearesho\Phonet\Call\Complete
 *
 * @method static Status TARGET_RESPONDED()
 * @method static Status TARGET_NOT_RESPONDED()
 * @method static Status DIRECTION_OVERLOADED()
 * @method static Status INTERNAL_ERROR()
 * @method static Status TARGET_IS_BUSY()
 */
final class Status extends Enum
{
    public const TARGET_RESPONDED = 0;
    public const TARGET_NOT_RESPONDED = 1;
    public const DIRECTION_OVERLOADED = 2;
    public const INTERNAL_ERROR = 3;
    public const TARGET_IS_BUSY = 4;
}
