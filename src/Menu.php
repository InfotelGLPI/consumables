<?php

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
use \CommonGLPI;
use GlpiPlugin\Consumables\Wizard;
use GlpiPlugin\Consumables\Request;

/**
 * Class Menu
 */
class Menu extends CommonGLPI
{
    public static $rightname = 'plugin_consumables';

// declare(strict_types=1); must be at the top of the file, not inside a class


        /**
         * @return string
         */
        public static function getMenuName(): string
        {
            return _n('Consumable request', 'Consumable requests', 1, 'consumables');
        }


    /**
     * @return array
     */
    public static function getMenuContent(): array
    {
        $menu = [];
        $menu['title'] = self::getMenuName();
        // Fallback: use wizard.php as menu page
        $menu['page'] = PLUGIN_CONSUMABLES_WEBDIR . '/front/wizard.php';
        // Permissions: always show for now (fix as needed)
        $menu['links']['search'] = $menu['page'];
        $menu['links']['add'] = $menu['page'];
        $menu['icon'] = 'ti ti-shopping-cart';
        return $menu;
    }

    /**
    /**
     * Remove rights from session for this menu
     * @return void
     */
    public static function removeRightsFromSession(): void
    {
        if (isset($_SESSION['glpimenu']['plugins']['types'][Menu::class])) {
            unset($_SESSION['glpimenu']['plugins']['types'][Menu::class]);
        }
        if (isset($_SESSION['glpimenu']['plugins']['content'][Menu::class])) {
            unset($_SESSION['glpimenu']['plugins']['content'][Menu::class]);
        }
    }
}
