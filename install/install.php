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
 * Install
 *
 * @return bool for success (will die for most error)
 * */
function install() {
   global $DB;

   $migration = new Migration(100);

   // Notification
   // Request
   $query_id = "INSERT INTO `glpi_notificationtemplates`(`name`, `itemtype`, `date_mod`) VALUES ('Consumables Request','PluginConsumablesRequest', NOW());";
   $result = $DB->query($query_id) or die($DB->error());
   $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginConsumablesRequest' AND `name` = 'Consumables Request'";
   $result = $DB->query($query_id) or die($DB->error());
   $itemtype = $DB->result($result, 0, 'id');

   $query = "INSERT INTO `glpi_notificationtemplatetranslations` (`notificationtemplates_id`, `subject`, `content_text`, `content_html`)
VALUES('" . $itemtype . "', '##consumable.action## : ##consumable.entity##',
'##FOREACHconsumabledata##
##lang.consumable.entity## :##consumable.entity##
##lang.consumablerequest.requester## : ##consumablerequest.requester##
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##
##lang.consumablerequest.number## : ##consumablerequest.number##
##lang.consumablerequest.requestdate## : ##consumablerequest.requestdate##
##lang.consumablerequest.status## : ##consumablerequest.status##
##ENDFOREACHconsumabledata##',
'##FOREACHconsumabledata##&lt;br /&gt; &lt;br /&gt;
&lt;p&gt;##lang.consumable.entity## :##consumable.entity##&lt;br /&gt; &lt;br /&gt;
##lang.consumablerequest.requester## : ##consumablerequest.requester##&lt;br /&gt;
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##&lt;br /&gt;
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##&lt;br /&gt;
##lang.consumablerequest.number## : ##consumablerequest.number##&lt;br /&gt;
##lang.consumablerequest.requestdate## : ##consumablerequest.requestdate##&lt;br /&gt;
##lang.consumablerequest.status## : ##consumablerequest.status##&lt;br /&gt;
##ENDFOREACHconsumabledata##');";
   $DB->query($query);

   $query = "INSERT INTO `glpi_notifications` (`name`, `entities_id`, `itemtype`, `event`, `is_recursive`)
              VALUES ('Consumable request', 0, 'PluginConsumablesRequest', 'ConsumableRequest', 1);";
   $DB->query($query);

   //retrieve notification id
   $query_id = "SELECT `id` FROM `glpi_notifications`
               WHERE `name` = 'Consumable request' AND `itemtype` = 'PluginConsumablesRequest' AND `event` = 'ConsumableRequest'";
   $result = $DB->query($query_id) or die ($DB->error());
   $notification = $DB->result($result, 0, 'id');

   $query = "INSERT INTO `glpi_notifications_notificationtemplates` (`notifications_id`, `mode`, `notificationtemplates_id`) 
               VALUES (" . $notification . ", 'mailing', " . $itemtype . ");";
   $DB->query($query);

   // Request validation
   $query_id = "INSERT INTO `glpi_notificationtemplates`(`name`, `itemtype`, `date_mod`, `comment`, `css`) VALUES ('Consumables Request Validation','PluginConsumablesRequest', NOW(),'','');";
   $result = $DB->query($query_id) or die($DB->error());
   $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginConsumablesRequest' AND `name` = 'Consumables Request Validation'";
   $result = $DB->query($query_id) or die($DB->error());
   $itemtype = $DB->result($result, 0, 'id');

   $query = "INSERT INTO `glpi_notificationtemplatetranslations` (`notificationtemplates_id`, `subject`, `content_text`, `content_html`)
VALUES('" . $itemtype . "', '##consumable.action## : ##consumable.entity##',
'##FOREACHconsumabledata##
##lang.consumable.entity## :##consumable.entity##
##lang.consumablerequest.requester## : ##consumablerequest.requester##
##lang.consumablerequest.validator## : ##consumablerequest.validator##
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##
##lang.consumablerequest.number## : ##consumablerequest.number##
##lang.consumablerequest.requestdate## : ##consumablerequest.requestdate##
##lang.consumablerequest.status## : ##consumablerequest.status##
##ENDFOREACHconsumabledata##
##lang.consumablerequest.comment## : ##consumablerequest.comment##',
'##FOREACHconsumabledata##&lt;br /&gt; &lt;br /&gt;
&lt;p&gt;##lang.consumable.entity## :##consumable.entity##&lt;br /&gt; &lt;br /&gt;
##lang.consumablerequest.requester## : ##consumablerequest.requester##&lt;br /&gt;
##lang.consumablerequest.validator## : ##consumablerequest.validator##&lt;br /&gt;
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##&lt;br /&gt;
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##&lt;br /&gt;
##lang.consumablerequest.number## : ##consumablerequest.number##&lt;br /&gt;
##lang.consumablerequest.requestdate## : ##consumablerequest.requestdate##&lt;br /&gt;
##lang.consumablerequest.status## : ##consumablerequest.status##&lt;br /&gt;
##lang.consumablerequest.comment## : ##consumablerequest.comment##&lt;br /&gt;
##ENDFOREACHconsumabledata##');";
   $DB->query($query);

   $query = "INSERT INTO `glpi_notifications` (`name`, `entities_id`, `itemtype`, `event`, `is_recursive`)
              VALUES ('Consumable request validation', 0, 'PluginConsumablesRequest', 'ConsumableResponse', 1);";
   $DB->query($query);

   //retrieve notification id
   $query_id = "SELECT `id` FROM `glpi_notifications`
               WHERE `name` = 'Consumable request validation' AND `itemtype` = 'PluginConsumablesRequest' 
               AND `event` = 'ConsumableResponse'";
   $result = $DB->query($query_id) or die ($DB->error());
   $notification = $DB->result($result, 0, 'id');

   $query = "INSERT INTO `glpi_notifications_notificationtemplates` (`notifications_id`, `mode`, `notificationtemplates_id`) 
               VALUES (" . $notification . ", 'mailing', " . $itemtype . ");";
   $DB->query($query);

   $migration->executeMigration();

   return true;
}
