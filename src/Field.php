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
use GlpiPlugin\Consumables\ConsumableItem;
use Html;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Field
 *
 * This class shows the plugin main page
 *
 * @package    Consumables
 * @author     Ludovic Dupont
 */
class Field extends CommonDBTM
{
    /**
     * Fields property for runtime compatibility
     * @var array
     */
    public $fields = [];

    /**
     * Find a record by criteria and load it into $this->fields
     * @param array $criteria
     * @return bool
     */
    public function getFromDBByCrit(array $criteria): bool
    {
        global $DB;
        $table = 'glpi_plugin_consumables_fields';
        $where = [];
        foreach ($criteria as $k => $v) {
            $where[] = "$k = '" . addslashes($v) . "'";
        }
        $sql = "SELECT * FROM $table WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $res = isset($DB) ? $DB->query($sql) : false;
        if ($res && $DB->numrows($res) > 0) {
            $this->fields = $DB->fetch_assoc($res);
            return true;
        }
        return false;
    }

    /**
     * Add a new record
     * @param array $input
     * @return bool
     */
    public function add(array $input, $history = null): bool
    {
        global $DB;
        $table = 'glpi_plugin_consumables_fields';
        $keys = array_keys($input);
        $values = array_map(function($v) { return "'" . addslashes($v) . "'"; }, array_values($input));
        $sql = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES (" . implode(',', $values) . ")";
        $res = isset($DB) ? $DB->query($sql) : false;
        if ($res) {
            $this->fields = $input;
            $this->fields['id'] = isset($DB) ? $DB->insert_id() : 0;
            return true;
        }
        return false;
    }

    /**
     * Update a record
     * @param array $input
     * @return bool
     */
    public function update(array $input, $history = null, $options = null): bool
    {
        global $DB;
        $table = 'glpi_plugin_consumables_fields';
        if (!isset($input['id'])) return false;
        $id = (int)$input['id'];
        $sets = [];
        foreach ($input as $k => $v) {
            if ($k === 'id') continue;
            $sets[] = "$k = '" . addslashes($v) . "'";
        }
        $sql = "UPDATE $table SET " . implode(',', $sets) . " WHERE id = $id";
        $res = isset($DB) ? $DB->query($sql) : false;
        if ($res) {
            foreach ($input as $k => $v) {
                $this->fields[$k] = $v;
            }
            return true;
        }
        return false;
    }
    public static array $types = ['ConsumableItem'];
    public static string $rightname = 'plugin_consumables';


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
    * Show order reference field
    *
    * @param $params
    */
    /**
     * Show order reference field
     * @param array $params
     * @return bool|null
     */
    public static function addFieldOrderReference(array $params): ?bool
    {
        $item = $params['item'];
        if (!in_array($item::getType(), self::$types, true)) {
            return false;
        }
        $consumableitems_id = $item->getID();
        $field = new self();
        if ($field->getFromDBByCrit(['consumableitems_id' => $consumableitems_id])) {
            echo "<div class='form-field row col-12 col-sm-6  mb-2'>";
            echo "<label class='col-form-label col-xxl-4 text-xxl-end'>";
            echo  __('Order reference', 'consumables');
            echo "</label>";
            echo "<div class='col-xxl-7  field-container'>";
            // Fallback: simple input for order_ref if Html::input is not available
            echo "<input type='text' name='order_ref' value='" . htmlspecialchars((($field->fields['order_ref'] ?? ''))) . "' size='40'>";
            echo "</div>";
            echo "</div>";
        }
        return null;
    }

   /**
    * Post add consumable
    *
    * @param ConsumableItem $consumableItem
    */
    /**
     * Post add consumable
     * @param ConsumableItem $consumableItem
     * @return void
     */
    public static function postAddConsumable(ConsumableItem $consumableItem): void
    {
        $field = new self();
        if (isset($consumableItem->input['order_ref'])) {
            $field->add([
                'consumableitems_id' => (($consumableItem->fields['id'] ?? '')),
                'order_ref' => $consumableItem->input['order_ref']
            ]);
        }
    }

   /**
    * Pre update consumable
    *
    * @param ConsumableItem $consumableItem
    */
    /**
     * Pre update consumable
     * @param ConsumableItem $consumableItem
     * @return void
     */
    public static function preUpdateConsumable(ConsumableItem $consumableItem): void
    {
        $field = new self();
        $field->getFromDBByCrit(['consumableitems_id' => $consumableItem->input['id']]);
        if (!empty($field->fields)) {
            $field->update([
                'id' => (($field->fields['id'] ?? '')),
                'order_ref' => $consumableItem->input['order_ref']
            ]);
        } else {
            self::postAddConsumable($consumableItem);
        }
    }
}
