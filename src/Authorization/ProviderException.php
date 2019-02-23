<?php

namespace Wearesho\Phonet\Authorization;

use Throwable;
use Wearesho\Phonet;

/**
 * Class ProviderException
 * @package Wearesho\Phonet\Authorization
 */
class ProviderException extends Phonet\Exception
{
    /** @var string */
    protected $domain;

    public function __construct(string $domain, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->domain = $domain;

        parent::__construct($message, $code, $previous);
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
