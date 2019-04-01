<?php

namespace Wearesho\Phonet\Authorization;

use Wearesho\Phonet\Exception;

/**
 * Class CookieException
 * @package Wearesho\Phonet\Authorization
 */
class CookieException extends Exception
{
    public const COOKIE_UNAVAILABLE = -1;

    /** @var array */
    protected $headers;

    public function __construct($headers = [], $message = "", $code = 0, \Throwable $previous = null)
    {
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
