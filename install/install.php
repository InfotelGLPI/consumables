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

/**
 * Install
 *
 * @return bool for success (will die for most error)
 * */
function install()
{
   global $DB;

   $migration = new Migration(100);

   // Install script
   $DB->runFile(GLPI_ROOT . "/plugins/consumables/install/sql/empty-1.0.0.sql");

   // Notification
   // Request
   $query_id = "INSERT INTO `glpi_notificationtemplates`(`name`, `itemtype`, `date_mod`) VALUES ('Consumables Request','PluginConsumablesRequest', NOW());";
   $result = $DB->query($query_id) or die($DB->error());
   $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginConsumablesRequest' AND `name` = 'Consumables Request'";
   $result = $DB->query($query_id) or die($DB->error());
   $itemtype = $DB->result($result, 0, 'id');

   $query = "INSERT INTO `glpi_notificationtemplatetranslations` (`notificationtemplates_id`, `subject`, `content_text`, `content_html`)
VALUES('" . $itemtype . "', '##consumable.action## : ##consumable.entity##',
'##FOREACHconsumabledatas##
##lang.consumable.entity## :##consumable.entity##
##lang.consumablerequest.requester## : ##consumablerequest.requester##
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##
##lang.consumablerequest.number## : ##consumablerequest.number##
##lang.consumablerequest.request_date## : ##consumablerequest.request_date##
##lang.consumablerequest.status## : ##consumablerequest.status##
##ENDFOREACHconsumabledatas##',
'##FOREACHconsumabledatas##&lt;br /&gt; &lt;br /&gt;
&lt;p&gt;##lang.consumable.entity## :##consumable.entity##&lt;br /&gt; &lt;br /&gt;
##lang.consumablerequest.requester## : ##consumablerequest.requester##&lt;br /&gt;
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##&lt;br /&gt;
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##&lt;br /&gt;
##lang.consumablerequest.number## : ##consumablerequest.number##&lt;br /&gt;
##lang.consumablerequest.request_date## : ##consumablerequest.request_date##&lt;br /&gt;
##lang.consumablerequest.status## : ##consumablerequest.status##&lt;br /&gt;
##ENDFOREACHconsumabledatas##');";
   $DB->query($query);

   $query = "INSERT INTO `glpi_notifications` (`name`, `entities_id`, `itemtype`, `event`, `mode`, `notificationtemplates_id`, `is_recursive`)
              VALUES ('Consumable request', 0, 'PluginConsumablesRequest', 'ConsumableRequest',
                     'mail','" . $itemtype . "', 1);";
   $DB->query($query);

   // Request validation
   $query_id = "INSERT INTO `glpi_notificationtemplates`(`name`, `itemtype`, `date_mod`, `comment`, `css`) VALUES ('Consumables Request Validation','PluginConsumablesRequest', NOW(),'','');";
   $result = $DB->query($query_id) or die($DB->error());
   $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginConsumablesRequest' AND `name` = 'Consumables Request Validation'";
   $result = $DB->query($query_id) or die($DB->error());
   $itemtype = $DB->result($result, 0, 'id');

   $query = "INSERT INTO `glpi_notificationtemplatetranslations` (`notificationtemplates_id`, `subject`, `content_text`, `content_html`)
VALUES('" . $itemtype . "', '##consumable.action## : ##consumable.entity##',
'##FOREACHconsumabledatas##
##lang.consumable.entity## :##consumable.entity##
##lang.consumablerequest.requester## : ##consumablerequest.requester##
##lang.consumablerequest.validator## : ##consumablerequest.validator##
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##
##lang.consumablerequest.number## : ##consumablerequest.number##
##lang.consumablerequest.request_date## : ##consumablerequest.request_date##
##lang.consumablerequest.status## : ##consumablerequest.status##
##ENDFOREACHconsumabledatas##
##lang.consumablerequest.comment## : ##consumablerequest.comment##',
'##FOREACHconsumabledatas##&lt;br /&gt; &lt;br /&gt;
&lt;p&gt;##lang.consumable.entity## :##consumable.entity##&lt;br /&gt; &lt;br /&gt;
##lang.consumablerequest.requester## : ##consumablerequest.requester##&lt;br /&gt;
##lang.consumablerequest.validator## : ##consumablerequest.validator##&lt;br /&gt;
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##&lt;br /&gt;
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##&lt;br /&gt;
##lang.consumablerequest.number## : ##consumablerequest.number##&lt;br /&gt;
##lang.consumablerequest.request_date## : ##consumablerequest.request_date##&lt;br /&gt;
##lang.consumablerequest.status## : ##consumablerequest.status##&lt;br /&gt;
##lang.consumablerequest.comment## : ##consumablerequest.comment##&lt;br /&gt;
##ENDFOREACHconsumabledatas##');";
   $DB->query($query);

   $query = "INSERT INTO `glpi_notifications` (`name`, `entities_id`, `itemtype`, `event`, `mode`, `notificationtemplates_id`, `is_recursive`)
              VALUES ('Consumable request validation', 0, 'PluginConsumablesRequest', 'ConsumableResponse',
                     'mail','" . $itemtype . "', 1);";
   $DB->query($query);

   $migration->executeMigration();

   return true;
}