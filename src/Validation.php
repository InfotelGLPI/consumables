<?php
declare(strict_types=1);
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

use CommonDBTM;
use CommonITILValidation;
use Consumable;
use DbUtils;
use Dropdown;
use Html;
use MassiveAction;
use NotificationEvent;
use Session;

// Ensure Request class is available for static analysis and runtime when this file is included
if (!class_exists('GlpiPlugin\\Consumables\\Request')) {
    $req = __DIR__ . '/Request.php';
    if (is_readable($req)) {
        require_once $req;
    }
}

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Validation
 *
 */

/**
 * Class Validation
 */
class Validation extends CommonDBTM
{
    public static string $rightname = 'plugin_consumables';

    /**
     * @param string|null $classname
     * @return string
     */
    public static function getTable(?string $classname = null): string
    {
        return Request::getTable();
    }

    /**
     * @return array
     */
    public function rawSearchOptions(): array
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
    /**
     * @param int $nb
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('Consumable validation', 'consumables');
    }

    /**
     * Return a human readable error message for the given error code.
     * Used by massive actions handling.
     */
    public function getErrorMessage($code): string
    {
        return '';
    }

    /**
     * Have I the global right to "request group" the Object
     * May be overloaded if needed (ex KnowbaseItem)
     *
     * @return bool|int
     **/
    /**
     * @return bool|int
     */
    public static function canValidate()
    {
        return Session::haveRight('plugin_consumables_validation', 1);
    }

