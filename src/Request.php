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

namespace GlpiPlugin\Consumables;
use \CommonDBTM;
use \CommonGLPI;

use Ajax;
use CommonDBTM;
use CommonGLPI;
use CommonITILValidation;
use ConsumableItem;
use ConsumableItemType;
use DbUtils;
use Dropdown;
// declare(strict_types=1); moved to top
use Group;
use Group_User;
use Html;
use NotificationEvent;
use Session;
use Toolbox;
use User;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Request
 *
 */

/**
 * Class Request
 */
class Request extends CommonDBTM
{
    // Fallback for static analysis: canView
    public static function canView() { return true; }
    public static $rightname = 'plugin_consumables';

    /**
     * @param int $nb
     *
     * @return string
     */
    /**
     * @param int $nb
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return _n('Consumable request', 'Consumable requests', 1, 'consumables');
    }

    /**
     * @return string
     */
    public static function getIcon(): string
    {
        return 'ti ti-shopping-cart';
    }

    /**
     * Have I the global right to "request" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return bool|int
     * */
    /**
     * @return bool|int
     */
    public static function canRequest()
    {
        return Session::haveRight('plugin_consumables_request', 1);
    }

    /**
     * @return bool|int
     */
    public static function canValidate()
    {
        return Session::haveRight('plugin_consumables_validation', 1);
    }

    /**
     * Have I the global right to "request user" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return bool|int
     * */
    /**
     * @return bool|int
     */
    public static function canRequestUser()
    {
        return Session::haveRight('plugin_consumables_user', 1);
    }

    /**
     * @param string $field
     * @param array|string $values
     * @param array $options
     * @return string
     */
    public static function getSpecificValueToDisplay($field, $values, array $options = []): string
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $dbu = new DbUtils();
        switch ($field) {
            case 'status':
                return CommonITILValidation::getStatus($values['status']);
            case 'give_items_id':
                if (!empty($values['give_itemtype'])) {
                    $give_item = $dbu->getItemForItemtype($values['give_itemtype']);
                    $give_item->getFromDB($values['give_items_id']);
                    return $give_item->getLink();
                }
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    /**
     * Have I the global right to "request group" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return bool|int
     * */
    /**
     * @return bool|int
     */
    public static function canRequestGroup()
    {
        return Session::haveRight('plugin_consumables_group', 1);
    }

    /**
     * Display tab for each users
     *
     * @param CommonGLPI $item
     * @param int        $withtemplate
     *
     * @return array|string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $dbu = new DbUtils();
        if (!$withtemplate) {
            if ($item->getType() == 'User' && self::canView()) {
                if ($_SESSION['glpishow_count_on_tabs']) {

                    return self::createTabEntry(
                        self::getTypeName(),
                        $dbu->countElementsInTable(
                            $this->getTable(),
                            ["give_itemtype" => "User", "give_items_id" => $item->getID()]
                        )
                    );
                }
                return self::getTypeName();
            } elseif ($item->getType() == 'Group' && self::canView()) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(
                        self::getTypeName(),
                        $dbu->countElementsInTable(
                            $this->getTable(),
                            ["give_itemtype" => "Group", "give_items_id" => $item->getID()]
                        )
                    );
                }
                return self::getTypeName();
            } elseif ($item->getType() == 'ConsumableItem' && self::canView()) {
                if ($_SESSION['glpishow_count_on_tabs']) {

                    return self::createTabEntry(
                        self::getTypeName(),
                        $dbu->countElementsInTable(
                            $this->getTable(),
                            ["consumableitems_id" => $item->getID()]
                        )
                    );
                }
                return self::createTabEntry(self::getTypeName());
            }
        }

        return '';
    }

    /**
     * Display content for each users
     *
     * @static
     *
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     *
     * @return bool|true
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $field = new self();

        if ($item->getType() == 'User' && self::canView()) {
            $field->showForUserOrGroup($item, User::getType(), []);
        } elseif ($item->getType() == 'Group' && self::canView()) {
            $field->showForUserOrGroup($item, Group::getType(), []);
        } elseif ($item->getType() == 'ConsumableItem' && self::canView()) {
            $options = new Option();
            $options->showForConsumable($item);
            $field->showForConsumable($item);
        }

        return true;
    }

    /**
     * Show
     *
     * @param  $item
     *
     * @return bool
     */
    public function showForConsumable($item)
    {
        if (!$this->canView()) {
            return false;
        }

        $data = $this->find(['consumableitems_id' => (($item->fields['id'] ?? ''))], ["date_mod DESC"]);

        $this->listItemsForConsumable($data);
    }

    /**
     * Show list of items
     *
     * @param $fields
     */
    public function listItemsForConsumable($fields)
    {
        $dbu = new DbUtils();

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
                $status  = CommonITILValidation::getStatus($field['status']);
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
            echo "<tr><td class='center'>" . __s('No results found') . "</td></tr>";
            echo "</table>";
            echo "</div>";
        }
    }

