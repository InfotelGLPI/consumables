<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 consumables plugin for GLPI
 Copyright (C) 2009-2016 by the consumables Development Team.

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
 * Class PluginPurchaserequestServicecatalog
 */
class PluginConsumablesServicecatalog extends CommonGLPI
{

   static $rightname     = 'plugin_consumables';

   var     $dohistory = false;

   /**
    * @return bool
    */
   static function canUse() {
      return Session::haveRight(self::$rightname, UPDATE);
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
      global $CFG_GLPI;

      $btclass = "bt-interface bt-purchaserequest";
      return "<a class='".$btclass."' href='".$CFG_GLPI['root_doc'] . "/plugins/consumables/front/wizard.php'></a>";

   }

   /**
    * @return string|\translated
    */
   static function getMenuComment() {

      return __('See your consumables requests', 'consumables');
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