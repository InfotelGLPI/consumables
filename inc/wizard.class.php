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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginConsumablesMenu
 *
 * This class shows the plugin main page
 *
 * @package    Consumables
 * @author     Ludovic Dupont
 */
class PluginConsumablesWizard extends CommonDBTM {

   static $rightname = "plugin_consumables";

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {
      return __('Consumables wizard', 'consumables');
   }

   /**
    * Show config menu
    */
   function showMenu() {
      global $CFG_GLPI;

      $request = new PluginConsumablesRequest();

      if (!$this->canView()) {
         return false;
      }

      echo "<h3><div class='alert alert-secondary' role='alert'>";
      echo "<i class='fas fas fa-id-badge'></i>&nbsp;";
      echo __("Consumable request", "consumables");
      echo "</div></h3>";

      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe consumables_wizard_rank' style='width: 950px;'>";
//      echo "<tr>";
//      echo "<th colspan='5'>" . __("Consumable request", "consumables") . "</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1' style='background-color:white;'>";

      // Consumable request
      if ($request->canRequest()) {
         echo "<td class='center'>";
         echo "<a  class='consumables_menu_a' href='" . $CFG_GLPI["root_doc"] . "/plugins/consumables/front/wizard.form.php?action=consumablerequest'>";
         echo "<i class='thumbnail fas fa-cart-plus fa-4x'></i>";
         echo "<br><br>" . __("Consumable request", "consumables") . "<br></a>";
         echo "</td>";
      }

      // Consumable validation
      echo "<td class='center'>";
      echo "<a  class='consumables_menu_a' href='" . $CFG_GLPI["root_doc"] . "/plugins/consumables/front/wizard.form.php?action=consumablevalidation'>";
      echo "<i class='thumbnail fas fa-clipboard-check fa-4x'></i>";
      echo "<br><br>" . __("Consumable validation", "consumables") . "</a>";
      echo "</td>";

      echo "</tr>";
      echo "</table></div>";
   }

   /**
    * Show wizard form of the current step
    *
    * @param $step
    */
   function showWizard($step) {

      echo "<div class='consumables_wizard'>";
      switch ($step) {
         case 'consumablerequest':
            $consumablerequest = new PluginConsumablesRequest();
            $consumablerequest->showConsumableRequest();
            break;
         case 'consumablevalidation':
            $consumablevalidation = new PluginConsumablesValidation();
            $consumablevalidation->showConsumableValidation();
            break;
      }
      echo "</div>";
   }

}
