<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 consumables plugin for GLPI
 Copyright (C) 2009-2016 by the consumables Development Team.

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

include('../../../inc/includes.php');
Session::checkLoginUser();

$plugin = new Plugin();

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::header(PluginConsumablesWizard::getTypeName(2), '', "management", "pluginconsumablesmenu");
} else {
   if ($plugin->isActivated('servicecatalog')) {
      PluginServicecatalogMain::showDefaultHeaderHelpdesk(PluginConsumablesWizard::getTypeName(2));
      echo "<br>";
   } else {
      Html::helpHeader(PluginConsumablesWizard::getTypeName(2));
   }
}

   $p = ['criteria'   => [
      [
         'field'      => 6,        // field index in search options
         'searchtype' => 'equals',  // type of search
         'value'      => 2,         // value to search
      ]
   ],
      'as_map'=>0];
$p = Search::manageParams(PluginConsumablesValidation::getType(), $_GET);
$p["criteria"][0] =  [
   'field'      => 6,        // field index in search options
   'searchtype' => 'equals',  // type of search
   'value'      => 2,         // value to search
];
   Search::showList("PluginConsumablesValidation",$p);

if (Session::getCurrentInterface() != 'central'
    && $plugin->isActivated('servicecatalog')) {

   PluginServicecatalogMain::showNavBarFooter('consumables');
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
