<?php

/*
 -------------------------------------------------------------------------
 consumables plugin for GLPI
 Copyright (C) 2015-2026 by the consumables Development Team.

 https://github.com/InfotelGLPI/consumables
 -------------------------------------------------------------------------

 LICENSE

 This file is part of consumables.

 consumables is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
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

use Ajax;
use CommonDBTM;
use CommonGLPI;
use CommonITILValidation;
use ConsumableItem;
use ConsumableItemType;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
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
class Request extends CommonDBTM
{
    public static $rightname = "plugin_consumables";

    /**
     * @param int $nb
     *
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return _n('Consumable request', 'Consumable requests', 1, 'consumables');
    }

    public static function getIcon()
    {
        return "ti ti-shopping-cart";
    }

    /**
     * Have I the global right to "request" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return bool|int
     * */
    public static function canRequest()
    {
        return Session::haveRight("plugin_consumables_request", 1);
    }

    public static function canValidate()
    {
        return Session::haveRight("plugin_consumables_validation", 1);
    }

    /**
     * Have I the global right to "request user" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return bool|int
     * */
    public static function canRequestUser()
    {
        return Session::haveRight("plugin_consumables_user", 1);
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $dbu = new DbUtils();

        switch ($field) {
            case 'status':
                return CommonITILValidation::getStatus($values['status']);
                break;
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
    public static function canRequestGroup()
    {
        return Session::haveRight("plugin_consumables_group", 1);
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

        $data = $this->find(['consumableitems_id' => $item->fields['id']], ["date_mod DESC"]);

        $this->listItemsForConsumable($data);
    }

    /**
     * Show list of items
     *
     * @param $fields
     */
    public function listItemsForConsumable($fields)
    {
        $rows = [];
        foreach ($fields as $field) {
            $give_link = '';
            if (!empty($field['give_itemtype'])) {
                $give_item = getItemForItemtype($field['give_itemtype']);
                $give_item->getFromDB($field['give_items_id']);
                $give_link = $give_item->getLink();
            }
            $rows[] = [
                'requester'    => getUserName($field['requesters_id']),
                'approver'     => getUserName($field['validators_id']),
                'number'       => $field['number'],
                'date'         => Html::convDateTime($field['date_mod']),
                'give_link'    => $give_link,
                'status'       => CommonITILValidation::getStatus($field['status']),
                'status_color' => CommonITILValidation::getStatusColor($field['status']),
            ];
        }

        echo TemplateRenderer::getInstance()->render('@consumables/request_consumable_list.html.twig', [
            'rows' => $rows,
        ]);
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

        ob_start();
        Html::showDateTimeField("begin_date", ['value' => $begin_date]);
        $begin_date_field = ob_get_clean();

        ob_start();
        Html::showDateTimeField("end_date", ['value' => $end_date]);
        $end_date_field = ob_get_clean();

        $result = $this->listItemsForUserOrGroup($item->fields['id'], $type, ['begin_date' => $begin_date,
            'end_date'   => $end_date]);

        Html::requireJs('glpi_dialog');

        TemplateRenderer::getInstance()->display('@consumables/request_search.html.twig', [
            'begin_date_field' => $begin_date_field,
            'end_date_field'   => $end_date_field,
            'search_button'    => Html::submit(__('Search'), [
                'name'    => 'addToCart',
                'class'   => 'btn btn-primary',
                'onclick' => "consumables_searchConsumables('searchConsumables','consumables_formSearchConsumables', 'consumables_searchConsumables','$type')",
            ]),
            'hidden_requester' => Html::hidden('requesters_id', ['value' => $item->fields['id']]),
            'results'          => $result['message'],
            'webdir'           => PLUGIN_CONSUMABLES_WEBDIR,
        ]);
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
        // "give to" recipient is always a User or a Group.
        if (!in_array($type, ['User', 'Group'], true)) {
            return ['success' => false, 'message' => ''];
        }

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

        $rows       = [];
        $consumable = new ConsumableItem();
        foreach ($data as $field) {
            $consumable->getFromDB($field['consumableitems_id']);
            $rows[] = [
                'consumable_link' => $consumable->getLink(),
                'type'            => Dropdown::getDropdownName(ConsumableItemType::getTable(), $field['consumableitemtypes_id']),
                'requester'       => getUserName($field['requesters_id']),
                'approver'        => getUserName($field['validators_id']),
                'number'          => $field['number'],
                'date'            => Html::convDateTime($field['date_mod']),
                'status'          => CommonITILValidation::getStatus($field['status']),
                'status_color'    => CommonITILValidation::getStatusColor($field['status']),
            ];
        }

        $message = TemplateRenderer::getInstance()->render('@consumables/request_list.html.twig', [
            'rows' => $rows,
        ]);

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

        // Consumable pictures + comment cell
        ob_start();
        $this->seeConsumablesInfos();
        $see_infos = ob_get_clean();

        // Consumable type dropdown (fires loadAvailableConsumables on change)
        ob_start();
        Dropdown::show("ConsumableItemType", ['entity'    => $_SESSION['glpiactive_entity'],
            'on_change' => 'loadAvailableConsumables(this);']);
        $type_dropdown = ob_get_clean();

        // Number dropdown / "No consumable" placeholder
        ob_start();
        $this->loadAvailableConsumablesNumber();
        $number_cell = ob_get_clean();

        // Give to (User/Group) selector
        $give_to = '';
        if (self::canRequestGroup() || self::canRequestUser()) {
            $itemtypes = [];
            if (self::canRequestGroup()) {
                $itemtypes[] = "Group";
            }
            if (self::canRequestUser()) {
                $itemtypes[] = "User";
            }
            ob_start();
            self::showSelectItemFromItemtypes(['itemtype_name'   => 'give_itemtype',
                'items_id_name'   => 'give_items_id',
                'entity_restrict' => $_SESSION['glpiactive_entity'],
                'itemtypes'       => $itemtypes]);
            $give_to = ob_get_clean();
        }

        $can_add = $this->canCreate() || $this->canRequest();
        if ($can_add) {
            Html::requireJs('glpi_dialog');
        }

        TemplateRenderer::getInstance()->display('@consumables/request_form.html.twig', [
            'can_add'        => $can_add,
            'requester_name' => $dbu->getUserName(Session::getLoginUserID()),
            'see_infos'      => $see_infos,
            'type_dropdown'  => $type_dropdown,
            'number_cell'    => $number_cell,
            'give_to'        => $give_to,
            'webdir'         => PLUGIN_CONSUMABLES_WEBDIR,
        ]);
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

            echo TemplateRenderer::getInstance()->render('@consumables/select_item_span.html.twig', [
                'show_id' => $show_id,
            ]);

            // We check $options as the caller will set $options['default_itemtype'] only if it needs a
            // default itemtype and the default value can be '' thus empty won't be valid !
            if (array_key_exists('default_itemtype', $options)) {
                echo Html::scriptBlock(Html::jsSetDropdownValue($field_id, $params['default_itemtype']));

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
        if ($consumable->getFromDB($consumableitems_id)
            && Session::haveAccessToEntity($consumable->fields['entities_id'], $consumable->fields['is_recursive'])) {
            if (isset($consumable->fields['pictures'])) {
                $pictures = json_decode($consumable->fields['pictures'], true);
                if (isset($pictures) && is_array($pictures)) {
                    $picture_urls = [];
                    foreach ($pictures as $picture) {
                        $picture_urls[] = Toolbox::getPictureUrl($picture);
                    }
                    echo TemplateRenderer::getInstance()->render('@consumables/request_infos.html.twig', [
                        'pictures' => $picture_urls,
                        'comment'  => $consumable->fields['comment'],
                    ]);
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
        $consumableitems_id = (int) $consumableitems_id;

        // Do not disclose stock for a consumable outside the user's entities.
        if ($consumableitems_id > 0) {
            $consumable = new ConsumableItem();
            if (!$consumable->getFromDB($consumableitems_id)
                || !Session::haveAccessToEntity($consumable->fields['entities_id'], $consumable->fields['is_recursive'])) {
                echo TemplateRenderer::getInstance()->render('@consumables/request_number_empty.html.twig', [
                    'hidden' => Html::hidden('number', ['value' => 0]),
                ]);
                return;
            }
        }

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
            echo TemplateRenderer::getInstance()->render('@consumables/request_number_empty.html.twig', [
                'hidden' => Html::hidden('number', ['value' => 0]),
            ]);
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

        // Server-side mirror of the wizard UI gating: reject a forged line before
        // returning any consumable / recipient label.
        if ($success && isset($params['consumableitems_id'])) {
            if (!$this->isRequestLineAllowed(
                (int) $params['consumableitems_id'],
                $params['give_itemtype'] ?? 'User',
                $params['give_items_id'] ?? Session::getLoginUserID()
            )) {
                return ['success' => false,
                    'message' => "<div class='alert alert-important alert-warning d-flex'>" . __('You are not allowed to request this consumable', 'consumables') . "</div>",
                    'rowId'   => mt_rand(),
                    'fields'  => []];
            }
        }

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
                    $consumableitems_id = (int) $row['consumableitems_id'];

                    // Do not trust the client: re-check entity access, the group
                    // restriction and the "give to" target on the submit endpoint.
                    if (!$this->isRequestLineAllowed(
                        $consumableitems_id,
                        $row['give_itemtype'] ?? '',
                        $row['give_items_id'] ?? 0
                    )) {
                        $success = false;
                        $message = "<div class='alert alert-important alert-warning d-flex'>" . __('You are not allowed to request this consumable', 'consumables') . "</div>";
                        continue;
                    }

                    // Bound the requested quantity to the available stock and the
                    // per-request cap (max_cart), server-side.
                    $number = (int) $row['number'];
                    $max    = self::countForConsumableItem($consumableitems_id);
                    $option = new Option();
                    if ($option->getFromDBByCrit(['consumableitems_id' => $consumableitems_id])
                        && $option->getMaxCart() > 0) {
                        $max = min($max, (int) $option->getMaxCart());
                    }
                    if ($number < 1 || $number > $max) {
                        $success = false;
                        $message = "<div class='alert alert-important alert-warning d-flex'>" . __('Invalid requested quantity', 'consumables') . "</div>";
                        continue;
                    }

                    $input = ['consumableitemtypes_id' => (int) $row['consumableitemtypes_id'],
                        'consumableitems_id'     => $consumableitems_id,
                        'number'                 => $number,
                        'date_mod'               => date("Y-m-d H:i:s"),
                        'give_items_id'          => (int) $row['give_items_id'],
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

    /**
     * Server-side check of the per-consumable group restriction.
     *
     * Mirrors the filtering done in loadAvailableConsumables() so that the
     * "allowed groups" ACL cannot be bypassed with a forged submit.
     *
     * @param int $consumableitems_id
     *
     * @return bool true when the current user is allowed to request the consumable
     */
    private function isConsumableAllowedForUser($consumableitems_id)
    {
        $option = new Option();
        if (!$option->getFromDBByCrit(['consumableitems_id' => (int) $consumableitems_id])) {
            return true;
        }

        $groups = $option->getAllowedGroups();
        if (empty($groups)) {
            return true;
        }

        foreach (Group_User::getUserGroups(Session::getLoginUserID()) as $usergroup) {
            if (in_array($usergroup['id'], $groups)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Server-side check of the "give to" target.
     *
     * Replicates the UI gating (canRequestUser / canRequestGroup), restricts the
     * itemtype to User/Group, always allows self-assignment, and requires the
     * target to exist and be reachable within the current entities.
     *
     * @param string $give_itemtype
     * @param int    $give_items_id
     *
     * @return bool
     */
    private function isGiveTargetAllowed($give_itemtype, $give_items_id)
    {
        $give_items_id = (int) $give_items_id;

        if (!in_array($give_itemtype, ['User', 'Group'], true)) {
            return false;
        }

        // Self-assignment is always allowed.
        if ($give_itemtype === 'User' && $give_items_id === (int) Session::getLoginUserID()) {
            return true;
        }

        if ($give_itemtype === 'User' && !self::canRequestUser()) {
            return false;
        }
        if ($give_itemtype === 'Group' && !self::canRequestGroup()) {
            return false;
        }

        $dbu    = new DbUtils();
        $target = $dbu->getItemForItemtype($give_itemtype);
        if (!$target || !$target->getFromDB($give_items_id)) {
            return false;
        }

        if ($target->isEntityAssign()
            && !Session::haveAccessToEntity($target->fields['entities_id'], $target->fields['is_recursive'] ?? false)) {
            return false;
        }

        return true;
    }

    /**
     * Server-side authorization for a single request line.
     *
     * Re-checks entity access to the consumable, the per-consumable group
     * restriction and the "give to" target. The wizard UI already enforces
     * these, but the submit endpoint must not trust the client.
     *
     * @param int    $consumableitems_id
     * @param string $give_itemtype
     * @param int    $give_items_id
     *
     * @return bool
     */
    private function isRequestLineAllowed($consumableitems_id, $give_itemtype, $give_items_id)
    {
        $consumable = new ConsumableItem();
        if (!$consumable->getFromDB((int) $consumableitems_id)) {
            return false;
        }

        if (!Session::haveAccessToEntity($consumable->fields['entities_id'], $consumable->fields['is_recursive'])) {
            return false;
        }

        if (!$this->isConsumableAllowedForUser($consumableitems_id)) {
            return false;
        }

        return $this->isGiveTargetAllowed($give_itemtype, $give_items_id);
    }
}
