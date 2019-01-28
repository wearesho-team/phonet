<?php

namespace Wearesho\Phonet;

/**
 * Interface ConfigInterface
 * @package Wearesho\Phonet
 */
interface ConfigInterface
{
    public function getDomain(): string;

    public function getApiKey(): string;
}
