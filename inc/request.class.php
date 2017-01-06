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
class PluginConsumablesRequest extends CommonDBTM
{

   static $rightname = "plugin_consumables";

   /**
    * @param int $nb
    * @return translated
    */
   static function getTypeName($nb = 0)
   {
      return _n('Consumable request', 'Consumable requests', 1, 'consumables');
   }

   /**
    * Have I the global right to "request" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    * */
   static function canRequest()
   {
      return Session::haveRight("plugin_consumables_request", 1);
   }

   /**
    * Have I the global right to "request user" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    * */
   static function canRequestUser()
   {
      return Session::haveRight("plugin_consumables_user", 1);
   }

   /**
    * Have I the global right to "request group" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    * */
   static function canRequestGroup()
   {
      return Session::haveRight("plugin_consumables_group", 1);
   }

   /**
    * Display tab for each users
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {

      if (!$withtemplate) {
         if ($item->getType() == 'User' && self::canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(self::getTypeName());
            }
            return self::getTypeName();
         } else if ($item->getType() == 'ConsumableItem' && self::canView()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(self::getTypeName(), countElementsInTable($this->getTable(), "`consumables_id` = '" . $item->getID() . "'"));
            }
            return self::getTypeName();
         }
      }

      return '';
   }

   /**
    * Display content for each users
    *
    * @static
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      $field = new self();

      if ($item->getType() == 'User' && self::canView()) {
         $field->showForUser($item);
      } else if ($item->getType() == 'ConsumableItem' && self::canView()) {
         $field->showForConsumable($item);
      }

      return true;
   }

   /**
    * Show
    *
    * @param type $item
    * @return bool
    */
   function showForConsumable($item)
   {

      if (!$this->canView()) {
         return false;
      }

      $data = $this->find('`consumables_id` = ' . $item->fields['id'], "`date_mod` DESC");

      $this->listItemsForConsumable($data);
   }

   /**
    * Show list of items
    *
    * @param type $fields
    */
   function listItemsForConsumable($fields)
   {

      if (!empty($fields)) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='6'>" . __('Consumable request report', 'consumables') . "</th>";
         echo "</tr>";
         echo "<tr>";
         echo "<th>" . __('Requester') . "</th>";
         echo "<th>" . __('Approver') . "</th>";
         echo "<th>" . __('Number of used consumables') . "</th>";
         echo "<th>" . __('Request date') . "</th>";
         echo "<th>" . __("Give to") . "</th>";
         echo "<th>" . __("Status") . "</th>";
         echo "</tr>";

         foreach ($fields as $field) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . getUserName($field['requesters_id']) . "</td>";
            echo "<td>" . getUserName($field['validators_id']) . "</td>";
            echo "<td>" . $field['number'] . "</td>";
            echo "<td>" . Html::convDateTime($field['date_mod']) . "</td>";
            echo "<td>";
            if (!empty($field['give_itemtype'])) {
               $give_item = getItemForItemtype($field['give_itemtype']);
               $give_item->getFromDB($field['give_items_id']);
               echo $give_item->getLink();
            }
            echo "</td>";
            echo "<td>";
            $bgcolor = CommonITILValidation::getStatusColor($field['status']);
            $status = CommonITILValidation::getStatus($field['status']);
            echo "<div style='background-color:" . $bgcolor . ";'>" . $status . "</div>";
            echo "</td>";
            echo "</tr>";
         }

