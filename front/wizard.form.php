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

use GlpiPlugin\Consumables\Menu;
use GlpiPlugin\Consumables\Request;
use GlpiPlugin\Consumables\Validation;
use GlpiPlugin\Consumables\Wizard;
use GlpiPlugin\Servicecatalog\Main;

\Session::checkRight('plugin_consumables_request', READ);

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   \Html::header(Wizard::getTypeName(2), '', "management", Menu::class);
} else {
   if (\Plugin::isPluginActive('servicecatalog')) {
      Main::showDefaultHeaderHelpdesk(Wizard::getTypeName(2));
   } else {
      \Html::helpHeader(Wizard::getTypeName(2));
   }
}

if (!empty($_GET['action'])) {
   switch ($_GET['action']) {
      case 'consumablerequest':
         $consumablerequest = new Request();
         $consumablerequest->showConsumableRequest();
         break;
      case 'consumablevalidation':
         echo "<div class='alert alert-secondary'>";
         echo "<i class='thumbnail ti ti-shopping-cart-plus fa-2x'></i>";
         echo "&nbsp;";
         echo __("Consumable validation", "consumables");
         echo "</div>";
         $p = ['criteria' => [
            [
               'field' => 6,        // field index in search options
               'searchtype' => 'equals',  // type of search
               'value' => 2,         // value to search
            ]
         ],
            'as_map' => 0];
         $p = Search::manageParams(Validation::getType(), $_GET);
         $p["criteria"][0] =  [
            'field'      => 6,        // field index in search options
            'searchtype' => 'equals',  // type of search
            'value'      => 2,         // value to search
         ];
         Search::showList(Validation::class, $p);
         break;
   }
}

if (\Session::getCurrentInterface() != 'central'
   && \Plugin::isPluginActive('servicecatalog')) {

   Main::showNavBarFooter('consumables');
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
   \Html::footer();
} else {
   \Html::helpFooter();
}
