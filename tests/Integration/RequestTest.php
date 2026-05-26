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
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 consumables is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with consumables. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Consumables\Tests\Integration;

use CommonITILValidation;
use Consumable;
use ConsumableItem;
use ConsumableItemType;
use Glpi\Tests\DbTestCase;
use GlpiPlugin\Consumables\Request;
use Session;

class RequestTest extends DbTestCase
{
    public function testCountForConsumableItemReturnsZeroForUnknownItem(): void
    {
        $this->assertSame(0, Request::countForConsumableItem(999999));
    }

    public function testCountForConsumableItemReturnsCorrectCount(): void
    {
        $consumableItem = new ConsumableItem();
        $itemId = (int) $consumableItem->add([
            'name'        => 'Count Test Item',
            'entities_id' => 0,
        ]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
        $this->assertGreaterThan(0, $itemId);

        $consumable = new Consumable();
        for ($i = 0; $i < 3; $i++) {
            $consumable->add([
                'consumableitems_id' => $itemId,
                'entities_id'        => 0,
                'date_in'            => date('Y-m-d'),
            ]);
            $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
        }

        $this->assertSame(3, Request::countForConsumableItem($itemId));
    }

    public function testAddCreatesRequestRecord(): void
    {
        $this->login();

        $type = new ConsumableItemType();
        $typeId = (int) $type->add(['name' => 'Add Test Type', 'entities_id' => 0]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
        $this->assertGreaterThan(0, $typeId);

        $consumableItem = new ConsumableItem();
        $itemId = (int) $consumableItem->add([
            'name'                   => 'Add Test Item',
            'entities_id'            => 0,
            'consumableitemtypes_id' => $typeId,
        ]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];
        $this->assertGreaterThan(0, $itemId);

        $request   = new Request();
        $requestId = (int) $request->add([
            'consumableitems_id'     => $itemId,
            'consumableitemtypes_id' => $typeId,
            'number'                 => 2,
            'requesters_id'          => Session::getLoginUserID(),
            'validators_id'          => 0,
            'status'                 => CommonITILValidation::WAITING,
            'give_items_id'          => Session::getLoginUserID(),
            'give_itemtype'          => 'User',
            'date_mod'               => date('Y-m-d H:i:s'),
        ]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $this->assertGreaterThan(0, $requestId);
        $this->assertTrue($request->getFromDB($requestId));
        $this->assertSame($itemId, (int) $request->fields['consumableitems_id']);
        $this->assertSame(2, (int) $request->fields['number']);
        $this->assertSame(CommonITILValidation::WAITING, (int) $request->fields['status']);
        $this->assertSame((int) Session::getLoginUserID(), (int) $request->fields['requesters_id']);
    }

    public function testAddToCartReturnsFalseWhenMandatoryFieldsAreZero(): void
    {
        $this->login();

        $request = new Request();
        $result  = $request->addToCart([
            'consumableitemtypes_id' => 0,
            'consumableitems_id'     => 0,
            'number'                 => 0,
        ]);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['message']);
    }

    public function testAddToCartReturnsTrueWithValidInput(): void
    {
        $this->login();

        $type = new ConsumableItemType();
        $typeId = (int) $type->add(['name' => 'Cart Type', 'entities_id' => 0]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $consumableItem = new ConsumableItem();
        $itemId = (int) $consumableItem->add([
            'name'                   => 'Cart Item',
            'entities_id'            => 0,
            'consumableitemtypes_id' => $typeId,
        ]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $request = new Request();
        $result  = $request->addToCart([
            'consumableitemtypes_id' => $typeId,
            'consumableitems_id'     => $itemId,
            'number'                 => 1,
        ]);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('rowId', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertIsArray($result['fields']);
    }

    public function testAddConsumablesCreatesRequestInDatabase(): void
    {
        $this->login();

        $type = new ConsumableItemType();
        $typeId = (int) $type->add(['name' => 'Consumables Cart Type', 'entities_id' => 0]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $consumableItem = new ConsumableItem();
        $itemId = (int) $consumableItem->add([
            'name'                   => 'Consumables Cart Item',
            'entities_id'            => 0,
            'consumableitemtypes_id' => $typeId,
        ]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $request = new Request();
        $result  = $request->addConsumables([
            'consumables_cart' => [
                [
                    'consumableitemtypes_id' => $typeId,
                    'consumableitems_id'     => $itemId,
                    'number'                 => 1,
                    'give_items_id'          => Session::getLoginUserID(),
                    'give_itemtype'          => 'User',
                ],
            ],
        ]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);

        $rows = $request->find(['consumableitems_id' => $itemId]);
        $this->assertCount(1, $rows);
        $row = reset($rows);
        $this->assertSame((int) Session::getLoginUserID(), (int) $row['requesters_id']);
        $this->assertSame(CommonITILValidation::WAITING, (int) $row['status']);
        $this->assertSame(0, (int) $row['validators_id']);
    }

    public function testAddConsumablesReturnsFalseWhenCartIsAbsent(): void
    {
        $this->login();

        $request = new Request();
        $result  = $request->addConsumables([]);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
    }
}
