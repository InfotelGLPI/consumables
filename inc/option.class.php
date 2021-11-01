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
      $data = [];
      if ($this->getFromDBByCrit(["consumables_id" => $item->fields['id']])) {
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
      Dropdown::showNumber('max_cart', ['value' => $data['max_cart'],
                                        'max'   => 100]);
      echo " </td>";
      if ($this->canCreate()) {
         echo "<td class='center'>";
         echo Html::submit(_sx('button', 'Define', 'consumables'), ['name' => 'update', 'class' => 'btn btn-primary']);
         echo "</td>";
      }
      echo "</tr>";
      echo Html::hidden('consumables_id', ['value' => $data['consumables_id']]);
      echo Html::hidden('id', ['value' => $ID]);
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
                                 ['delete_groups' => 'delete_groups',
                                  'id'            => $ID,
                                  '_groups_id'    => $val],
                                 'fa-times-circle');
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

      echo Html::hidden('consumables_id', ['value' => $data['consumables_id']]);
      echo Html::hidden('id', ['value' => $ID]);
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

      $used = ($data["groups"] == '' ? [] : json_decode($data["groups"], true));

      Group::dropdown(['name'        => '_groups_id',
                       'used'        => $used,
                       'entity'      => $item->fields['entities_id'],
                       'entity_sons' => $item->fields["is_recursive"]]);

      echo "</td>";
      echo "<td>";
      echo Html::hidden('consumables_id', ['value' => $item->getID()]);
      echo Html::hidden('id', ['value' => $data['id']]);
      echo Html::submit(_sx('button', 'Add'), ['name' => 'add_groups', 'class' => 'btn btn-primary']);
      echo "</td>";
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
      $dbu = new DbUtils();

      if (isset($params["add_groups"])) {
         $input = [];

         $restrict = ["id" => $params['id']];
         $configs  = $dbu->getAllDataFromTable("glpi_plugin_consumables_options", $restrict);

         $groups = [];
         if (!empty($configs)) {
            foreach ($configs as $config) {
               if (!empty($config["groups"])) {
                  $groups = json_decode($config["groups"], true);
                  if (count($groups) > 0) {
                     if (!in_array($params["_groups_id"], $groups)) {
                        array_push($groups, $params["_groups_id"]);
                     }
                  } else {
                     $groups = [$params["_groups_id"]];
                  }
               } else {
                  $groups = [$params["_groups_id"]];
               }
            }
         }

         $group = json_encode($groups);

         $input['id']     = $params['id'];
         $input['groups'] = $group;

      } else if (isset($params["delete_groups"])) {

         $restrict = ["id" => $params['id']];
         $configs  = $dbu->getAllDataFromTable("glpi_plugin_consumables_options", $restrict);

         $groups = [];
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
      if(!empty($this->fields['groups'])) {
         return json_decode($this->fields['groups'], true);
      } else {
         return [];
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "add_number":
            echo "</br>&nbsp;" . __('Maximum number allowed for request', 'consumables') . " : ";
            Dropdown::showNumber('max_cart', ['value' => 0,
                                              'min'   => 0,
                                              'max'   => 100]);
            echo "&nbsp;" .
                 Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;

         case "add_groups":
            echo "</br>&nbsp;" . __('Add a group for request', 'consumables') . " : ";
            Group::dropdown(['name' => '_groups_id']);
            echo "&nbsp;" .
                 Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
            break;
      }

   }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $option = new self();

      switch ($ma->getAction()) {
         case "add_number":
            $input = $ma->getInput();
            foreach ($ids as $id) {

               $input = ['max_cart'       => $input['max_cart'],
                         'consumables_id' => $id];

               if ($item->getFromDB($id)) {
                  if ($option->getFromDBByCrit(["consumables_id" => $id])) {

                     $input['id'] = $option->getID();
                     if ($option->can(-1, UPDATE, $input) && $option->update($input)) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                     } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                     }

                  } else {
                     if ($option->can(-1, CREATE, $input) && $option->add($input)) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);

                     } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                     }
                  }
               }

            }
            return;

         case "add_groups":
            $input = $ma->getInput();
            foreach ($ids as $id) {

               if ($item->getFromDB($id)) {
                  if ($option->getFromDBByCrit(["consumables_id" => $id])) {
                     $groups = json_decode($option->fields["groups"], true);

                     if (count($groups) > 0) {
                        if (!in_array($input["_groups_id"], $groups)) {
                           array_push($groups, $input["_groups_id"]);
                        }
                     } else {
                        $groups = [$input["_groups_id"]];
                     }

                     $params = ['id'     => $option->getID(),
                                'groups' => json_encode($groups)];

                     $params['id'] = $option->getID();
                     if ($option->can(-1, UPDATE, $params) && $option->update($params)) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                     } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                     }

                  } else {
                     $params = ['consumables_id' => $id,
                                'groups'         => json_encode([$input['_groups_id']])];

                     if ($option->can(-1, CREATE, $params) && $option->add($params)) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);

                     } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                     }
                  }
               }

            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   /**
    * @param $field
    * @param $values
    * @param $options   array
    **/
   static function getSpecificValueToDisplay($field, $values, array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'groups':
            $list_groups = '';
            $groups      = json_decode($values['groups'], true);
            if (!empty($groups)) {
               foreach ($groups as $key => $val) {
                  $list_groups .= Dropdown::getDropdownName("glpi_groups", $val) . "<br>";
               }
            }
            return $list_groups;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }
}
