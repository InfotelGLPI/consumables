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
 * Class PluginConsumablesOption
 */
class PluginConsumablesOption extends CommonDBTM {

   static $rightname = "plugin_consumables";

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   public static function getTypeName($nb = 0) {

      return __('Consumable request options', 'consumables');
   }

   /**
    * Show
    *
    * @param type $item
    *
    * @return bool
    */
   function showForConsumable($item) {

      if (!$this->canView()) {
         return false;
      }
      $data = array();
      if ($this->getFromDBByQuery("WHERE `consumables_id` = " . $item->fields['id'])) {
         $data = $this->fields;
      }
      if (count($data) < 1) {
         $data = $this->initConfig($item->fields['id']);
      }
      $this->listOptionsForConsumable($data, $item);
   }

   /**
    * Initialize the original configuration
    *
    * @param $ID
    *
    * @return array
    */
   function initConfig($ID) {
      $input['consumables_id'] = $ID;
      $input['groups']         = "";
      $input['max_cart']       = "0";
      $this->add($input);
      return $this->fields;
   }

   /**
    * Show list of items
    *
    * @param $data
    * @param $item
    *
    * @internal param \type $fields
    */
   function listOptionsForConsumable($data, $item) {
      global $CFG_GLPI;

      $ID = $data['id'];

      echo "<div class='center'>";
      echo "<form action='" . Toolbox::getItemTypeFormURL('PluginConsumablesOption') . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='3'>" . self::getTypeName(1) . "</th>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Maximum number allowed for request', 'consumables');
      echo " </td>";
      echo "<td>";
      Dropdown::showNumber('max_cart', ['value' =>$data['max_cart'],
                                        'max' => 100]);
      echo " </td>";
      if ($this->canCreate()) {
         echo "<td class='center'>";
         echo "<input type=\"submit\" name=\"update\" class=\"submit\"
         value=\"" . _sx('button', 'Define', 'consumables') . "\" >";
         echo "</td>";
      }
      echo "</tr>";
      echo "<input type='hidden' name='consumables_id' value='" . $data['consumables_id'] . "'>";
      echo "<input type='hidden' name='id' value='" . $ID . "'>";
      echo "</table>";
      Html::closeForm();

      echo "<form action='" . Toolbox::getItemTypeFormURL('PluginConsumablesOption') . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'>";
      echo __('Allowed groups for request', 'consumables');
      echo " </th>";
      echo "</tr>";

      $groups = json_decode($data['groups'], true);
      if (!empty($groups)) {
         foreach ($groups as $key => $val) {

            echo "<tr class='tab_bg_1 center'>";
            echo "<td>";
            echo Dropdown::getDropdownName("glpi_groups", $val);
            echo "</td>";
            echo "<td>";
            Html::showSimpleForm(Toolbox::getItemTypeFormURL('PluginConsumablesOption'),
                                 'delete_groups',
                                 _x('button', 'Delete permanently'),
                                 array('delete_groups' => 'delete_groups',
                                       'id'            => $ID,
                                       '_groups_id'    => $val),
                                 $CFG_GLPI["root_doc"] . "/pics/delete.png");
            echo " </td>";
            echo "</tr>";

         }
      } else {
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='2'>";
         echo __('None');
         echo "</td>";
         echo "</tr>";
      }

      echo "<input type='hidden' name='consumables_id' value='" . $data['consumables_id'] . "'>";
      echo "<input type='hidden' name='id' value='" . $ID . "'>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";

      self::showAddGroup($item, $data);
   }


   /**
    * @param $item
    * @param $data
    */
   static function showAddGroup($item, $data) {

      echo "<form action='" . Toolbox::getItemTypeFormURL('PluginConsumablesOption') . "' method='post'>";
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr class='tab_bg_1 center'>";
      echo "<th>" . __('Add a group for request', 'consumables') . "</th>";
      echo "<th>&nbsp;</th>";
      echo "</tr>";
      echo "<tr class='tab_bg_1 center'>";
      echo "<td>";

      $used = ($data["groups"] == '' ? array() : json_decode($data["groups"], true));

      Group::dropdown(array('name'        => '_groups_id',
                            'used'        => $used,
                            'entity'      => $item->fields['entities_id'],
                            'entity_sons' => $item->fields["is_recursive"]));

      echo "</td>";
      echo "<td><input type='hidden' name='consumables_id' value='" . $item->getID() . "'>";
      echo "<input type='hidden' name='id' value='" . $data['id'] . "'>";
      echo "<input type='submit' class='submit' name='add_groups' value='" . _sx('button', 'Add') . "'></td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();

   }

   /**
    * @param array|\datas $params
    *
    * @return array|\datas
    */
   function prepareInputForUpdate($params) {

      if (isset($params["add_groups"])) {
         $input = array();

         $restrict = "`id` = " . $params['id'];
         $configs  = getAllDatasFromTable("glpi_plugin_consumables_options", $restrict);

         $groups = array();
         if (!empty($configs)) {
            foreach ($configs as $config) {
               if (!empty($config["groups"])) {
                  $groups = json_decode($config["groups"], true);
                  if (count($groups) > 0) {
                     if (!in_array($params["_groups_id"], $groups)) {
                        array_push($groups, $params["_groups_id"]);
                     }
                  } else {
                     $groups = array($params["_groups_id"]);
                  }
               } else {
                  $groups = array($params["_groups_id"]);
               }
            }
         }

         $group = json_encode($groups);

         $input['id']     = $params['id'];
         $input['groups'] = $group;

      } else if (isset($params["delete_groups"])) {

         $restrict = "`id` = " . $params['id'];
         $configs  = getAllDatasFromTable("glpi_plugin_consumables_options", $restrict);

         $groups = array();
         if (!empty($configs)) {
            foreach ($configs as $config) {
               if (!empty($config["groups"])) {
                  $groups = json_decode($config["groups"], true);
                  if (count($groups) > 0) {
                     if (($key = array_search($params["_groups_id"], $groups)) !== false) {
                        unset($groups[$key]);
                     }
                  }
               }
            }
         }

         if (count($groups) > 0) {
            $group = json_encode($groups);
         } else {
            $group = "";
         }

         $input['id']     = $params['id'];
         $input['groups'] = $group;


      } else {
         $input = $params;
      }
      return $input;
   }

   /**
    * @return mixed
    */
   function getMaxCart() {
      return $this->fields['max_cart'];
   }

   /**
    * @return mixed
    */
   function getAllowedGroups() {
      return json_decode($this->fields['groups'], true);
   }
}