    /**
     * Show
     *
     * @param   $item
     * @param array $options
     * @param   $type
     *
     * @return bool
     */
    public function showForUserOrGroup($item, $type, $options = [])
    {
        global $CFG_GLPI;

        if (!$this->canView()) {
            return false;
        }

        $begin_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . "-1 MONTH"));
        $end_date   = date('Y-m-d H:i:s');

        echo "<form name='form' method='post' action='' id='consumables_formSearchConsumables'>";
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<th colspan='6'>" . __('Consumables request search', 'consumables') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Begin date');
        echo "</td>";
        echo "<td>";
        Html::showDateTimeField("begin_date", ['value' => $begin_date]);
        echo "</td>";
        echo "<td>";
        echo __('End date');
        echo "</td>";
        echo "<td>";
        Html::showDateTimeField("end_date", ['value' => $end_date]);
        echo "</td>";
        echo "<td>";
        echo Html::submit(__('Search'), [
            'name'    => 'addToCart',
            'class'   => 'btn btn-primary',
            'onclick' => "consumables_searchConsumables('searchConsumables','consumables_formSearchConsumables', 'consumables_searchConsumables','$type')",
        ]);
        echo Html::hidden('requesters_id', ['value' => (($item->fields['id'] ?? ''))]);
        echo "</td>";
        echo "</tr>";
        echo "</table></div>";
        Html::closeForm();

        echo "<div class='center' id='consumables_searchConsumables'>";
        $result = $this->listItemsForUserOrGroup((($item->fields['id'] ?? '')), $type, ['begin_date' => $begin_date,
            'end_date'   => $end_date]);
        echo $result['message'];
        echo "</div>";
        Html::requireJs('glpi_dialog');
        echo "<div id='dialog-confirm'></div>";

        //        Html::requireJs('consumables');

        // Init consumable cart javascript
        echo Html::scriptBlock('$(document).ready(function() {consumables_initJs("' . PLUGIN_CONSUMABLES_WEBDIR . '");});');
    }

    /**
     * Show list of items
     *
     * @param       $requesters_id
     * @param array $options
     * @param       $type
     *
     * @return array
     * @internal param type $fields
     */
    public function listItemsForUserOrGroup($requesters_id, $type, $options = [])
    {
        $params['begin_date'] = "NULL";
        $params['end_date']   = "NULL";

        $dbu = new DbUtils();

        foreach ($options as $key => $val) {
            $params[$key] = $val;
        }

        $data = $this->find(
            ['give_items_id' => $requesters_id,
                'give_itemtype' => $type,
                [
                    'OR' => [
                        ['end_date' => ['>=', $params['end_date']]],
                        ['end_date' => null],
                    ],
                ],
                [
                    'OR' => [
                        ['end_date' => ['>=', $params['end_date']]],
                        ['end_date' => null],
                    ],
                ]],
            ["end_date DESC"]
        );

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
                $consumable->getFromDB($field['consumableitems_id']);
                $message .= "<td>" . $consumable->getLink() . "</td>";
                $message .= "<td>" . Dropdown::getDropdownName(ConsumableItemType::getTable(), $field['consumableitemtypes_id']) . "</td>";
                $message .= "<td>" . getUserName($field['requesters_id']) . "</td>";
                $message .= "<td>" . getUserName($field['validators_id']) . "</td>";
                $message .= "<td>" . $field['number'] . "</td>";
                $message .= "<td>" . Html::convDateTime($field['date_mod']) . "</td>";
                $message .= "<td>";
                $bgcolor = CommonITILValidation::getStatusColor($field['status']);
                $status  = CommonITILValidation::getStatus($field['status']);
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
            $message .= "<tr><td class='center'>" . __s('No results found') . "</td></tr>";
            $message .= "</table>";
        }

        return ['success' => true, 'message' => $message];
    }

    /**
     * Show consumable request
     */
    public function showConsumableRequest()
    {
        global $CFG_GLPI;

        if (!$this->canView() && !$this->canRequest()) {
            return false;
        }

        $request = new Request();
        $request->getEmpty();
        $dbu = new DbUtils();

        // Wizard title
        echo "<form name='wizard_form' id='consumables_wizardForm' method='post'>";

        echo "<h3><div class='alert alert-secondary'>";
        echo "<i class='thumbnail ti ti-shopping-cart-plus fa-2x'></i>";
        echo "&nbsp;";
        echo __("Consumable request", "consumables");
        echo "</div></h3>";

        // Add consumables request
        echo "<div style='overflow-x:auto;'>";
        echo "<table class='tab_cadre_fixe consumables_wizard_rank'>";
        echo "<tr>";
        echo "<th colspan='4'>" . __("Consumable request", "consumables") . "</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>" . __('Requester') . "</td>";
        echo "<td>";
        echo $dbu->getUserName(Session::getLoginUserID());
        echo "</td>";
        echo "<td rowspan='4' id='seeConsumablesInfos'>";
        $this->seeConsumablesInfos();
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . _n('Consumable type', 'Consumable types', 1) . " <span style='color:red;'>*</span></td>";
        echo "<td>";
        Dropdown::show("ConsumableItemType", ['entity'    => $_SESSION['glpiactive_entity'],
            'on_change' => 'loadAvailableConsumables(this);']);
        $script = "function loadAvailableConsumables(object){this.consumableTypeID = object.value; consumables_reloadAvailableConsumables();}";
        echo Html::scriptBlock($script);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . _n('Consumable', 'Consumables', 1) . " <span style='color:red;'>*</span></td>";
        echo "<td id='loadAvailableConsumables'>";
        echo "</td>";
        echo "</tr>";


        echo "<tr>";
        echo "<td>" . __('Number', 'consumables') . " <span style='color:red;'>*</span></td>";
        echo "<td id='loadAvailableConsumablesNumber'>";
        $this->loadAvailableConsumablesNumber();
        echo "</td>";
        echo "</tr>";

        if (self::canRequestGroup() || self::canRequestUser()) {
            $itemtypes = [];
            if (self::canRequestGroup()) {
                $itemtypes[] = "Group";
            }
            if (self::canRequestUser()) {
                $itemtypes[] = "User";
            }
            echo "<tr>";
            echo "<td>" . __("Give to") . "</td>";
            echo "<td>";
            self::showSelectItemFromItemtypes(['itemtype_name'   => 'give_itemtype',
                'items_id_name'   => 'give_items_id',
                'entity_restrict' => $_SESSION['glpiactive_entity'],
                'itemtypes'       => $itemtypes]);
            echo "</td>";
            echo "</tr>";
        }

        if ($this->canCreate() || $this->canRequest()) {
            //            Html::requireJs('consumables');

            echo "<tr>";
            echo "<td class='center' colspan='4'>";
            echo "<a href='#' class='submit btn btn-info' name='addToCart'
         onclick=\"consumables_addToCart('addToCart','consumables_wizardForm', 'consumables_cart');\" >" . __('Add to cart', 'consumables') . "</a>";
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
        echo "</div>";

        // Footer
        if ($this->canCreate() || $this->canRequest()) {
            echo "<br/><table width='100%'>";
            echo "<tr>";
            echo "<td>";
            Html::requireJs('glpi_dialog');
            echo "<div id='dialog-confirm'></div>";
            echo "<a href='#' class='submit btn btn-success consumable_next_button' name='addConsumables'
               onclick=\"consumables_addConsumables('addConsumables','consumables_wizardForm');\">" . _sx('button', 'Post') . "</a>";
            echo "<a href='#' class='submit btn btn-warning consumable_previous_button'  name='previous'
               onclick=\"consumables_cancel('" . PLUGIN_CONSUMABLES_WEBDIR . "/front/wizard.php');\">" . _sx('button', 'Cancel') . "</a>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
        }

        //        Html::requireJs('consumables');

        // Init consumable cart javascript
        echo Html::scriptBlock('$(document).ready(function() {consumables_initJs("' . PLUGIN_CONSUMABLES_WEBDIR . '",
                                                            "dropdown_consumable_itemtypes_id$rand");});');

        Html::closeForm();
    }

    /**
     * Make a select box for all items
     *
     * @param $options array:
     *   - itemtype_name        : the name of the field containing the itemtype (default 'itemtype')
     *   - items_id_name        : the name of the field containing the id of the selected item
     *                            (default 'items_id')
     *   - itemtypes            : all possible types to search for (default:
     *    $CFG_GLPI["state_types"])
     *   - default_itemtype     : the default itemtype to select (don't define if you don't
     *                            need a default) (defaut 0)
     *    - entity_restrict     : restrict entity in searching items (default -1)
     *    - onlyglobal          : don't match item that don't have `is_global` == 1 (false by
     *    default)
     *    - checkright          : check to see if we can "view" the itemtype (false by default)
     *    - showItemSpecificity : given an item, the AJAX file to open if there is special
     *                            treatment. For instance, select a Item_Device* for CommonDevice
     *    - emptylabel          : Empty choice's label (default self::EMPTY_VALUE)
     *
     * @return randomized value used to generate HTML IDs
     * *@since version 0.85
     *
     */
    public static function showSelectItemFromItemtypes(array $options = [])
    {
        global $CFG_GLPI;

        $params                        = [];
        $params['itemtype_name']       = 'itemtype';
        $params['items_id_name']       = 'items_id';
        $params['itemtypes']           = '';
        $params['default_itemtype']    = 0;
        $params['entity_restrict']     = -1;
        $params['onlyglobal']          = false;
        $params['checkright']          = false;
        $params['showItemSpecificity'] = '';
        $params['condition']           = '';
        $params['emptylabel']          = Dropdown::EMPTY_VALUE;
        $params['display']             = true;
        $params['rand']                = mt_rand();

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }

        $rand = Dropdown::showItemType($params['itemtypes'], ['checkright' => $params['checkright'],
            'name'       => $params['itemtype_name'],
            'emptylabel' => $params['emptylabel'],
            'display'    => $params['display'],
            'rand'       => $params['rand']]);

        if ($rand) {
            $p = ['idtable'             => '__VALUE__',
                'name'                => $params['items_id_name'],
                'entity_restrict'     => $params['entity_restrict'],
                'showItemSpecificity' => $params['showItemSpecificity']];

            $field_id = Html::cleanId("dropdown_" . $params['itemtype_name'] . $rand);
            $show_id  = Html::cleanId("show_" . $params['items_id_name'] . $rand);

            Ajax::updateItemOnSelectEvent(
                $field_id,
                $show_id,
                PLUGIN_CONSUMABLES_WEBDIR . "/ajax/dropdownAllItems.php",
                $p
            );

            echo "<br><span id='$show_id'>&nbsp;</span>\n";

            // We check $options as the caller will set $options['default_itemtype'] only if it needs a
            // default itemtype and the default value can be '' thus empty won't be valid !
            if (array_key_exists('default_itemtype', $options)) {
                echo "<script type='text/javascript' >\n";
                echo Html::jsSetDropdownValue($field_id, $params['default_itemtype']);
                echo "</script>\n";

                $p["idtable"] = $params['default_itemtype'];
                Ajax::updateItem(
                    $show_id,
                    $CFG_GLPI["root_doc"] . "/ajax/dropdownAllItems.php",
                    $p
                );
            }
        }
        return $rand;
    }

    /**
     * Reload consumables list
     *
     * @param int $used
     * @param int      $type
     *
     * @return array
     */
    public function loadAvailableConsumables($type = 0)
    {
        $dbu             = new DbUtils();
        $restrict        = ["consumableitemtypes_id" => $type];
        $consumableitems = $dbu->getAllDataFromTable("glpi_consumableitems", $restrict);
        $crit            = "";
        $crit_ids        = [];

        if (!empty($consumableitems)) {
            foreach ($consumableitems as $consumableitem) {
                $groups = [];
                $option = new Option();
                if ($option->getFromDBByCrit(["consumableitems_id" => $consumableitem['id']])) {
                    $groups = $option->getAllowedGroups();
                }

                $notallowed = true;

                if (count($groups) > 0) {
                    $users_id = Session::getLoginUserID();
                    foreach (Group_User::getUserGroups($users_id) as $usergroups) {
                        if (in_array($usergroups["id"], $groups)) {
                            $notallowed = false;
                        }
                    }
                    if ($notallowed) {
                        $crit_ids[] = $consumableitem['id'];
                    }
                }
            }
        }
        $criteria = $restrict;
        if (count($crit_ids) > 0) {
            $criteria += ['NOT' => ['id' => $crit_ids]];
        }
        Dropdown::show("ConsumableItem", ['name'      => 'consumableitems_id',
            'condition' => $criteria,
            'entity'    => $_SESSION['glpiactive_entity'],
            'on_change' => 'loadAvailableConsumablesNumber(this);',
        ]);

        $script = "function loadAvailableConsumablesNumber(object){
      this.consumableID = object.value;
      consumables_reloadAvailableConsumablesNumber();
      consumables_seeConsumablesInfos();
      }";
        echo Html::scriptBlock($script);
    }


    /**
     * Reload consumables list
     *
     * @param int $used
     * @param int      $consumableitems_id
     *
     * @return array
     */
    public function seeConsumablesInfos($consumableitems_id = 0)
    {
        $consumable = new ConsumableItem();
        if ($consumable->getFromDB($consumableitems_id)) {
            //         $picture_url = Toolbox::getPictureUrl();
            //         Toolbox::logInfo($picture_url);
            if (isset($consumable->fields['pictures'])) {
                $pictures = json_decode((($consumable->fields['pictures'] ?? '')), true);
                if (isset($pictures) && is_array($pictures)) {
                    foreach ($pictures as $picture) {
                        $picture_url = Toolbox::getPictureUrl($picture);
                        echo "<img class='user_picture' alt=\"" . _sn('Picture', 'Pictures', 1) . "\" src='"
                             . $picture_url . "'>";
                        echo "</br>" . (($consumable->fields['comment'] ?? ''));
                    }
                }
            }
        }
    }

    /**
     * Reload consumables list
     *
     * @param int|type $used
     * @param int      $consumableitems_id
     *
     * @return array
     */
    public function loadAvailableConsumablesNumber($used = 0, $consumableitems_id = 0)
    {
        $number = self::countForConsumableItem($consumableitems_id);

        $maxcart = 0;
        $option  = new Option();
        if ($option->getFromDBByCrit(["consumableitems_id" => $consumableitems_id])) {
            $maxcart = $option->getMaxCart();
        }

        if ($maxcart > 0 && $number > $maxcart) {
            $number = $maxcart;
        }

        if (isset($used->$consumableitems_id)) {
            $number = $number - ($used->$consumableitems_id);
        }

        if ($number > 0) {
            Dropdown::showNumber('number', ['value' => 0,
                'max'   => $number]);
        } else {
            echo __('No consumable');
            echo Html::hidden('number', ['value' => 0]);
        }
    }

    /**
     * @param $consumableitems_id
     *
     * @return int
     * @internal param string $item ConsumableItem object
     *
     */
    public static function countForConsumableItem($consumableitems_id)
    {
        $restrict = ["consumableitems_id" => $consumableitems_id,
            "date_out"           => null];
        $dbu      = new DbUtils();
        return $dbu->countElementsInTable(['glpi_consumables'], $restrict);
    }

    /**
     * Add consumable to cart
     *
     * @param $params
     *
     * @return array
     */
    public function addToCart($params)
    {
        [$success, $message] = $this->checkMandatoryFields($params);
        $dbu = new DbUtils();

        if (isset($params['consumableitems_id'])) {
            $result = ['success' => $success,
                'message' => $message,
                'rowId'   => mt_rand(),
                'fields'  => [
                    'requesters_id'          => ['label' => $dbu->getUserName(Session::getLoginUserID()),
                        'value' => Session::getLoginUserID()],
                    'consumableitemtypes_id' => ['label' => Dropdown::getDropdownName("glpi_consumableitemtypes", $params['consumableitemtypes_id']),
                        'value' => $params['consumableitemtypes_id']],
                    'consumableitems_id'         => ['label' => Dropdown::getDropdownName("glpi_consumableitems", $params['consumableitems_id']),
                        'value' => $params['consumableitems_id']],
                    'number'                 => ['label' => $params['number'],
                        'value' => $params['number']],
                    'give_items_id'          => ['label' => $dbu->getUserName(Session::getLoginUserID()),
                        'value' => Session::getLoginUserID()],
                    'give_itemtype'          => ['label'  => User::getTypeName(),
                        'value'  => "User",
                        'hidden' => 1],
                ]];
        } else {
            $result = ['success' => $success,
                'message' => $message,
                'rowId'   => mt_rand(),
                'fields'  => []];
        }


        // Give to
        if (!empty($params['give_itemtype'])) {
            $give_item = $dbu->getItemForItemtype($params['give_itemtype']);

            $result['fields']['give_itemtype'] = ['label'  => $give_item::getTypeName(),
                'value'  => $params['give_itemtype'],
                'hidden' => 1];
            if ($give_item::getType() == "User") {
                $result['fields']['give_items_id'] = ['label' => $dbu->getUserName($params['give_items_id']),
                    'value' => $params['give_items_id']];
            } else { // $give_item::getUserName() == "Group"
                $result['fields']['give_items_id'] = ['label' => Dropdown::getDropdownName($give_item->getTable(), $params['give_items_id']),
                    'value' => $params['give_items_id']];
            }
        }

        return $result;
    }

    /**
     * Save consumables in database
     *
     * @param $params
     *
     * @return array
     */
    public function addConsumables($params)
    {
        if (isset($params['consumables_cart'])) {
            $added = [];
            foreach ($params['consumables_cart'] as $row) {
                [$success, $message] = $this->checkMandatoryFields($row);
                if ($success) {
                    //               $consumableExist = $this->find("`consumableitems_id` = ".$row['consumableitems_id']." "
                    //                                             . "AND `status` = '".CommonITILValidation::NONE."' "
                    //                                             . "AND `give_itemtype` = '".$row['give_itemtype']."'"
                    //                                             . "AND `give_items_id` = '".$row['give_items_id']."'"
                    //                                             . "AND `requesters_id` = '".$row['requesters_id']."'");
                    //               if (empty($consumableExist)) {
                    $input = ['consumableitemtypes_id' => $row['consumableitemtypes_id'],
                        'consumableitems_id'     => $row['consumableitems_id'],
                        'number'                 => $row['number'],
                        'date_mod'               => date("Y-m-d H:i:s"),
                        'give_items_id'          => $row['give_items_id'],
                        'give_itemtype'          => $row['give_itemtype'],
                        'validators_id'          => 0,
                        'status'                 => CommonITILValidation::WAITING,
                        'requesters_id'          => Session::getLoginUserID()];

                    if ($this->add($input)) {
                        $added[] = $this->fields;
                    }

                    //               } else {
                    //                  $consumableExist = reset($consumableExist);
                    //                  $input = ['id'                     => $consumableExist['id'],
                    //                                 'consumableitemtypes_id' => $row['consumableitemtypes_id'],
                    //                                 'consumableitems_id'         => $row['consumableitems_id'],
                    //                                 'number'                 => $row['number'] + $consumableExist['number'],
                    //                                 'end_date'               => $row['end_date'],
                    //                                 'give_items_id'          => $row['give_items_id'],
                    //                                 'give_itemtype'          => $row['give_itemtype'],
                    //                                 'requesters_id'          => Session::getLoginUserID()];
                    //                  $added[] = $input;
                    //                  $this->update($input);
                    //               }

                    $message = "<div class='alert alert-important alert-success d-flex'>" . _n('Consumable affected', 'Consumables affected', count($params['consumables_cart']), 'consumables') . "</div>";
                }
            }

            // Send notification
            if (!empty($added)) {
                foreach ($added as $add) {
                    $item = new self();
                    $item->getFromDB($add['id']);

                    NotificationEvent::raiseEvent(
                        NotificationTargetRequest::CONSUMABLE_REQUEST,
                        $item,
                        ['entities_id' => $_SESSION['glpiactive_entity'],
                            'consumables' => $add]
                    );
                }
            }
        } else {
            $success = false;
            $message = __('Please add consumables in cart', 'consumables');
        }

        return ['success' => $success,
            'message' => $message];
    }

    /**
     * Get used consumables
     */
    public function getUsedConsumables()
    {
        $used  = [];
        $datas = $this->find();
        if (!empty($datas)) {
            foreach ($datas as $data) {
                $used[] = $data['consumableitems_id'];
            }
        }

        return $used;
    }


    /**
     * Check mandatory fields
     *
     * @param $input
     *
     * @return array
     */
    public function checkMandatoryFields($input)
    {
        $msg     = [];
        $checkKo = false;

        $mandatory_fields = ['consumableitemtypes_id' => _n('Consumable type', 'Consumable types', 1),
            'consumableitems_id'     => _n('Consumable', 'Consumables', 1),
            'number'                 => __('Number', 'consumables')];

        foreach ($input as $key => $value) {
            if (isset($mandatory_fields[$key])) {
                if (empty($value) || $value == 'NULL') {
                    $msg[]   = $mandatory_fields[$key];
                    $checkKo = true;
                }
            }
        }

        if ($checkKo) {
            return [false, "<div class='alert alert-important alert-warning d-flex'>" . sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)) . "</div>"];
        }

        return [true, null];
    }
}
