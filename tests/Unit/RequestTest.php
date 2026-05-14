<?php

namespace GlpiPlugin\Consumables\Tests\Unit;

use GlpiPlugin\Consumables\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        $this->request = new Request();
    }

    public function testGetTypeNameReturnsNonEmptyString(): void
    {
        $name = Request::getTypeName();
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function testGetIconReturnsNonEmptyString(): void
    {
        $this->assertNotEmpty(Request::getIcon());
    }

    public function testCheckMandatoryFieldsReturnsTrueWhenAllFieldsArePresent(): void
    {
        [$ok, $msg] = $this->request->checkMandatoryFields([
            'consumableitemtypes_id' => 3,
            'consumableitems_id'     => 5,
            'number'                 => 2,
        ]);

        $this->assertTrue($ok);
        $this->assertNull($msg);
    }

    public function testCheckMandatoryFieldsReturnsFalseWhenConsumableItemsIdIsZero(): void
    {
        [$ok,] = $this->request->checkMandatoryFields([
            'consumableitemtypes_id' => 3,
            'consumableitems_id'     => 0,
            'number'                 => 2,
        ]);

        $this->assertFalse($ok);
    }

    public function testCheckMandatoryFieldsReturnsFalseWhenConsumableItemTypesIdIsZero(): void
    {
        [$ok,] = $this->request->checkMandatoryFields([
            'consumableitemtypes_id' => 0,
            'consumableitems_id'     => 5,
            'number'                 => 2,
        ]);

        $this->assertFalse($ok);
    }

    public function testCheckMandatoryFieldsReturnsFalseWhenNumberIsZero(): void
    {
        [$ok,] = $this->request->checkMandatoryFields([
            'consumableitemtypes_id' => 3,
            'consumableitems_id'     => 5,
            'number'                 => 0,
        ]);

        $this->assertFalse($ok);
    }

    public function testCheckMandatoryFieldsReturnsFalseWhenNumberIsNullString(): void
    {
        [$ok,] = $this->request->checkMandatoryFields([
            'consumableitemtypes_id' => 3,
            'consumableitems_id'     => 5,
            'number'                 => 'NULL',
        ]);

        $this->assertFalse($ok);
    }

    public function testCheckMandatoryFieldsReturnsFalseWhenFieldIsEmptyString(): void
    {
        [$ok,] = $this->request->checkMandatoryFields([
            'consumableitemtypes_id' => '',
            'consumableitems_id'     => 5,
            'number'                 => 2,
        ]);

        $this->assertFalse($ok);
    }

    public function testCheckMandatoryFieldsErrorMessageIsNonEmptyStringOnFailure(): void
    {
        [, $msg] = $this->request->checkMandatoryFields([
            'consumableitemtypes_id' => 3,
            'consumableitems_id'     => 0,
            'number'                 => 2,
        ]);

        $this->assertIsString($msg);
        $this->assertNotEmpty($msg);
    }

    public function testCheckMandatoryFieldsReturnsTrueWhenNumberIsOne(): void
    {
        [$ok,] = $this->request->checkMandatoryFields([
            'consumableitemtypes_id' => 1,
            'consumableitems_id'     => 1,
            'number'                 => 1,
        ]);

        $this->assertTrue($ok);
    }
}
