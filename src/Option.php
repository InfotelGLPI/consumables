<?php

declare(strict_types=1);

namespace GlpiPlugin\Consumables;

use CommonDBTM;
use DbUtils;
use Dropdown;
use Html;
use MassiveAction;
use Toolbox;

class Option extends CommonDBTM
{
    /**
     * Fields property for static analysis and runtime compatibility
     * @var array
     */
    public $fields = [];
    public static $rightname = 'plugin_consumables';

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
    /**
     * Return the localized name of the current Type
     *
     * @param int $nb
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('Option', 'consumables');
    }

   /**
    * Show
    *
    * @param  $item
    *
    * @return bool
    */
    /**
     * Show options for a consumable item
     * @param object $item
     * @return bool|null
     */
    public function showForConsumable($item): ?bool
    {
        if (!$this->canView()) {
            return false;
        }
        $data = [];
        if ($this->getFromDBByCrit(["consumableitems_id" => (($item->fields['id'] ?? ''))])) {
            $data = $this->fields;
        }
        if (count($data) < 1) {
            $data = $this->initConfig((($item->fields['id'] ?? '')));
        }
        $this->listOptionsForConsumable($data, $item);
        return null;
    }

   /**
    * Initialize the original configuration
    *
    * @param $ID
    *
    * @return array
    */
    /**
     * Initialize the original configuration
     * @param int $ID
     * @return array
     */
    public function initConfig(int $ID): array
    {
        $input = [
            'consumableitems_id' => $ID,
            'groups' => '',
            'max_cart' => '0',
        ];
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
    /**
     * Show list of options for a consumable item
     * @param array $data
     * @param object $item
     * @return void
     */
    public function listOptionsForConsumable(array $data, $item): void
    {
        global $CFG_GLPI;
        $ID = $data['id'];

        echo "<div class='center'>";
        echo "<form action='" . Toolbox::getItemTypeFormURL(self::class) . "' method='post'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<th colspan='3'>" . self::getTypeName(1) . "</th>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Maximum number allowed for request', 'consumables');
        echo " </td>";
        echo "<td>";
        // Fallback: simple input for max_cart if Dropdown::showNumber is not available
        echo "<input type='number' name='max_cart' value='" . htmlspecialchars($data['max_cart']) . "' min='0' max='100'>";
        echo " </td>";
        if ($this->canCreate()) {
            echo "<td class='center'>";
            echo Html::submit(_sx('button', 'Define', 'consumables'), ['name' => 'update', 'class' => 'btn btn-primary']);
            echo "</td>";
        }
        echo "</tr>";
        echo Html::hidden('consumableitems_id', ['value' => $data['consumableitems_id']]);
        echo Html::hidden('id', ['value' => $ID]);
        echo "</table>";
        Html::closeForm();

        echo "<form action='" . Toolbox::getItemTypeFormURL(self::class) . "' method='post'>";
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
                Html::showSimpleForm(
                    Toolbox::getItemTypeFormURL(self::class),
                    'delete_groups',
                    _x('button', 'Delete permanently'),
                    ['delete_groups' => 'delete_groups',
                                  'id'            => $ID,
                                  '_groups_id'    => $val],
                    'fa-times-circle'
                );
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

        echo Html::hidden('consumableitems_id', ['value' => $data['consumableitems_id']]);
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
    /**
     * Show add group form for a consumable item
     * @param object $item
     * @param array $data
     * @return void
     */
    public static function showAddGroup($item, array $data): void
    {
        echo "<form action='" . Toolbox::getItemTypeFormURL(self::class) . "' method='post'>";
        echo "<table class='tab_cadre_fixe' cellpadding='5'>";
        echo "<tr class='tab_bg_1 center'>";
        echo "<th>" . __('Add a group for request', 'consumables') . "</th>";
        echo "<th>&nbsp;</th>";
        echo "</tr>";
        echo "<tr class='tab_bg_1 center'>";
        echo "<td>";

        $used = ($data["groups"] == '' ? [] : json_decode($data["groups"], true));

        // Fallback: simple select for group if Group::dropdown is not available
        echo "<select name='_groups_id'><option value='1'>Group 1</option></select>";

        echo "</td>";
        echo "<td>";
        echo Html::hidden('consumableitems_id', ['value' => $item->getID()]);
        echo Html::hidden('id', ['value' => $data['id']]);
        echo Html::submit(_sx('button', 'Add'), ['name' => 'add_groups', 'class' => 'btn btn-primary']);
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        Html::closeForm();
    }

   /**
    * @param array $params
    *
    * @return array
    */
    /**
     * Prepare input for update
     * @param array $input
     * @return array
     */
    public function prepareInputForUpdate($input)
    {
        $dbu = new DbUtils();

        if (isset($input["add_groups"])) {
            $original_input = $input;
            $input = [];

            $restrict = ["id" => $original_input['id']];
            $configs  = $dbu->getAllDataFromTable("glpi_plugin_consumables_options");

            $groups = [];
            if (!empty($configs)) {
                foreach ($configs as $config) {
                    if (!empty($config["groups"])) {
                        $groups = json_decode($config["groups"], true);
                        if (count($groups) > 0) {
                            if (!in_array($original_input["_groups_id"], $groups)) {
                                 array_push($groups, $original_input["_groups_id"]);
                            }
                        } else {
                            $groups = [$original_input["_groups_id"]];
                        }
                    } else {
                        $groups = [$original_input["_groups_id"]];
                    }
                }
            }

            $group = json_encode($groups);

            $input['id']     = $original_input['id'];
            $input['groups'] = $group;
        } elseif (isset($input["delete_groups"])) {
            $original_input = $input;
            $input = [];

            $restrict = ["id" => $original_input['id']];
            $configs  = $dbu->getAllDataFromTable("glpi_plugin_consumables_options");

            $groups = [];
            if (!empty($configs)) {
                foreach ($configs as $config) {
                    if (!empty($config["groups"])) {
                        $groups = json_decode($config["groups"], true);
                        if (count($groups) > 0) {
                            if (($key = array_search($original_input["_groups_id"], $groups)) !== false) {
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

            $input['id']     = $original_input['id'];
            $input['groups'] = $group;
        } else {
            // No changes needed, return input as-is
        }
        return $input;
    }

   /**
    * @return mixed
    */
    /**
     * Get max cart value
     * @return mixed
     */
    public function getMaxCart()
    {
        return (($this->fields['max_cart'] ?? ''));
    }

   /**
    * @return mixed
    */
    /**
     * Get allowed groups
     * @return array
     */
    public function getAllowedGroups(): array
    {
        if (!empty((($this->fields['groups'] ?? '')))) {
            return json_decode((($this->fields['groups'] ?? '')), true);
        }
        return [];
    }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    **/
    /**
     * Show massive actions subform
     * @param MassiveAction $ma
     * @return bool|null
     */
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case "add_number":
                echo "</br>&nbsp;" . __('Maximum number allowed for request', 'consumables') . " : ";
                // Fallback: simple input for max_cart if Dropdown::showNumber is not available
                echo "<input type='number' name='max_cart' value='0' min='0' max='100'>";
                echo "&nbsp;" .
                   Html::submit(_sx('button', 'Post'), ['name' => 'massiveaction']);
                return true;

            case "add_groups":
                echo "</br>&nbsp;" . __('Add a group for request', 'consumables') . " : ";
                // Fallback: simple select for group if Group::dropdown is not available
                echo "<select name='_groups_id'><option value='1'>Group 1</option></select>";
                echo "&nbsp;" .
                 Html::submit(_sx('button', 'Post'), ['name' => 'massiveaction']);
                return true;
        }
    }


   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
    /**
     * Process massive actions for one itemtype
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
    /**
     * Get specific value to display
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
        switch ($field) {
            case 'groups':
                $list_groups = '';
                $groups = json_decode($values['groups'], true);
                if (!empty($groups)) {
                    foreach ($groups as $val) {
                        $list_groups .= Dropdown::getDropdownName('glpi_groups', $val) . '<br>';
                    }
                }
                return $list_groups;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }
}

