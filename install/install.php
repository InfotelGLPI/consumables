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

use GlpiPlugin\Consumables\Request;

/**
 * Install
 *
 * @return bool for success (will die for most error)
 * */
function install_notifications_consumables()
{
    global $DB;

    $migration = new Migration(100);

   // Notification
    // Request
    $options_notif        = ['itemtype' => Request::class,
        'name' => 'Consumables Request'];
    $DB->insert(
        "glpi_notificationtemplates",
        $options_notif
    );

    foreach ($DB->request([
        'FROM' => 'glpi_notificationtemplates',
        'WHERE' => $options_notif]) as $data) {
        $templates_id = $data['id'];

        if ($templates_id) {
            $DB->insert(
                "glpi_notificationtemplatetranslations",
                [
                    'notificationtemplates_id' => $templates_id,
                    'subject' => '##consumable.action## : ##consumable.entity##',
                    'content_text' => '##FOREACHconsumabledata##
##lang.consumable.entity## :##consumable.entity##
##lang.consumablerequest.requester## : ##consumablerequest.requester##
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##
##lang.consumablerequest.number## : ##consumablerequest.number##
##lang.consumablerequest.requestdate## : ##consumablerequest.requestdate##
##lang.consumablerequest.status## : ##consumablerequest.status##
##ENDFOREACHconsumabledata##',
                    'content_html' => '##FOREACHconsumabledata##&lt;br /&gt; &lt;br /&gt;
&lt;p&gt;##lang.consumable.entity## :##consumable.entity##&lt;br /&gt; &lt;br /&gt;
##lang.consumablerequest.requester## : ##consumablerequest.requester##&lt;br /&gt;
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##&lt;br /&gt;
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##&lt;br /&gt;
##lang.consumablerequest.number## : ##consumablerequest.number##&lt;br /&gt;
##lang.consumablerequest.requestdate## : ##consumablerequest.requestdate##&lt;br /&gt;
##lang.consumablerequest.status## : ##consumablerequest.status##&lt;br /&gt;
##ENDFOREACHconsumabledata##',
                ]
            );

            $DB->insert(
                "glpi_notifications",
                [
                    'name' => 'Consumable request',
                    'entities_id' => 0,
                    'itemtype' => Request::class,
                    'event' => 'ConsumableRequest',
                    'is_recursive' => 1,
                ]
            );

            $options_notif        = ['itemtype' => Request::class,
                'name' => 'Consumable request',
                'event' => 'ConsumableRequest'];

            foreach ($DB->request([
                'FROM' => 'glpi_notifications',
                'WHERE' => $options_notif]) as $data_notif) {
                $notification = $data_notif['id'];
                if ($notification) {
                    $DB->insert(
                        "glpi_notifications_notificationtemplates",
                        [
                            'notifications_id' => $notification,
                            'mode' => 'mailing',
                            'notificationtemplates_id' => $templates_id,
                        ]
                    );
                }
            }
        }
    }

    // Request validation
    $options_notif        = ['itemtype' => Request::class,
        'name' => 'Consumables Request Validation'];
    // Request
    $DB->insert(
        "glpi_notificationtemplates",
        $options_notif
    );

    foreach ($DB->request([
        'FROM' => 'glpi_notificationtemplates',
        'WHERE' => $options_notif]) as $data) {
        $templates_id = $data['id'];

        if ($templates_id) {
            $DB->insert(
                "glpi_notificationtemplatetranslations",
                [
                    'notificationtemplates_id' => $templates_id,
                    'subject' => '##consumable.action## : ##consumable.entity##',
                    'content_text' => '##FOREACHconsumabledata##
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
                    'content_html' => '##FOREACHconsumabledata##&lt;br /&gt; &lt;br /&gt;
&lt;p&gt;##lang.consumable.entity## :##consumable.entity##&lt;br /&gt; &lt;br /&gt;
##lang.consumablerequest.requester## : ##consumablerequest.requester##&lt;br /&gt;
##lang.consumablerequest.validator## : ##consumablerequest.validator##&lt;br /&gt;
##lang.consumablerequest.consumabletype## : ##consumablerequest.consumabletype##&lt;br /&gt;
##lang.consumablerequest.consumable## : ##consumablerequest.consumable##&lt;br /&gt;
##lang.consumablerequest.number## : ##consumablerequest.number##&lt;br /&gt;
##lang.consumablerequest.requestdate## : ##consumablerequest.requestdate##&lt;br /&gt;
##lang.consumablerequest.status## : ##consumablerequest.status##&lt;br /&gt;
##lang.consumablerequest.comment## : ##consumablerequest.comment##&lt;br /&gt;
##ENDFOREACHconsumabledata##',
                ]
            );

            $DB->insert(
                "glpi_notifications",
                [
                    'name' => 'Consumable request validation',
                    'entities_id' => 0,
                    'itemtype' => Request::class,
                    'event' => 'ConsumableResponse',
                    'is_recursive' => 1,
                ]
            );

            $options_notif        = ['itemtype' => Request::class,
                'name' => 'Consumable request validation',
                'event' => 'ConsumableResponse'];

            foreach ($DB->request([
                'FROM' => 'glpi_notifications',
                'WHERE' => $options_notif]) as $data_notif) {
                $notification = $data_notif['id'];
                if ($notification) {
                    $DB->insert(
                        "glpi_notifications_notificationtemplates",
                        [
                            'notifications_id' => $notification,
                            'mode' => 'mailing',
                            'notificationtemplates_id' => $templates_id,
                        ]
                    );
                }
            }
        }
    }

    $migration->executeMigration();

    return true;
}