         echo "</table>";
         echo "</div>";
         echo "</div>";
      } else {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='6'>" . __('Consumable requests history', 'consumables') . "</th>";
         echo "</tr>";
         echo "<tr><td class='center'>" . __('No item found') . "</td></tr>";
         echo "</table>";
         echo "</div>";
      }
   }

   /**
    * Show
    *
    * @param type $item
    * @param array $options
    * @return bool
    */
   function showForUser($item, $options = array())
   {
      global $CFG_GLPI;

      if (!$this->canView()) {
         return false;
      }

      $begin_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . "-1 MONTH"));
      $end_date = date('Y-m-d H:i:s');

      echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL($this->getType()) . "' id='consumables_formSearchConsumables'>";
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='6'>" . __('Consumables request search', 'consumables') . "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      _e('Begin date');
      echo "</td>";
      echo "<td>";
      Html::showDateTimeField("begin_date", array('value' => $begin_date));
      echo "</td>";
      echo "<td>";
      _e('End date');
      echo "</td>";
      echo "<td>";
      Html::showDateTimeField("end_date", array('value' => $end_date));
      echo "</td>";
      echo "<td>";
      echo "<input type=\"button\" class=\"submit\" name=\"addToCart\" onclick=\"consumables_searchConsumables('searchConsumables','consumables_formSearchConsumables', 'consumables_searchConsumables');\" value=\"" . __('Search') . "\">";
      echo "<input type='hidden' name='requesters_id' value='" . $item->fields['id'] . "' >";
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      Html::closeForm();

      echo "<div class='center' id='consumables_searchConsumables'>";
      $result = $this->listItemsForUser($item->fields['id'], array('begin_date' => $begin_date, 'end_date' => $end_date));
      echo $result['message'];
      echo "</div>";

      echo "<div id='dialog-confirm'></div>";

      // Init consumable cart javascript
      echo "<script type='text/javascript'>";
      echo "consumables_initJs('" . $CFG_GLPI['root_doc'] . "');";
      echo "</script>";
   }

   /**
    * Show list of items
    *
    * @param $requesters_id
    * @param array $options
    * @return array
    * @internal param type $fields
    */
   function listItemsForUser($requesters_id, $options = array())
   {

      $params['begin_date'] = "NULL";
      $params['end_date'] = "NULL";

      foreach ($options as $key => $val) {
         $params[$key] = $val;
      }

      $data = $this->find('`requesters_id` = ' . $requesters_id . " "
         . "AND (`end_date` >= '" . $params['begin_date'] . "'  OR `end_date` IS NULL) "
         . "AND (`end_date` <= '" . $params['end_date'] . "' OR `end_date` IS NULL)", "`end_date` DESC");

      $message = null;
      if (!empty($data)) {
         $message .= "<table class='tab_cadre_fixe'>";
         $message .= "<tr>";
         $message .= "<th colspan='7'>" . __('Consumable request report', 'consumables') . "</th>";
         $message .= "</tr>";
         $message .= "<tr>";
         $message .= "<th>" . _n('Consumable', 'Consumables', 1) . "</th>";
         $message .= "<th>" . _n('Consumable type', 'Consumable types', 1) . "</th>";
         $message .= "<th>" . __('Requester') . "</th>";
         $message .= "<th>" . __('Approver') . "</th>";
         $message .= "<th>" . __('Number of used consumables') . "</th>";
         $message .= "<th>" . __('Request date') . "</th>";
         $message .= "<th>" . __('Status') . "</th>";
         $message .= "</tr>";

         $consumable = new ConsumableItem();
         foreach ($data as $field) {
            $message .= "<tr class='tab_bg_1'>";
            $consumable->getFromDB($field['consumables_id']);
            $message .= "<td>" . $consumable->getLink() . "</td>";
            $message .= "<td>" . Dropdown::getDropdownName(ConsumableItemType::getTable(), $field['consumableitemtypes_id']) . "</td>";
            $message .= "<td>" . getUserName($field['requesters_id']) . "</td>";
            $message .= "<td>" . getUserName($field['validators_id']) . "</td>";
            $message .= "<td>" . $field['number'] . "</td>";
            $message .= "<td>" . Html::convDateTime($field['date_mod']) . "</td>";
            $message .= "<td>";
            $bgcolor = CommonITILValidation::getStatusColor($field['status']);
            $status = CommonITILValidation::getStatus($field['status']);
            $message .= "<div style='background-color:" . $bgcolor . ";'>" . $status . "</div>";
            $message .= "</td>";
            $message .= "</tr>";
         }

         $message .= "</table>";
         $message .= "</div>";
      } else {
         $message .= "<div class='center'>";
         $message .= "<table class='tab_cadre_fixe'>";
         $message .= "<tr>";
         $message .= "<th colspan='6'>" . __('Consumable request report', 'consumables') . "</th>";
         $message .= "</tr>";
         $message .= "<tr><td class='center'>" . __('No item found') . "</td></tr>";
         $message .= "</table>";
      }

      return array('success' => true, 'message' => $message);
   }

   /**
    * Show consumable request
    */
   function showConsumableRequest()
   {
      global $CFG_GLPI;

      if (!$this->canView()) {
         return false;
      }

      $request = new PluginConsumablesRequest();
      $request->getEmpty();

      // Wizard title
      echo "<form name='wizard_form' id='consumables_wizardForm' method='post'>";
      echo "<div class='consumables_wizard_title'><p>";
      echo "<img class='consumables_wizard_img' src='" . $CFG_GLPI['root_doc'] . "/plugins/consumables/pics/consumablerequest.png' alt='consumablerequest'/>&nbsp;";
      _e("Consumable request", "consumables");
      echo "</p></div>";

      // Add consumables request
      echo "<table class='tab_cadre_fixe consumables_wizard_rank'>";
      echo "<tr>";
      echo "<th colspan='4'>" . __("Consumable request", "consumables") . "</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<td>" . __('Requester') . "</td>";
      echo "<td>";
      echo getUserName(Session::getLoginUserID());
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>" . _n('Consumable type', 'Consumable types', 1) . " <span class='red'>*</span></td>";
      echo "<td>";
      $rand = Dropdown::show("ConsumableItemType", array('entity' => $_SESSION['glpiactive_entity'], 'on_change' => 'loadAvailableConsumables(this);'));
      $script = "function loadAvailableConsumables(object){this.consumableTypeID = object.value; consumables_reloadAvailableConsumables();}";
      echo Html::scriptBlock($script);
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td>" . _n('Consumable', 'Consumables', 1) . " <span class='red'>*</span></td>";
      echo "<td id='loadAvailableConsumables'>";
      $this->loadAvailableConsumables();
      echo "</td>";

      echo "</tr>";

      echo "<tr>";
      echo "<td>" . __('Number', 'consumables') . " <span class='red'>*</span></td>";
      echo "<td id='loadAvailableConsumablesNumber'>";
      $this->loadAvailableConsumablesNumber();
      echo "</td>";
      echo "</tr>";

      if (self::canRequestGroup() || self::canRequestUser()) {
         $itemtypes = array();
         if (self::canRequestGroup()) {
            $itemtypes[] = "Group";
         }
         if (self::canRequestUser()) {
            $itemtypes[] = "User";
         }
         echo "<tr>";
         echo "<td>" . __("Give to") . "</td>";
         echo "<td>";
         self::showSelectItemFromItemtypes(array('itemtype_name' => 'give_itemtype',
            'items_id_name' => 'give_items_id',
            'entity_restrict' => $_SESSION['glpiactive_entity'],
            'itemtypes' => $itemtypes));
         echo "</td>";
         echo "</tr>";
      }

      if ($this->canCreate() && $this->canRequest()) {
         echo "<tr>";
         echo "<td class='center' colspan='4'>";
         echo "<input type=\"button\" class=\"submit\" name=\"addToCart\" onclick=\"consumables_addToCart('addToCart','consumables_wizardForm', 'consumables_cart');\" value=\"" . __('Add to cart', 'consumables') . "\">";
         echo "</td>";
         echo "</tr>";
      }
      echo "</table>";

      // Cart
      echo "<br><div class='center'>";
      echo "<table class='tab_cadre_fixe consumables_wizard_rank' id='consumables_cart' style='display:none'>";
      echo "<tr><th colspan='7'>" . __("Cart", "consumables") . "</th></tr>";
      echo "<tr>";
      echo "<th>" . __('Requester') . "</th>";
      echo "<th>" . _n('Consumable type', 'Consumable types', 1) . "</th>";
      echo "<th>" . _n('Consumable', 'Consumables', 1) . "</th>";
      echo "<th>" . __('Number', 'consumables') . "</th>";
      echo "<th>" . __("Give to") . "</th>";
      echo "<th></th>";
      echo "</tr>";
      echo "</table>";
      echo "</div>";

      // Footer
      if ($this->canCreate() && $this->canRequest()) {
         echo "<br/><table width='100%'>";
         echo "<tr>";
         echo "<td class='consumables_wizard_button'>";
         echo "<div id='dialog-confirm'></div>";
         echo "<input type=\"button\" class=\"submit consumable_next_button\" name=\"addConsumables\" value=\"" . _sx('button', 'Post') . "\" onclick=\"consumables_addConsumables('addConsumables','consumables_wizardForm');\">";
         echo "<input type=\"button\" class=\"consumable_previous_button submit\" name=\"previous\" value=\"" . _sx('button', 'Cancel') . "\" onclick=\"consumables_cancel('" . $CFG_GLPI['root_doc'] . "/plugins/consumables/front/wizard.php');\">";
         echo "</td>";
         echo "</tr>";
         echo "</table>";
      }

      // Init consumable cart javascript
      echo "<script type='text/javascript'>";
      echo "consumables_initJs('" . $CFG_GLPI['root_doc'] . "', 'dropdown_consumable_itemtypes_id$rand');";
      echo "</script>";

      Html::closeForm();
   }

   /**
    * Make a select box for all items
    *
    * @since version 0.85
    *
    * @param $options array:
    *   - itemtype_name        : the name of the field containing the itemtype (default 'itemtype')
    *   - items_id_name        : the name of the field containing the id of the selected item
    *                            (default 'items_id')
    *   - itemtypes            : all possible types to search for (default: $CFG_GLPI["state_types"])
    *   - default_itemtype     : the default itemtype to select (don't define if you don't
    *                            need a default) (defaut 0)
    *    - entity_restrict     : restrict entity in searching items (default -1)
    *    - onlyglobal          : don't match item that don't have `is_global` == 1 (false by default)
    *    - checkright          : check to see if we can "view" the itemtype (false by default)
    *    - showItemSpecificity : given an item, the AJAX file to open if there is special
    *                            treatment. For instance, select a Item_Device* for CommonDevice
    *    - emptylabel          : Empty choice's label (default self::EMPTY_VALUE)
    *
    * @return randomized value used to generate HTML IDs
    * */
   static function showSelectItemFromItemtypes(array $options = array())
   {
      global $CFG_GLPI;

      $params = array();
      $params['itemtype_name'] = 'itemtype';
      $params['items_id_name'] = 'items_id';
      $params['itemtypes'] = '';
      $params['default_itemtype'] = 0;
      $params['entity_restrict'] = -1;
      $params['onlyglobal'] = false;
      $params['checkright'] = false;
      $params['showItemSpecificity'] = '';
      $params['condition'] = '';
      $params['emptylabel'] = Dropdown::EMPTY_VALUE;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $rand = Dropdown::showItemType($params['itemtypes'], array('checkright' => $params['checkright'],
         'name' => $params['itemtype_name'],
         'emptylabel' => $params['emptylabel']));

      if ($rand) {
         $p = array('idtable' => '__VALUE__',
            'name' => $params['items_id_name'],
            'entity_restrict' => $params['entity_restrict'],
            'showItemSpecificity' => $params['showItemSpecificity']);

         $field_id = Html::cleanId("dropdown_" . $params['itemtype_name'] . $rand);
         $show_id = Html::cleanId("show_" . $params['items_id_name'] . $rand);

         Ajax::updateItemOnSelectEvent($field_id, $show_id, $CFG_GLPI["root_doc"] . "/plugins/consumables/ajax/dropdownAllItems.php", $p);

         echo "<br><span id='$show_id'>&nbsp;</span>\n";

         // We check $options as the caller will set $options['default_itemtype'] only if it needs a
         // default itemtype and the default value can be '' thus empty won't be valid !
         if (array_key_exists('default_itemtype', $options)) {
            echo "<script type='text/javascript' >\n";
            echo Html::jsSetDropdownValue($field_id, $params['default_itemtype']);
            echo "</script>\n";

            $p["idtable"] = $params['default_itemtype'];
            Ajax::updateItem($show_id, $CFG_GLPI["root_doc"] . "/ajax/dropdownAllItems.php", $p);
         }
      }
      return $rand;
   }

   /**
    * Reload consumables list
    *
    * @param int|type $used
    * @param int $type
    * @return array
    */
   function loadAvailableConsumables($used = 0, $type = 0)
   {

      Dropdown::show("ConsumableItem", array('name' => 'consumables_id',
         'condition' => "`consumableitemtypes_id` = '$type'",
         'entity' => $_SESSION['glpiactive_entity'],
         'on_change' => 'loadAvailableConsumablesNumber(this);'
      ));

      $script = "function loadAvailableConsumablesNumber(object){this.consumableID = object.value; consumables_reloadAvailableConsumablesNumber();}";
      echo Html::scriptBlock($script);
   }

   /**
    * Reload consumables list
    *
    * @param int|type $used
    * @param int $consumables_id
    * @return array
    */
   function loadAvailableConsumablesNumber($used = 0, $consumables_id = 0)
   {

      $number = (self::countForConsumableItem($consumables_id)) - ($used);

      if ($number < 0) {
         $number = 0;
      }

      if ($number > 0) {
         Dropdown::showInteger('number', 0, 0, $number);
      } else {
         echo __('No consumable') . "<input type='hidden' name='number' value='0'>";
      }
   }

   /**
    * @param $consumables_id
    * @return int
    * @internal param string $item ConsumableItem object
    *
    */
   static function countForConsumableItem($consumables_id)
   {

      $restrict = "`glpi_consumables`.`consumableitems_id` = '" . $consumables_id . "' AND `glpi_consumables`.`date_out`IS NULL";

      return countElementsInTable(array('glpi_consumables'), $restrict);
   }

   /**
    * Add consumable to cart
    *
    * @param type $params
    * @return array
    */
   function addToCart($params)
   {

      list($success, $message) = $this->checkMandatoryFields($params);

      $result = array('success' => $success,
         'message' => $message,
         'rowId' => mt_rand(),
         'fields' => array(
            'requesters_id' => array('label' => getUserName(Session::getLoginUserID()),
               'value' => Session::getLoginUserID()),
            'consumableitemtypes_id' => array('label' => Dropdown::getDropdownName("glpi_consumableitemtypes", $params['consumableitemtypes_id']),
               'value' => $params['consumableitemtypes_id']),
            'consumables_id' => array('label' => Dropdown::getDropdownName("glpi_consumableitems", $params['consumables_id']),
               'value' => $params['consumables_id']),
            'number' => array('label' => $params['number'],
               'value' => $params['number']),
            'give_items_id' => array('label' => getUserName(Session::getLoginUserID()),
               'value' => Session::getLoginUserID()),
            'give_itemtype' => array('label' => User::getTypeName(),
               'value' => "User",
               'hidden' => 1)
         ));

      // Give to
      if (!empty($params['give_itemtype'])) {
         $give_item = getItemForItemtype($params['give_itemtype']);

         $result['fields']['give_itemtype'] = array('label' => $give_item::getTypeName(),
            'value' => $params['give_itemtype'],
            'hidden' => 1);
         if($give_item::getType() == "User"){
            $result['fields']['give_items_id'] = array('label' => getUserName($params['give_items_id']),
            'value' => $params['give_items_id']);
         } else { // $give_item::getUserName() == "Group"
            $result['fields']['give_items_id'] = array('label' => Dropdown::getDropdownName($give_item->getTable(), $params['give_items_id']),
            'value' => $params['give_items_id']);
         }
      }

      return $result;
   }

   /**
    * Save consumables in database
    * @param $params
    * @return array
    */
   function addConsumables($params)
   {

      if (isset($params['consumables_cart'])) {
         $added = array();
         foreach ($params['consumables_cart'] as $row) {
            list($success, $message) = $this->checkMandatoryFields($row);
            if ($success) {
//               $consumableExist = $this->find("`consumables_id` = ".$row['consumables_id']." "
//                                             . "AND `status` = '".CommonITILValidation::NONE."' "
//                                             . "AND `give_itemtype` = '".$row['give_itemtype']."'"
//                                             . "AND `give_items_id` = '".$row['give_items_id']."'"
//                                             . "AND `requesters_id` = '".$row['requesters_id']."'");
//               if (empty($consumableExist)) {
               $input = array('consumableitemtypes_id' => $row['consumableitemtypes_id'],
                  'consumables_id' => $row['consumables_id'],
                  'number' => $row['number'],
                  'date_mod' => date("Y-m-d H:i:s"),
                  'give_items_id' => $row['give_items_id'],
                  'give_itemtype' => $row['give_itemtype'],
                  'validators_id' => 0,
                  'status' => CommonITILValidation::WAITING,
                  'requesters_id' => Session::getLoginUserID());

               if ($this->add($input)) {
                  $added[] = $this->fields;
               }

//               } else {
//                  $consumableExist = reset($consumableExist);
//                  $input = array('id'                     => $consumableExist['id'],
//                                 'consumableitemtypes_id' => $row['consumableitemtypes_id'],
//                                 'consumables_id'         => $row['consumables_id'],
//                                 'number'                 => $row['number'] + $consumableExist['number'],
//                                 'end_date'               => $row['end_date'],
//                                 'give_items_id'          => $row['give_items_id'],
//                                 'give_itemtype'          => $row['give_itemtype'],
//                                 'requesters_id'          => Session::getLoginUserID());
//                  $added[] = $input;
//                  $this->update($input);
//               }

               $message = _n('Consumable affected', 'Consumables affected', count($params['consumables_cart']), 'consumables');
            }
         }

         // Send notification
         if (!empty($added)) {
            NotificationEvent::raiseEvent(PluginConsumablesNotificationTargetRequest::CONSUMABLE_REQUEST, $this,
               array('entities_id' => $_SESSION['glpiactive_entity'],
                  'consumables' => $added));
         }
      } else {
         $success = false;
         $message = __('Please add consumables in cart', 'consumables');
      }

      return array('success' => $success,
         'message' => $message);
   }

   /**
    * Get used consumables
    */
   function getUsedConsumables()
   {

      $used = array();
      $datas = $this->find();
      if (!empty($datas)) {
         foreach ($datas as $data) {
            $used[] = $data['consumables_id'];
         }
      }

      return $used;
   }

   /**
    * Get consumables of a given user
    *
    * @param type $users_id
    * @param string $condition
    * @param string $order
    * @return type
    */
   function getUserConsumables($users_id, $condition = "1", $order = "")
   {

      $query = null;

      if (!empty($users_id)) {
         $query .= " `requesters_id` = $users_id";
      }
      $datas = $this->find("$query AND $condition");

      return $datas;
   }

   /**
    * Check mandatory fields
    *
    * @param type $input
    * @return boolean
    */
   function checkMandatoryFields($input)
   {
      $msg = array();
      $checkKo = false;

      $mandatory_fields = array('consumableitemtypes_id' => _n('Consumable type', 'Consumable types', 1),
         'consumables_id' => _n('Consumable', 'Consumables', 1),
         'number' => __('Number', 'consumables'));

      foreach ($input as $key => $value) {
         if (isset($mandatory_fields[$key])) {
            if (empty($value) || $value == 'NULL') {
               $msg[] = $mandatory_fields[$key];
               $checkKo = true;
            }
         }
      }

      if ($checkKo) {
         return array(false, sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)));
      }

      return array(true, null);
   }

}