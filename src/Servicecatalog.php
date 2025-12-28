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
use CommonGLPI;
use GlpiPlugin\Consumables\Request;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}


/**
 * Class Servicecatalog
 */
class Servicecatalog extends CommonGLPI
{
    public static string $rightname = 'plugin_consumables_request';
    public bool $dohistory = false;

   /**
    * @return bool
    */
    /**
     * @return bool|int
     */
    public static function canUse()
    {
        return Session::haveRight('plugin_consumables_request', 1);
    }

   /**
    * @return string
    */
    /**
     * @return string
     */
    public static function getMenuLink(): string
    {
        return PLUGIN_CONSUMABLES_WEBDIR . '/front/wizard.php';
    }

   /**
    * @return string
    */
    /**
     * @return string
     */
    public static function getNavBarLink(): string
    {
        global $CFG_GLPI;
        return PLUGIN_CONSUMABLES_WEBDIR . '/front/wizard.php';
    }

   /**
    * @return string
    */
    /**
     * @return string
     */
    public static function getMenuTitle(): string
    {
        return _n('Consumable request', 'Consumable requests', 2, 'consumables');
    }

   /**
    * @return string
    */
    /**
     * @return string
     */
    public static function getMenuLogo(): string
    {
        return Request::getIcon();
    }

   /**
    * @return string
    * @throws \GlpitestSQLError
    */
    /**
     * @return string
     */
    public static function getMenuLogoCss(): string
    {
        $addstyle = 'font-size: 4.5em;';
        return $addstyle;
    }

   /**
    * @return string
    */
    /**
     * @return string
     */
    public static function getMenuComment(): string
    {
        return __('Make a consumable request', 'consumables');
    }

   /**
    * @return string
    */
    /**
     * @return string
     */
    public static function getLinkList(): string
    {
        return '';
    }

   /**
    * @return string
    */
    /**
     * @return string
     */
    public static function getList(): string
    {
        return '';
    }
}
