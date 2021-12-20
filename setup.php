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

define('PLUGIN_CONSUMABLES_VERSION', '2.0.0-rc1');

if (!defined("PLUGIN_CONSUMABLES_DIR")) {
   define("PLUGIN_CONSUMABLES_DIR", Plugin::getPhpDir("consumables"));
   define("PLUGIN_CONSUMABLES_DIR_NOFULL", Plugin::getPhpDir("consumables",false));
}
if (!defined("PLUGIN_CONSUMABLES_WEBDIR")) {
   define("PLUGIN_CONSUMABLES_WEBDIR", Plugin::getWebDir("consumables"));
   define("PLUGIN_CONSUMABLES_NOTFULL_WEBDIR", Plugin::getPhpDir("consumables",false));
}

// Init the hooks of the plugins -Needed
function plugin_init_consumables() {
   global $PLUGIN_HOOKS,$CFG_GLPI;

   $CFG_GLPI['glpitablesitemtype']['PluginConsumablesValidation'] = 'glpi_plugin_consumables_requests';
   $PLUGIN_HOOKS['csrf_compliant']['consumables'] = true;
   $PLUGIN_HOOKS['change_profile']['consumables'] = ['PluginConsumablesProfile', 'initProfile'];
   $PLUGIN_HOOKS['add_css']['consumables']        = ['consumables.css'];
   $PLUGIN_HOOKS['javascript']['consumables'][]   = PLUGIN_CONSUMABLES_NOTFULL_WEBDIR.'/consumables.js';

   if (Session::getLoginUserID()) {
      $PLUGIN_HOOKS['post_item_form']['consumables'] = ['PluginConsumablesField', 'addFieldOrderReference'];

      Plugin::registerClass('PluginConsumablesProfile', ['addtabon' => 'Profile']);
      Plugin::registerClass('PluginConsumablesRequest', ['addtabon'                    => 'User',
                                                         'notificationtemplates_types' => true]);
      Plugin::registerClass('PluginConsumablesRequest', ['addtabon'                    => 'Group',
                                                         'notificationtemplates_types' => true]);
      Plugin::registerClass('PluginConsumablesRequest', ['addtabon' => 'ConsumableItem']);

      $PLUGIN_HOOKS['item_add']['consumables']        = ['ConsumableItem' => ['PluginConsumablesField', 'postAddConsumable']];
      $PLUGIN_HOOKS['pre_item_update']['consumables'] = ['ConsumableItem' => ['PluginConsumablesField', 'preUpdateConsumable']];

      if (Session::haveRight("plugin_consumables", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['consumables'] = 1;
      }

      if (class_exists('PluginServicecatalogMain')) {
         $PLUGIN_HOOKS['servicecatalog']['consumables'] = ['PluginConsumablesServicecatalog'];
      }

      if (Session::haveRight("plugin_consumables", READ)) {
         $PLUGIN_HOOKS['menu_toadd']['consumables'] = ['management' => 'PluginConsumablesMenu'];
      }
      if (Session::haveRight("plugin_consumables", READ)
          && !class_exists('PluginServicecatalogMain')) {
         $PLUGIN_HOOKS['helpdesk_menu_entry']['consumables'] = PLUGIN_CONSUMABLES_NOTFULL_WEBDIR.'/front/wizard.php';
      }

      // Post item purge
      $PLUGIN_HOOKS['item_purge']['consumables'] = ['ConsumableItem' => 'plugin_item_purge_consumables'];
   }
}

// Get the name and the version of the plugin - Needed

/**
 * @return array
 */
function plugin_version_consumables() {

   return [
      'name'         => _n('Consumable request', 'Consumable requests', 1, 'consumables'),
      'version'      => PLUGIN_CONSUMABLES_VERSION,
      'author'       => "<a href='http://blogglpi.infotel.com'>Infotel</a>",
      'license'      => 'GPLv2+',
      'homepage'     => 'https://github.com/InfotelGLPI/consumables',
      'requirements' => [
         'glpi' => [
            'min' => '10.0',
            'max' => '11.0',
            'dev' => false
         ]
      ]
   ];
}
