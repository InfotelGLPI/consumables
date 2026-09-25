<?php

/**
 * -------------------------------------------------------------------------
 * consumables plugin for GLPI
 * Copyright (C) 2015-2026 by the consumables Development Team.
 *
 * https://github.com/InfotelGLPI/consumables
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of consumables.
 *
 * consumables is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * consumables is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with consumables. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Consumables;

use CommonDBTM;
use ConsumableItem;
use Glpi\Application\View\TemplateRenderer;
use Html;

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
    public static $types     = ['ConsumableItem'];
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

    /*
     * The table has no entities_id, so checkEntity() is a no-op and can() would reduce to the
     * global plugin_consumables right: the generic front/field.form.php route of the core
     * would then read, rewrite or purge the order reference of any entity's consumables.
     * The field is only written by the ConsumableItem hooks below (after the core checked the
     * consumable itself) and purged with its consumable, none of which goes through can() on
     * this class, so no generic access is granted at all.
     */
    public static function canView(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canUpdate(): bool
    {
        return false;
    }

    public static function canDelete(): bool
    {
        return false;
    }

    public static function canPurge(): bool
    {
        return false;
    }


    /**
     * Show order reference field
     *
     * @param $params
     */
    public static function addFieldOrderReference($params)
    {

        $item = $params['item'];

        if (!in_array($item::getType(), self::$types)) {
            return false;
        }
        $order_ref = '';
        $field     = new self();
        if (!$item->isNewItem()
            && $field->getFromDBByCrit(["consumableitems_id" => $item->getID()])) {
            $order_ref = $field->fields['order_ref'] ?? '';
        }
        // Rendered inside the ConsumableItem form: the input must be named
        // "order_ref" (a "name" input would overwrite the item name on save), and
        // it is shown on creation too so that the first Field row can be created.
        TemplateRenderer::getInstance()->display('@consumables/field_order_reference.html.twig', [
            'order_ref_input' => Html::input('order_ref', ['value' => $order_ref, 'size' => 40]),
        ]);
    }

    /**
     * Post add consumable
     *
     * @param ConsumableItem $consumableItem
     */
    public static function postAddConsumable(ConsumableItem $consumableItem)
    {

        $field = new self();
        if (isset($consumableItem->input['order_ref'])) {
            $field->add(['consumableitems_id' => $consumableItem->fields['id'],
                'order_ref'      => $consumableItem->input['order_ref']]);
        }
    }

    /**
     * Pre update consumable
     *
     * @param ConsumableItem $consumableItem
     */
    public static function preUpdateConsumable(ConsumableItem $consumableItem)
    {

        // Only touch order_ref when the update actually carries it. A ConsumableItem
        // can be updated through paths that never submit this plugin field (REST API,
        // mass actions, other plugins); reading the absent key raised a PHP warning
        // and wrote an empty value, silently wiping the stored order reference.
        if (!array_key_exists('order_ref', $consumableItem->input)) {
            return;
        }

        $field = new self();
        $field->getFromDBByCrit(["consumableitems_id" => $consumableItem->input['id']]);

        if (!empty($field->fields)) {
            $field->update(['id'        => $field->fields['id'],
                'order_ref' => $consumableItem->input['order_ref']]);
        } else {
            self::postAddConsumable($consumableItem);
        }
    }
}
