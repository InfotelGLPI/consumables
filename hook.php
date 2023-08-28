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

/**
 * @return bool
 */
function plugin_consumables_install() {
   global $DB;

   include_once(PLUGIN_CONSUMABLES_DIR . "/inc/profile.class.php");

   if (!$DB->tableExists("glpi_plugin_consumables_requests")) {
      // Install script
      $DB->runFile(PLUGIN_CONSUMABLES_DIR . "/install/sql/empty-2.0.1.sql");
      include(PLUGIN_CONSUMABLES_DIR . "/install/install.php");
      install();
   } else if (!$DB->tableExists("glpi_plugin_consumables_options")) {
      $DB->runFile(PLUGIN_CONSUMABLES_DIR . "/install/sql/update-1.2.2.sql");
   }  else if (!$DB->fieldExists("glpi_plugin_consumables_options", "consumableitems_id")) {
       $DB->runFile(PLUGIN_CONSUMABLES_DIR . "/install/sql/update-2.0.1.sql");
   }

   PluginConsumablesProfile::initProfile();
   PluginConsumablesProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

   return true;
}

/**
 * @return bool
 */
function plugin_consumables_uninstall() {
   global $DB;

   include_once(PLUGIN_CONSUMABLES_DIR . "/inc/profile.class.php");
   include_once(PLUGIN_CONSUMABLES_DIR . "/inc/menu.class.php");

   $tables = ["glpi_plugin_consumables_profiles",
              "glpi_plugin_consumables_requests",
              "glpi_plugin_consumables_options",
              "glpi_plugin_consumables_fields"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $options = ['itemtype' => 'PluginConsumablesRequest',
               'event'    => 'ConsumableRequest',
               'FIELDS'   => 'id'];

   $notif = new Notification();
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   $options = ['itemtype' => 'PluginConsumablesRequest',
               'event'    => 'ConsumableResponse',
               'FIELDS'   => 'id'];

   $notif = new Notification();
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   //templates
   $template       = new NotificationTemplate();
   $translation    = new NotificationTemplateTranslation();
   $notif_template = new Notification_NotificationTemplate();
   $options        = ['itemtype' => 'PluginConsumablesRequest',
                      'FIELDS'   => 'id'];

   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = ['notificationtemplates_id' => $data['id'],
                           'FIELDS'                   => 'id'];
      foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
         $translation->delete($data_template);
      }
      $template->delete($data);

      foreach ($DB->request('glpi_notifications_notificationtemplates', $options_template) as $data_template) {
         $notif_template->delete($data_template);
      }
   }

   // Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginConsumablesProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }

   PluginConsumablesMenu::removeRightsFromSession();
   PluginConsumablesProfile::removeRightsFromSession();

   return true;
}

// Hook done on purge item case
/**
 * @param $item
 */
function plugin_item_purge_consumables($item) {
   switch (get_class($item)) {
      case 'ConsumableItem' :
         $temp = new PluginConsumablesRequest();
         $temp->deleteByCriteria(['consumableitems_id' => $item->getField('id')], 1);
         break;
   }
}

// Define dropdown relations
/**
 * @return array
 */
function plugin_consumables_getDatabaseRelations() {

   if (Plugin::isPluginActive("consumables")) {
      return ["glpi_profiles"        => ["glpi_plugin_consumables_profiles" => "profiles_id"],
              "glpi_consumableitems" => ["glpi_plugin_consumables_requests" => "consumableitems_id"],
              "glpi_consumableitems" => ["glpi_plugin_consumables_options" => "consumableitems_id"]];
   } else {
      return [];
   }
}

// Define search option for types of the plugins
/**
 * @param $itemtype
 *
 * @return array
 */
function plugin_consumables_getAddSearchOptions($itemtype) {

   $sopt = [];

   if ($itemtype == "ConsumableItem") {
      if (Session::haveRight("plugin_consumables", READ)) {
         $sopt[] = [
            'id'            => 185,
            'table'         => 'glpi_plugin_consumables_fields',
            'field'         => 'order_ref',
            'name'          => __('Order reference', 'consumables'),
            'datatype'      => "text",
            'joinparams'    => ['jointype'  => 'child',
                                'linkfield' => 'consumableitems_id'],
            'massiveaction' => false
         ];
         $sopt[] = [
            'id'            => 186,
            'table'         => 'glpi_plugin_consumables_options',
            'field'         => 'max_cart',
            'name'          => __('Maximum number allowed for request', 'consumables'),
            'datatype'      => "number",
            'linkfield'     => 'consumableitems_id',
            'joinparams'    => ['jointype'  => 'child',
                                'linkfield' => 'consumableitems_id'],
            'massiveaction' => false,
            'searchtype'    => 'equals'
         ];
         $sopt[] = [
            'id'            => 187,
            'table'         => 'glpi_plugin_consumables_options',
            'field'         => 'groups',
            'name'          => __('Allowed groups for request', 'consumables'),
            'datatype'      => "specific",
            'linkfield'     => 'consumableitems_id',
            'joinparams'    => ['jointype'  => 'child',
                                'linkfield' => 'consumableitems_id'],
            'massiveaction' => false,
            'nosearch'      => 'true'
         ];
      }
   }

   return $sopt;
}

function plugin_consumables_MassiveActions($type) {

   switch ($type) {
      case 'ConsumableItem':
         return [
            'PluginConsumablesOption' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_number' =>
               __('Maximum number allowed for request', 'consumables'),
            'PluginConsumablesOption' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_groups' =>
               __('Add a group for request', 'consumables')];
         break;
   }
   return [];
}
