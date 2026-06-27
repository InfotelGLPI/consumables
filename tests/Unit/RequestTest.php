<?php

/*
 -------------------------------------------------------------------------
 consumables plugin for GLPI
 Copyright (C) 2015-2026 by the consumables Development Team.

 https://github.com/InfotelGLPI/consumables
 -------------------------------------------------------------------------

 LICENSE

 This file is part of consumables.

 consumables is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 consumables is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with consumables. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

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
