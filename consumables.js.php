<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Consumables plugin for GLPI
  Copyright (C) 2003-2011 by the consumables Development Team.

  https://forge.indepnet.net/projects/consumables
  -------------------------------------------------------------------------

  LICENSE

  This file is part of consumables.

  Consumables is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Consumables is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Consumables. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');

//change mimetype
header("Content-type: application/javascript");

//not executed in self-service interface & right verification
if ($_SESSION['glpiactiveprofile']['interface'] == "central") {
   // Get item type
   $itemtype = PluginConsumablesField::$types;

   if (!empty($itemtype)) {
      $params = array('root_doc' => $CFG_GLPI['root_doc'],
         'glpi_tab' => 'ConsumableItem$main');

      echo "consumables_addelements(" . json_encode($params) . ");";
   }
}
