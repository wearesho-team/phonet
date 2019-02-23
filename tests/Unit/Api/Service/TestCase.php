<?php

namespace Wearesho\Phonet\Tests\Unit\Api\Service;

use Wearesho\Phonet;

/**
 * Class TestCase
 * @package Wearesho\Phonet\Tests\Unit\Api\Service
 */
abstract class TestCase extends Phonet\Tests\Unit\Api\TestCase
{
    /** @var Phonet\Service */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new Phonet\Service($this->sender);
    }

    /**
     * @dataProvider requestExceptionProvider
     */
    public function testAuthException(int $statusCode, string $exceptionMessage): void
    {
        $this->mock->append(
            $this->getResponse($statusCode, $exceptionMessage)
        );

        $this->expectException(Phonet\Authorization\ProviderException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->invokeMethod();
    }

    /**
     * @dataProvider requestExceptionProvider
     */
    public function testRestException(int $statusCode, string $exceptionMessage): void
    {
        $this->mock->append(
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getResponse($statusCode, $exceptionMessage)
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionMessage("Api [{$this->api()}] failed");

        $this->invokeMethod();
    }

    /**
     * @dataProvider requestExceptionProvider
     */
    public function testRestExceptionWithCache(int $statusCode, string $exceptionMessage): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::SESSION_ID));
        $this->mock->append(
            $this->getResponse($statusCode, $exceptionMessage)
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionMessage("Api [{$this->api()}] failed");

        $this->invokeMethod();
    }

    /**
     * @dataProvider requestExceptionProvider
     */
    public function testRestExceptionForceProvided(int $statusCode, string $exceptionMessage): void
    {
        $this->presetCache($this->getCacheKey(), $this->createCookie(static::EXPIRED_SESSION_ID));
        $this->mock->append(
            $this->getForbiddenAuthResponse(),
            $this->getSuccessAuthResponse(static::SESSION_ID),
            $this->getResponse($statusCode, $exceptionMessage)
        );

        $this->expectException(Phonet\Exception::class);
        $this->expectExceptionMessage("Api [{$this->api()}] with force auth failed");

        $this->invokeMethod();
    }

    protected function invokeMethod()
    {
        return $this->service->{$this->method()}(...$this->arguments());
    }

    abstract protected function api(): string;

    abstract protected function method(): string;

    abstract protected function arguments(): array;
}
