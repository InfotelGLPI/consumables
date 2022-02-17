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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/**
 * Class PluginConsumablesServicecatalog
 */
class PluginConsumablesServicecatalog extends CommonGLPI
{

   static $rightname     = 'plugin_consumables_request';

   var     $dohistory = false;

   /**
    * @return bool
    */
   static function canUse() {
      return true;
   }

   /**
    * @return string
    */
   static function getMenuLink() {

      return PLUGIN_CONSUMABLES_WEBDIR . "/front/wizard.php";
   }

   /**
    * @return string
    */
   static function getNavBarLink() {
      global $CFG_GLPI;

      return PLUGIN_CONSUMABLES_DIR_NOFULL . "/front/wizard.php";
   }

   /**
    * @return string|\translated
    */
   static function getMenuTitle() {
      return _n('Consumable request', 'Consumable requests', 2, 'consumables');
   }

   /**
    * @return string
    */
   static function getMenuLogo() {

      return PluginConsumablesRequest::getIcon();
   }

   /**
    * @return string
    * @throws \GlpitestSQLError
    */
   static function getMenuLogoCss() {

      $addstyle = "font-size: 4.5em;";
      return $addstyle;

   }

   /**
    * @return string|\translated
    */
   static function getMenuComment() {

      return __('Make a consumable request', 'consumables');
   }

   /**
    * @return string
    */
   static function getLinkList() {
      return "";
   }

   /**
    * @return string
    */
   static function getList() {
      return "";
   }
}