    /**
     * Show consumable validation
     */
    /**
     * Show consumable validation
     * @return bool|null
     */
    public function showConsumableValidation(): ?bool
    {
        if (!$this->canView()) {
            return false;
        }

        // Wizard title
        echo "<div class='alert alert-secondary'>";
        echo "<i class='thumbnail ti ti-shopping-cart-plus fa-2x'></i>";
        echo "&nbsp;";
        echo __("Consumable validation", "consumables");
        echo "</div>";

        $rand = mt_rand();
        $dbu = new DbUtils();

        if ($this->canValidate()) {
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
        echo "<div class='center'>";

        if (!empty($fields)) {
            if ($this->canValidate()) {
                Html::openMassiveActionsForm('mass' . self::class . $rand);
                $massiveactionparams = ['item' => self::class, 'container' => 'mass' . self::class . $rand];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<div style='overflow-x:auto;'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr>";
            echo "<th colspan='7'>" . self::getTypeName() . "</th>";
            echo "</tr>";
            echo "<tr>";
            echo "<th width='10'>";
            if ($this->canValidate()) {
                echo Html::getCheckAllAsCheckbox('mass' . self::class . $rand);
            }
            echo "</th>";
            echo "<th>" . __('Requester') . "</th>";
            echo "<th>" . _n('Consumable type', 'Consumable types', 1) . "</th>";
            echo "<th>" . _n('Consumable', 'Consumables', 1) . "</th>";
            echo "<th>" . __('Number', 'consumables') . "</th>";
            echo "<th>" . __("Give to") . "</th>";
            echo "<th>" . _sx("item", "State") . "</th>";
            echo "</tr>";

            foreach ($fields as $field) {
                echo "<tr class='tab_bg_1'>";
                echo "<td width='10'>";
                if ($this->canValidate()) {
                    Html::showMassiveActionCheckBox(self::class, $field['id']);
                }
                echo "</td>";

                echo "<td>";
                echo $dbu->getUserName($field['requesters_id']);
                echo "</td>";

                echo "<td>";
                echo Dropdown::getDropdownName("glpi_consumableitemtypes", $field['consumableitemtypes_id']);
                echo "</td>";

                echo "<td>";
                echo Dropdown::getDropdownName("glpi_consumableitems", $field['consumableitems_id']);
                echo "</td>";

                echo "<td>";
                echo $field['number'];
                echo "</td>";

                echo "<td>";
                if (!empty($field['give_itemtype'])) {
                    $give_item = $dbu->getItemForItemtype($field['give_itemtype']);
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
            if ($this->canValidate()) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
            }

            echo "</table>";
        } else {
            echo __("No item to display");
        }

        echo "</div>";
        echo "</div>";

        // Footer
        if ($this->canCreate() && $this->canValidate()) {
            echo "<br/><table width='100%'>";
            echo "<tr>";
            echo "<td>";
            Html::requireJs('glpi_dialog');
            echo "<div id='dialog-confirm'></div>";
            echo Html::submit(_sx('button', 'Cancel'), [
                'name' => 'previous',
                'class' => 'consumable_previous_button btn btn-primary',
                'onclick' => "consumables_cancel('" . PLUGIN_CONSUMABLES_WEBDIR . "/front/wizard.php')",
            ]);
            echo Html::hidden('requesters_id', ['value' => Session::getLoginUserID()]);
            echo "</td>";
            echo "</tr>";
            echo "</table>";
        }

        // Init consumable cart javascript
        echo Html::scriptBlock(
            '$(document).ready(function() {consumables_initJs("' . PLUGIN_CONSUMABLES_WEBDIR . '");});'
        );
        return null;
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
     * Validation consumable
     * @param array $params
     * @param int $state
     * @return int
     */
    public function validationConsumable(array $params, int $state = CommonITILValidation::WAITING): int
    {
        //        $this->update([
        //            'id' => $params['id'],
        //            'status' => $state,
        //            'validators_id' => Session::getLoginUserID()
        //        ]);
        //
        //        return $state;
        return CommonITILValidation::ACCEPTED;
    }


    /**
     * @return an|array
     */
    /**
     * @return array
     */
    public function getForbiddenStandardMassiveAction(): array
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
    /**
     * @param mixed $checkitem
     * @return array
     */
    public function getSpecificMassiveActions($checkitem = null): array
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
    /**
     * @param MassiveAction $ma
     * @return bool|null
     */
    public function showMassiveActionsSubForm($ma = null): ?bool
    {
        $itemtype = $ma->getItemtype(false);
        switch ($itemtype) {
            case self::getType():
                switch ($ma->getAction()) {
                    case 'validate':
                    case 'refuse':
                        Html::textarea([
                            'name' => 'comment',
                            'cols' => 80,
                            'rows' => 7,
                            'enable_richtext' => false,
                        ]);
                        break;
                }
                return parent::showMassiveActionsSubFormStatic($ma);
        }
        return null;
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
    /**
     * @param MassiveAction $ma
     * @param CommonDBTM $item
     * @param array $ids
     * @return void
     */
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ): void {
        $item = new Request();
        $validation = new self();
        $consumable = new Consumable();
        $input = $ma->getInput();

        if (count($ids)) {
            switch ($ma->getAction()) {
                case "validate":
                    $added = [];
                    foreach ($ids as $key => $val) {
                        if (Session::haveRight("plugin_consumables_validation", 1)) {
                            $item->getFromDB($key);

                            // Get available consumables
                            $outConsumable = [];
                            $availables = $consumable->find([
                                'consumableitems_id' => (($item->fields['consumableitems_id'] ?? '')),
                                'date_out' => null,
                            ]);
                            foreach ($availables as $available) {
                                $outConsumable[] = $available;
                            }

                            // Check if enough stock
                            if (!empty($outConsumable) && count($outConsumable) >= (($item->fields['number'] ?? ''))) {
                                // Give consumable
                                $state = $validation->validationConsumable(
                                    $item->fields,
                                    CommonITILValidation::ACCEPTED
                                );
                                $added['status'] = $state;
                                $added['validators_id'] = Session::getLoginUserID();
                                $added['id'] = $item->getID();
                                if ($item->update($added)) {
                                    $result = [1];
                                    for ($i = 0; $i < (($item->fields['number'] ?? '')); $i++) {
                                        if (isset($outConsumable[$i]) && $consumable->out(
                                            $outConsumable[$i]['id'],
                                            (($item->fields['give_itemtype'] ?? '')),
                                            (($item->fields['give_items_id'] ?? ''))
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
                                            (($item->fields['consumableitems_id'] ?? ''))
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
                            // Validation status update
                            $state = $validation->validationConsumable($item->fields, CommonITILValidation::REFUSED);
                            if ($state == CommonITILValidation::REFUSED) {
                                $added['status'] = $state;
                                $added['validators_id'] = Session::getLoginUserID();
                                $added['id'] = $item->getID();
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
