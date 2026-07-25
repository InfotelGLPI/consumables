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

use CommonDBTM;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Group;
use Html;
use MassiveAction;
use Toolbox;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Option
 */
class Option extends CommonDBTM
{

    public static $rightname = "plugin_consumables";

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
    public static function getTypeName($nb = 0)
    {

        return __('Consumable request options', 'consumables');
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
        $data = [];
        if ($this->getFromDBByCrit(["consumableitems_id" => $item->fields['id']])) {
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
    public function initConfig($ID)
    {
        $input['consumableitems_id'] = $ID;
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
    public function listOptionsForConsumable($data, $item)
    {
        global $CFG_GLPI;

        $ID       = $data['id'];
        $form_url = Toolbox::getItemTypeFormURL(self::class);

        $groups_rows = [];
        $groups      = json_decode($data['groups'], true);
        if (!empty($groups)) {
            foreach ($groups as $val) {
                $groups_rows[] = [
                    'name'        => Dropdown::getDropdownName("glpi_groups", $val),
                    'delete_form' => Html::getSimpleForm(
                        $form_url,
                        'delete_groups',
                        _x('button', 'Delete permanently'),
                        ['delete_groups' => 'delete_groups',
                            'id'         => $ID,
                            '_groups_id' => $val],
                        'fa-times-circle'
                    ),
                ];
            }
        }

        TemplateRenderer::getInstance()->display('@consumables/option_form.html.twig', [
            'form_url'           => $form_url,
            'title'              => self::getTypeName(1),
            'max_cart_dropdown'  => Dropdown::showNumber('max_cart', ['value'   => $data['max_cart'],
                'max'     => 100,
                'display' => false]),
            'can_create'         => $this->canCreate(),
            'define_button'      => Html::submit(_sx('button', 'Define', 'consumables'), ['name' => 'update', 'class' => 'btn btn-primary']),
            'consumableitems_id' => $data['consumableitems_id'],
            'id'                 => $ID,
            'groups_rows'        => $groups_rows,
        ]);

        self::showAddGroup($item, $data);
    }


   /**
    * @param $item
    * @param $data
    */
    public static function showAddGroup($item, $data)
    {
        $used = ($data["groups"] == '' ? [] : json_decode($data["groups"], true));

        TemplateRenderer::getInstance()->display('@consumables/option_add_group.html.twig', [
            'form_url'           => Toolbox::getItemTypeFormURL(self::class),
            'group_dropdown'     => Group::dropdown(['name'        => '_groups_id',
                'used'        => $used,
                'entity'      => $item->fields['entities_id'],
                'entity_sons' => $item->fields["is_recursive"],
                'display'     => false]),
            'consumableitems_id' => $item->getID(),
            'id'                 => $data['id'],
            'add_button'         => Html::submit(_sx('button', 'Add'), ['name' => 'add_groups', 'class' => 'btn btn-primary']),
        ]);
    }

   /**
    * @param array $params
    *
    * @return array
    */
    public function prepareInputForUpdate($params)
    {
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
        } elseif (isset($params["delete_groups"])) {
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
    public function getMaxCart()
    {
        return $this->fields['max_cart'];
    }

   /**
    * @return mixed
    */
    public function getAllowedGroups()
    {
        if (!empty($this->fields['groups'])) {
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
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case "add_number":
                TemplateRenderer::getInstance()->display('@consumables/option_massiveaction.html.twig', [
                    'label'  => __('Maximum number allowed for request', 'consumables'),
                    'field'  => Dropdown::showNumber('max_cart', ['value'   => 0,
                        'min'     => 0,
                        'max'     => 100,
                        'display' => false]),
                    'submit' => Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']),
                ]);
                return true;

            case "add_groups":
                TemplateRenderer::getInstance()->display('@consumables/option_massiveaction.html.twig', [
                    'label'  => __('Add a group for request', 'consumables'),
                    'field'  => Group::dropdown(['name' => '_groups_id', 'display' => false]),
                    'submit' => Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']),
                ]);
                return true;
        }
    }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        $option = new self();

        switch ($ma->getAction()) {
            case "add_number":
                $input = $ma->getInput();
                foreach ($ids as $id) {
                    $input = ['max_cart'       => $input['max_cart'],
                         'consumableitems_id' => $id];

                    if ($item->getFromDB($id)) {
                        if ($option->getFromDBByCrit(["consumableitems_id" => $id])) {
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
                        if ($option->getFromDBByCrit(["consumableitems_id" => $id])) {
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
                            $params = ['consumableitems_id' => $id,
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
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'groups':
                $list_groups = '';
                $groups      = json_decode($values['groups'], true);
                if (!empty($groups)) {
                    foreach ($groups as $key => $val) {
                        // 'specific' search columns are rendered as HTML: escape the
                        // group label (a group name may contain HTML/JS).
                        $list_groups .= htmlescape(Dropdown::getDropdownName("glpi_groups", $val)) . "<br>";
                    }
                }
                return $list_groups;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }
}
