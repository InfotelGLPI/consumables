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

function plugin_consumables_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/consumables/inc/profile.class.php");

   if (!TableExists("glpi_plugin_consumables_requests")) {
      include(GLPI_ROOT."/plugins/consumables/install/install.php");
      install();
   }

   PluginConsumablesProfile::initProfile();
   PluginConsumablesProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

   return true;
}

function plugin_consumables_uninstall() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/consumables/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/consumables/inc/menu.class.php");

   $tables = array("glpi_plugin_consumables_profiles",
                   "glpi_plugin_consumables_requests");

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");


   $options = array('itemtype' => 'PluginConsumablesRequest',
                    'event'    => 'ConsumableRequest',
                    'FIELDS'   => 'id');

   $notif = new Notification();
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }
   
   $options = array('itemtype' => 'PluginConsumablesRequest',
                    'event'    => 'ConsumableResponse',
                    'FIELDS'   => 'id');

   $notif = new Notification();
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   //templates
   $template    = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $options     = array('itemtype' => 'PluginConsumablesRequest',
                        'FIELDS'   => 'id');
   
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = array('notificationtemplates_id' => $data['id'],
                                'FIELDS'                   => 'id');
      foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
         $translation->delete($data_template);
      }
      $template->delete($data);
   }

   // Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginConsumablesProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
   
   PluginConsumablesMenu::removeRightsFromSession();
   PluginConsumablesProfile::removeRightsFromSession();

   return true;
}

// Hook done on purge item case
function plugin_item_purge_consumables($item) {
   switch (get_class($item)) {
      case 'ConsumableItem' :
         $temp = new PluginConsumablesRequest();
         $temp->deleteByCriteria(array('consumables_id' => $item->getField('id')), 1);
         break;
   }
}

// Define dropdown relations
function plugin_consumables_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("consumables"))
      return array ("glpi_profiles"    => array("glpi_plugin_consumables_profiles"  => "profiles_id"),
                    "glpi_consumables" => array("glpi_plugin_consumables_requests"  => "consumables_id"));
   else
      return array();
}

// Define search option for types of the plugins
function plugin_consumables_getAddSearchOptions($itemtype) {

   $sopt=array();

   if ($itemtype == "ConsumableItem") {
      if (Session::haveRight("plugin_consumables", READ)) {
            $sopt[185]['table']         = 'glpi_plugin_consumables_fields';
            $sopt[185]['field']         = 'order_ref';
            $sopt[185]['name']          = __('Order reference', 'consumables');
            $sopt[185]['datatype']      = "text";
            $sopt[185]['joinparams']    = array('jointype'  => 'child', 
                                                'linkfield' => 'consumables_id');
            $sopt[185]['massiveaction'] = false;
      }
   }
   
   return $sopt;
}

?>