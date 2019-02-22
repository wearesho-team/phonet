<?php

namespace Wearesho\Phonet;

/**
 * Interface RestInterface
 * @package Wearesho\Phonet
 */
interface RestInterface
{
    public const HANGUP_CALL = 'hangup';

    public function send(string $method, string $api, ?string $body): array;
}
