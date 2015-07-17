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


include ('../../../inc/includes.php');

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::header(PluginConsumablesWizard::getTypeName(2), '', "plugins", "pluginconsumablesmenu");
} else {
   Html::helpHeader(PluginConsumablesWizard::getTypeName(2), '', "plugins", "pluginconsumablesmenu");
}

$wizard = new PluginConsumablesWizard();
$wizard->showMenu();

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
?>