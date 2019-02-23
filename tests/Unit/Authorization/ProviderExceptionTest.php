<?php

namespace Wearesho\Phonet\Tests\Unit\Authorization;

use PHPUnit\Framework\TestCase;
use Wearesho\Phonet\Authorization\ProviderException;

/**
 * Class ProviderExceptionTest
 * @package Wearesho\Phonet\Tests\Unit\Authorization
 * @coversDefaultClass ProviderException
 * @internal
 */
class ProviderExceptionTest extends TestCase
{
    protected const DOMAIN = 'test-domain';

    /** @var ProviderException */
    protected $fakeProviderException;

    protected function setUp(): void
    {
        $this->fakeProviderException = new ProviderException(static::DOMAIN);
    }

    public function testGetDomain(): void
    {
        $this->assertEquals(static::DOMAIN, $this->fakeProviderException->getDomain());
    }
}
