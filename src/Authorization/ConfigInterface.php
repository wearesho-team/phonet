<?php

namespace Wearesho\Phonet\Authorization;

/**
 * Interface ConfigInterface
 * @package Wearesho\Phonet\Authorization
 */
interface ConfigInterface
{
    public function getDomain(): string;

    public function getApiKey(): string;
}
