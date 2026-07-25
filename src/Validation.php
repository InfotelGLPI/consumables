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
use CommonITILValidation;
use Consumable;
use ConsumableItem;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Html;
use MassiveAction;
use NotificationEvent;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Validation
 *
 */
class Validation extends CommonDBTM
{
    public static $rightname = "plugin_consumables_validation";

    public static function getTable($classname = null)
    {
        return Request::getTable();
    }

    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => self::getTypeName(2),
        ];

        $tab[] = [
            'id' => '1',
            'table' => 'glpi_users',
            'field' => 'name',
            'linkfield' => 'requesters_id',
            'name' => __('Requester'),
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id' => '2',
            'table' => 'glpi_consumableitemtypes',
            'field' => 'name',
            'linkfield' => 'consumableitemtypes_id',
            'name' => _n('Consumable type', 'Consumable types', 1),
            'datatype' => 'text',
        ];

        $tab[] = [
            'id' => '3',
            'table' => 'glpi_consumableitems',
            'field' => 'name',
            'linkfield' => 'consumableitems_id',
            'name' => _n('Consumable', 'Consumables', 1),
            'datatype' => 'text',
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'number',
            'name' => __('Number', 'consumables'),
            'datatype' => 'integer',
        ];

        $tab[] = [
            'id' => '5',
            'table' => $this->getTable(),
            'field' => 'give_items_id',
            'name' => __("Give to"),
            'datatype' => 'specific',
            'searchtype' => 'equals',
            'additionalfields' => ['give_itemtype'],
        ];

        $tab[] = [
            'id' => '6',
            'table' => $this->getTable(),
            'field' => 'status',
            'name' => __('Status'),
            'searchtype' => 'equals',
            'datatype' => 'specific',
        ];

        $tab[] = [
            'id' => '7',
            'table' => $this->getTable(),
            'field' => 'date_mod',
            'name' => __('Request date'),
            'datatype' => 'datetime',
        ];

        return $tab;
    }

    /**
     * @param int $nb
     *
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('Consumable validation', 'consumables');
    }

    /**
     * Have I the global right to "request group" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return bool|int
     **/
    public static function canValidate()
    {
        return Session::haveRight("plugin_consumables_validation", 1);
    }

    /**
     * Show consumable validation
     */
    public function showConsumableValidation()
    {
        if (!$this->canView()) {
            return false;
        }

        $rand         = mt_rand();
        $dbu          = new DbUtils();
        $can_validate = $this->canValidate();
        $container    = 'mass' . self::class . $rand;

        if ($can_validate) {
            $fields = $this->find(
                [
                    'NOT'
                        => ['status' => [CommonITILValidation::REFUSED, CommonITILValidation::ACCEPTED]],
                ],
                ["requesters_id", "consumableitemtypes_id"]
            );
        } else {
            $fields = $this->find(
                [
                    'requesters_id' => Session::getLoginUserID(),
                    'NOT' => ['status' => [CommonITILValidation::REFUSED, CommonITILValidation::ACCEPTED]],
                ],
                ["requesters_id", "consumableitemtypes_id"]
            );
        }

        $ma_open   = '';
        $ma_top    = '';
        $ma_bottom = '';
        $check_all = '';
        $close_form = '';
        if (!empty($fields) && $can_validate) {
            $ma_open             = Html::getOpenMassiveActionsForm($container);
            $massiveactionparams = ['item' => self::class, 'container' => $container, 'display' => false];
            $ma_top              = Html::showMassiveActions($massiveactionparams);
            $check_all           = Html::getCheckAllAsCheckbox($container);
            $massiveactionparams['ontop'] = false;
            $ma_bottom           = Html::showMassiveActions($massiveactionparams);
            $close_form          = Html::closeForm(false);
        }

        $rows = [];
        foreach ($fields as $field) {
            $give_link = '';
            if (!empty($field['give_itemtype'])) {
                $give_item = $dbu->getItemForItemtype($field['give_itemtype']);
                $give_item->getFromDB($field['give_items_id']);
                $give_link = $give_item->getLink();
            }
            $rows[] = [
                'checkbox'     => $can_validate ? Html::getMassiveActionCheckBox(self::class, $field['id']) : '',
                'requester'    => $dbu->getUserName($field['requesters_id']),
                'type'         => Dropdown::getDropdownName("glpi_consumableitemtypes", $field['consumableitemtypes_id']),
                'consumable'   => Dropdown::getDropdownName("glpi_consumableitems", $field['consumableitems_id']),
                'number'       => $field['number'],
                'give_link'    => $give_link,
                'status'       => CommonITILValidation::getStatus($field['status']),
                'status_color' => CommonITILValidation::getStatusColor($field['status']),
            ];
        }

        $footer = ['show' => false];
        if ($this->canCreate() && $can_validate) {
            Html::requireJs('glpi_dialog');
            $footer = [
                'show'          => true,
                'cancel_button' => Html::submit(_sx('button', 'Cancel'), [
                    'name'    => 'previous',
                    'class'   => 'consumable_previous_button btn btn-primary',
                    'onclick' => "consumables_cancel('" . PLUGIN_CONSUMABLES_WEBDIR . "/front/wizard.php')",
                ]),
                'hidden'        => Html::hidden('requesters_id', ['value' => Session::getLoginUserID()]),
            ];
        }

        TemplateRenderer::getInstance()->display('@consumables/validation_list.html.twig', [
            'webdir'     => PLUGIN_CONSUMABLES_WEBDIR,
            'title'      => self::getTypeName(),
            'rows'       => $rows,
            'ma_open'    => $ma_open,
            'ma_top'     => $ma_top,
            'ma_bottom'  => $ma_bottom,
            'check_all'  => $check_all,
            'close_form' => $close_form,
            'footer'     => $footer,
        ]);
    }


    /**
     * Validation consumable
     *
     * @param $params
     * @param int $state
     *
     * @return int
     */
    /**
     * Check that the current user may act on a request, based on the entity of the
     * linked consumable (the requests table itself is not entity-scoped).
     *
     * @param array $request_fields Fields of a loaded Request record
     *
     * @return bool
     */
    private static function requestHasEntityAccess(array $request_fields)
    {
        $consumable = new ConsumableItem();
        if (empty($request_fields['consumableitems_id'])
            || !$consumable->getFromDB($request_fields['consumableitems_id'])) {
            return false;
        }

        return Session::haveAccessToEntity(
            $consumable->fields['entities_id'],
            $consumable->fields['is_recursive']
        );
    }

    public function validationConsumable($params, $state = CommonITILValidation::WAITING)
    {
        if (!Session::haveRight('plugin_consumables_validation', 1)) {
            return ['error' => 'Access denied'];
        }
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0 || !$this->getFromDB($id)) {
            return ['error' => 'Record not found'];
        }
        // The requests table is not entity-scoped: enforce the scope through the
        // linked consumable so a validator cannot act outside its entities.
        if (!self::requestHasEntityAccess($this->fields)) {
            return ['error' => 'Access denied'];
        }
        $this->update([
            'id'            => $id,
            'status'        => $state,
            'validators_id' => Session::getLoginUserID(),
        ]);
        return $state;
    }


    /**
     * @return an|array
     */
    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();

        $forbidden[] = 'update';
        $forbidden[] = 'clone';
        $forbidden[] = 'purge';

        return $forbidden;
    }

    /**
     * Get the specific massive actions
     *
     * @param $checkitem link item to check right   (default NULL)
     *
     * @return array array of massive actions
     * *@since version 0.84
     *
     */
    public function getSpecificMassiveActions($checkitem = null)
    {
        $isadmin = static::canValidate();
        $actions = parent::getSpecificMassiveActions($checkitem);
        $prefix = $this->getType() . MassiveAction::CLASS_ACTION_SEPARATOR;

        if ($isadmin) {
            $actions[$prefix . 'validate'] = __('Validate');
            $actions[$prefix . 'refuse'] = __('Refuse', 'consumables');
        }

        return $actions;
    }


    /**
     * Massive actions display
     *
     * @param MassiveAction $ma
     *
     * @return bool of results (nbok, nbko, nbnoright counts)
     * @internal param array $input of input datas
     *
     */
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        $itemtype = $ma->getItemtype(false);

        switch ($itemtype) {
            case self::getType():
                switch ($ma->getAction()) {
                    case "validate":
                    case "refuse":
                        Html::textarea([
                            'name' => 'comment',
                            'cols' => 80,
                            'rows' => 7,
                            'enable_richtext' => false,
                        ]);
                        break;
                }
                return parent::showMassiveActionsSubForm($ma);
        }
    }

    /**
     * @param MassiveAction $ma
     * @param CommonDBTM $item
     * @param array $ids
     *
     * @since version 0.85
     *
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
     *
     */
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {
        $item = new Request();
        $validation = new self();
        $consumable = new Consumable();
        $input = $ma->getInput();

        if (count($ids)) {
            if (in_array($ma->getAction(), ['validate', 'refuse'], true)) {
                // After processing, return to the wizard menu instead of the search list
                // (whose "action" GET param is dropped by Html::getBackUrl()).
                $ma->setRedirect(PLUGIN_CONSUMABLES_WEBDIR . '/front/wizard.php');
            }
            switch ($ma->getAction()) {
                case "validate":
                    $added = [];
                    foreach ($ids as $key => $val) {
                        if (Session::haveRight("plugin_consumables_validation", 1)) {
                            $item->getFromDB($key);

                            // Enforce the entity scope of the linked consumable.
                            if (!self::requestHasEntityAccess($item->fields)) {
                                $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_NORIGHT);
                                continue;
                            }

                            // Get available consumables
                            $outConsumable = [];
                            $availables = $consumable->find([
                                'consumableitems_id' => $item->fields['consumableitems_id'],
                                'date_out' => null,
                            ]);
                            foreach ($availables as $available) {
                                $outConsumable[] = $available;
                            }

                            // Check if enough stock
                            if (!empty($outConsumable) && count($outConsumable) >= $item->fields['number']) {
                                // Give consumable
                                $state = CommonITILValidation::ACCEPTED;
                                $added['status'] = $state;
                                $added['validators_id'] = Session::getLoginUserID();
                                $added['id'] = $item->getID();
                                if ($item->update($added)) {
                                    $result = [1];
                                    for ($i = 0; $i < $item->fields['number']; $i++) {
                                        if (isset($outConsumable[$i]) && $consumable->out(
                                            $outConsumable[$i]['id'],
                                            $item->fields['give_itemtype'],
                                            $item->fields['give_items_id']
                                        )
                                        ) {
                                            $result[] = 1;
                                        } else {
                                            $result[] = 0;
                                        }
                                    }
                                    $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_OK);
                                } else {
                                    $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_KO);
                                }
                            } else {
                                $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_KO);
                                $ma->addMessage(
                                    sprintf(
                                        __('Not enough stock for consumable %s', 'consumables'),
                                        Dropdown::getDropdownName(
                                            "glpi_consumableitems",
                                            $item->fields['consumableitems_id']
                                        )
                                    )
                                );
                            }
                        } else {
                            $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_NORIGHT);
                            $ma->addMessage($validation->getErrorMessage(ERROR_RIGHT));
                        }
                    }

                    // Send notification
                    if (!empty($added)) {
                        foreach ($added as $add) {
                            $request = new Request();
                            $request->getFromDB($added['id']);
                            NotificationEvent::raiseEvent(
                                NotificationTargetRequest::CONSUMABLE_RESPONSE,
                                $request,
                                [
                                    'entities_id' => $_SESSION['glpiactive_entity'],
                                    'consumables' => $request,
                                    'comment' => $input['comment'],
                                ]
                            );
                        }
                    }
                    break;

                case "refuse":
                    $added = [];
                    foreach ($ids as $key => $val) {
                        if (Session::haveRight("plugin_consumables_validation", 1)) {
                            // Enforce the entity scope of the linked consumable.
                            if (!$item->getFromDB($key) || !self::requestHasEntityAccess($item->fields)) {
                                $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_NORIGHT);
                                continue;
                            }

                            // Validation status update
                            $state = CommonITILValidation::REFUSED;
                            if ($state == CommonITILValidation::REFUSED) {
                                $added['status'] = $state;
                                $added['validators_id'] = Session::getLoginUserID();
                                $added['id'] = $key;
                                if ($item->update($added)) {
                                    $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_OK);
                                } else {
                                    $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_KO);
                                }
                            } else {
                                $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_KO);
                            }
                        } else {
                            $ma->itemDone($validation->getType(), $key, MassiveAction::ACTION_NORIGHT);
                            $ma->addMessage($validation->getErrorMessage(ERROR_RIGHT));
                        }
                    }
                    // Send notification
                    if (!empty($added)) {
                        $request = new Request();
                        $request->getFromDB($added['id']);
                        NotificationEvent::raiseEvent(
                            NotificationTargetRequest::CONSUMABLE_RESPONSE,
                            $request,
                            [
                                'entities_id' => $_SESSION['glpiactive_entity'],
                                'consumables' => $request,
                                'comment' => $input['comment'],
                            ]
                        );
                    }
                    break;
            }
        }
    }
}
