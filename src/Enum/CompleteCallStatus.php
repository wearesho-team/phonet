<?php

namespace Wearesho\Phonet\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class CompleteCallStatus
 * @package Wearesho\Phonet\Enum
 *
 * @method static CompleteCallStatus TARGET_RESPONDED()
 * @method static CompleteCallStatus TARGET_NOT_RESPONDED()
 * @method static CompleteCallStatus DIRECTION_OVERLOADED()
 * @method static CompleteCallStatus INTERNAL_ERROR()
 * @method static CompleteCallStatus TARGET_IS_BUSY()
 */
final class CompleteCallStatus extends Enum
{
    public const TARGET_RESPONDED = 0;
    public const TARGET_NOT_RESPONDED = 1;
    public const DIRECTION_OVERLOADED = 2;
    public const INTERNAL_ERROR = 3;
    public const TARGET_IS_BUSY = 4;
}
