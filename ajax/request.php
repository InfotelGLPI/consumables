<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 consumables plugin for GLPI
 Copyright (C) 2009-2022 by the consumables Development Team.

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

include("../../../inc/includes.php");

Session::checkLoginUser();
//Html::header_nocache();

switch ($_POST['action']) {
   case 'addToCart':
      header('Content-Type: application/json; charset=UTF-8"');
      $request = new PluginConsumablesRequest();
      echo json_encode($request->addToCart($_POST));
      break;

   case 'addConsumables':
      header('Content-Type: application/json; charset=UTF-8"');
      $request = new PluginConsumablesRequest();
      echo json_encode($request->addConsumables($_POST));
      break;

   case 'reloadAvailableConsumables':
      header("Content-Type: text/html; charset=UTF-8");
      $request = new PluginConsumablesRequest();
      $request->loadAvailableConsumables($_POST['type']);
      break;

   case 'seeConsumablesInfos':
      header("Content-Type: text/html; charset=UTF-8");
      $request = new PluginConsumablesRequest();
      $request->seeConsumablesInfos($_POST['consumableitems_id']);
      break;

   case 'reloadAvailableConsumablesNumber':
      header("Content-Type: text/html; charset=UTF-8");
      $request = new PluginConsumablesRequest();
      $request->loadAvailableConsumablesNumber(json_decode(stripslashes($_POST['used'])), $_POST['consumableitems_id']);
      break;

   case 'loadConsumableInformation':
      header("Content-Type: text/html; charset=UTF-8");
      $validation = new PluginConsumablesValidation();
      $validation->loadConsumableInformation(Session::getLoginUserID(), $_POST['consumableitems_id']);
      break;

   case 'validationConsumables':
      header('Content-Type: application/json; charset=UTF-8"');
      $validation = new PluginConsumablesValidation();
      echo json_encode($validation->validationConsumable($_POST));
      break;

   case 'searchConsumables':
      header('Content-Type: application/json; charset=UTF-8"');
      $request = new PluginConsumablesRequest();
      echo json_encode($request->listItemsForUserOrGroup($_POST['requesters_id'],$_POST['type'], $_POST));
      break;

   case 'loadAvailableConsumablesNumber':
      $request = new PluginConsumablesRequest();
      $request->loadAvailableConsumablesNumber(0, $_POST['consumableitems_id']);
      break;
}
