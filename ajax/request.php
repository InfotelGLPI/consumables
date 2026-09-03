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

use GlpiPlugin\Consumables\Request;
use GlpiPlugin\Consumables\Validation;

Session::checkRight('plugin_consumables_request', 1);

switch ($_POST['action'] ?? '') {
    case 'addToCart':
        header('Content-Type: application/json; charset=UTF-8');
        $request = new Request();
        echo json_encode($request->addToCart($_POST));
        break;

    case 'addConsumables':
        header('Content-Type: application/json; charset=UTF-8');
        $request = new Request();
        echo json_encode($request->addConsumables($_POST));
        break;

    case 'reloadAvailableConsumables':
        header("Content-Type: text/html; charset=UTF-8");
        $request = new Request();
        $request->loadAvailableConsumables($_POST['type'] ?? '');
        break;

    case 'seeConsumablesInfos':
        header("Content-Type: text/html; charset=UTF-8");
        $request = new Request();
        $request->seeConsumablesInfos((int) ($_POST['consumableitems_id'] ?? 0));
        break;

    case 'reloadAvailableConsumablesNumber':
        header("Content-Type: text/html; charset=UTF-8");
        $request = new Request();
        $request->loadAvailableConsumablesNumber(json_decode($_POST['used'] ?? '[]'), (int) ($_POST['consumableitems_id'] ?? 0));
        break;

        //    case 'loadConsumableInformation':
        //        header("Content-Type: text/html; charset=UTF-8");
        //        $validation = new Validation();
        //        $validation->loadConsumableInformation(Session::getLoginUserID(), $_POST['consumableitems_id']);
        //        break;

    case 'validationConsumables':
        header('Content-Type: application/json; charset=UTF-8');
        if (!Session::haveRight('plugin_consumables_validation', 1)) {
            echo json_encode(['error' => 'Access denied']);
            break;
        }
        $validation = new Validation();
        echo json_encode($validation->validationConsumable($_POST));
        break;

    case 'searchConsumables':
        header('Content-Type: application/json; charset=UTF-8');
        $requesters_id = (int) ($_POST['requesters_id'] ?? 0);
        $has_read      = Session::haveRight('plugin_consumables', READ);
        if (!$has_read && $requesters_id !== (int) Session::getLoginUserID()) {
            echo json_encode(['error' => 'Access denied', 'message' => '']);
            break;
        }
        // The self-restriction above only proves ownership in the User id-space. User and
        // Group ids are independent, so without the plugin READ right we must pin type to
        // 'User'; otherwise a requester could read a Group's requests by passing
        // type=Group together with their own (numerically equal) user id.
        $type = $_POST['type'] ?? 'User';
        if (!$has_read) {
            $type = 'User';
        }
        $request = new Request();
        echo json_encode($request->listItemsForUserOrGroup($requesters_id, $type, $_POST));
        break;

    case 'loadAvailableConsumablesNumber':
        $request = new Request();
        $request->loadAvailableConsumablesNumber(0, (int) ($_POST['consumableitems_id'] ?? 0));
        break;
}
