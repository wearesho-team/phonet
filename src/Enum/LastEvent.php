<?php

namespace Wearesho\Phonet\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class LastEvent
 * @package Wearesho\Phonet\Enum
 */
final class LastEvent extends Enum
{
    public const DIAL = 'call.dial';
    public const BRIDGE = 'call.dial';
    public const HANGUP = 'call.hangup';
}
