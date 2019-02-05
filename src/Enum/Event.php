<?php

namespace Wearesho\Phonet\Enum;

use MyCLabs\Enum\Enum;

/**
 * Class Event
 * @package Wearesho\Phonet\Enum
 *
 * @method static Event DIAL()
 * @method static Event BRIDGE()
 * @method static Event HANGUP()
 */
final class Event extends Enum
{
    public const DIAL = 'call.dial';
    public const BRIDGE = 'call.bridge';
    public const HANGUP = 'call.hangup';
}
