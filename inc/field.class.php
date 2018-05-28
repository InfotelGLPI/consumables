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
 * Class PluginConsumablesField
 *
 * This class shows the plugin main page
 *
 * @package    Consumables
 * @author     Ludovic Dupont
 */
class PluginConsumablesField extends CommonDBTM {

   static $types     = ['ConsumableItem'];
   static $rightname = "plugin_consumables";


   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {
      return _n('Consumable request', 'Consumable requests', 1, 'consumables');
   }


   /**
    * Show order reference field
    *
    * @param $params
    */
   public static function addFieldOrderReference($params) {

      $item = $params['item'];

      if (!in_array($item::getType(), self::$types)) {
         return false;
      }
      $consumables_id = $item->getID();
      $field          = new self();
      $field->getFromDBByCrit(["consumables_id = '$consumables_id'"]);

      echo "<tr class='tab_bg_1' id='plugin_cmdb_tr'>";
      echo "<td>" . __('Order reference', 'consumables') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($field, "order_ref");
      echo "</td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

   }

   /**
    * Post add consumable
    *
    * @param ConsumableItem $consumableItem
    */
   static function postAddConsumable(ConsumableItem $consumableItem) {

      $field = new self();
      $field->add(['consumables_id' => $consumableItem->fields['id'],
                   'order_ref'      => $consumableItem->input['order_ref']]);
   }

   /**
    * Pre update consumable
    *
    * @param ConsumableItem $consumableItem
    */
   static function preUpdateConsumable(ConsumableItem $consumableItem) {

      $field = new self();
      $field->getFromDBByCrit([" `consumables_id` = '" . $consumableItem->input['id'] . "'"]);

      if (!empty($field->fields)) {
         $field->update(['id'        => $field->fields['id'],
                         'order_ref' => $consumableItem->input['order_ref']]);
      } else {
         self::postAddConsumable($consumableItem);
      }
   }

}
