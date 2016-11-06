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

// Init the hooks of the plugins -Needed
function plugin_init_consumables()
{
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['consumables'] = true;
   $PLUGIN_HOOKS['change_profile']['consumables'] = array('PluginConsumablesProfile', 'initProfile');
   $PLUGIN_HOOKS['add_css']['consumables'] = array('consumables.css');
   $PLUGIN_HOOKS['add_javascript']['consumables'][] = 'consumables.js';

   if (Session::getLoginUserID()) {
      if (class_exists('PluginConsumablesField')) {
         foreach (PluginConsumablesField::$types as $item) {
            if (isset($_SERVER['REQUEST_URI']) && strpos(strtolower($_SERVER['REQUEST_URI']), strtolower($item)) !== false) {
               $PLUGIN_HOOKS['add_javascript']['consumables'][] = 'consumables.js.php';
            }
         }
      }

      Plugin::registerClass('PluginConsumablesProfile', array('addtabon' => 'Profile'));
      Plugin::registerClass('PluginConsumablesRequest', array('addtabon' => 'User',
         'notificationtemplates_types' => true));
      Plugin::registerClass('PluginConsumablesRequest', array('addtabon' => 'ConsumableItem'));

      $PLUGIN_HOOKS['item_add']['consumables'] = array('ConsumableItem' => array('PluginConsumablesField', 'postAddConsumable'));
      $PLUGIN_HOOKS['pre_item_update']['consumables'] = array('ConsumableItem' => array('PluginConsumablesField', 'preUpdateConsumable'));

      if (Session::haveRight("plugin_consumables", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['consumables'] = 1;
      }

      if (Session::haveRight("plugin_consumables", READ)) {
         $PLUGIN_HOOKS['menu_toadd']['consumables'] = array('plugins' => 'PluginConsumablesMenu');
         $PLUGIN_HOOKS['helpdesk_menu_entry']['consumables'] = '/front/wizard.php';
      }

      // Post item purge
      $PLUGIN_HOOKS['item_purge']['consumables'] = array('ConsumableItem' => 'plugin_item_purge_consumables');
   }
}

// Get the name and the version of the plugin - Needed

/**
 * @return array
 */
function plugin_version_consumables()
{

   return array(
      'name' => _n('Consumable request', 'Consumable requests', 1, 'consumables'),
      'version' => '1.2.0',
      'author' => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'license' => 'GPLv2+',
      'homepage' => 'https://github.com/InfotelGLPI/consumables',
      'minGlpiVersion' => '0.90',
   );
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
/**
 * @return bool
 */
function plugin_consumables_check_prerequisites()
{
   if (version_compare(GLPI_VERSION, '0.85', 'lt') || version_compare(GLPI_VERSION, '9.2', 'ge')) {
      _e('This plugin requires GLPI >= 0.85', 'consumables');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded
//may display messages or add to message after redirect
/**
 * @return bool
 */
function plugin_consumables_check_config()
{
   return true;
}