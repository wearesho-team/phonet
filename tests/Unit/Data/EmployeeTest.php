<?php

namespace Wearesho\Phonet\Tests\Unit\Data;

use PHPUnit\Framework\TestCase;
use Wearesho\Phonet\Employee;

/**
 * Class EmployeeTest
 * @package Wearesho\Phonet\Tests\Unit\Data
 */
class EmployeeTest extends TestCase
{
    protected const ID = 10;
    protected const INTERNAL_NUMBER = 'internal-number';
    protected const DISPLAY_NAME = 'display-name';
    protected const TYPE = 1;
    protected const EMAIL = 'email';

    /** @var Employee */
    protected $employee;

    protected function setUp(): void
    {
        $this->employee = new Employee(
            static::ID,
            static::INTERNAL_NUMBER,
            static::DISPLAY_NAME,
            static::TYPE,
            static::EMAIL
        );
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            [
                'id' => static::ID,
                'internalNumber' => static::INTERNAL_NUMBER,
                'displayName' => static::DISPLAY_NAME,
                'type' => static::TYPE,
                'email' => static::EMAIL
            ],
            $this->employee->jsonSerialize()
        );
    }

    public function testGetId(): void
    {
        $this->assertEquals(static::ID, $this->employee->getId());
    }

    public function testGetInternalNumber(): void
    {
        $this->assertEquals(static::INTERNAL_NUMBER, $this->employee->getInternalNumber());
    }

    public function testGetDisplayName(): void
    {
        $this->assertEquals(static::DISPLAY_NAME, $this->employee->getDisplayName());
    }

    public function testGetType(): void
    {
        $this->assertEquals(static::TYPE, $this->employee->getType());
    }

    public function testGetEmail(): void
    {
        $this->assertEquals(static::EMAIL, $this->employee->getEmail());
    }

    public function testGetDefaultValues(): void
    {
        $this->employee = new Employee(
            static::ID,
            static::INTERNAL_NUMBER,
            static::DISPLAY_NAME
        );

        $this->assertNull($this->employee->getType());
        $this->assertNull($this->employee->getEmail());
    }
}
