<?php

/**
 * -------------------------------------------------------------------------
 * consumables plugin for GLPI
 * Copyright (C) 2015-2026 by the consumables Development Team.
 *
 * https://github.com/InfotelGLPI/consumables
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of consumables.
 *
 * consumables is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * consumables is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with consumables. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

use GlpiPlugin\Consumables\Option;

Session::checkRight('plugin_consumables', UPDATE);

$option = new Option();

if (isset($_POST["add_groups"])
    || isset($_POST["delete_groups"])
    || isset($_POST["update"])) {
    // The option row is no longer created when the tab is merely viewed: create it
    // on the first write, once the consumable itself (entity included) is reachable.
    if ((int) ($_POST['id'] ?? 0) <= 0) {
        $consumableitems_id = (int) ($_POST['consumableitems_id'] ?? 0);
        $consumable         = new ConsumableItem();
        if (!$consumable->can($consumableitems_id, READ)) {
            Html::back();
        }
        if (!$option->getFromDBByCrit(['consumableitems_id' => $consumableitems_id])) {
            $init = ['consumableitems_id' => $consumableitems_id,
                'groups'             => '',
                'max_cart'           => 0];
            if (!$option->can(-1, CREATE, $init) || !$option->add($init)) {
                Html::back();
            }
        }
        $_POST['id'] = $option->getID();
    }
    if (!$option->can((int) ($_POST['id'] ?? 0), UPDATE)) {
        Html::back();
    }
    $option->update($_POST);
    Html::back();
}
