<?php

namespace Wearesho\Phonet\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class LastEvent
 * @package Wearesho\Phonet\Enum
 *
 * @method static LastEvent DIAL()
 * @method static LastEvent BRIDGE()
 * @method static LastEvent HANGUP()
 */
final class LastEvent extends Enum
{
    public const DIAL = 'call.dial';
    public const BRIDGE = 'call.bridge';
    public const HANGUP = 'call.hangup';
}
