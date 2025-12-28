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

 consumables is free software;
if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }
 you can redistribute it and/or modify
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

use GlpiPlugin\Consumables\Menu;
use GlpiPlugin\Consumables\Option;
use GlpiPlugin\Consumables\Profile;
use GlpiPlugin\Consumables\Request;

/**
 * Instala o plugin Consumables
 */
function plugin_consumables_install(): bool
{
    global $DB;
    try {
        if (!$DB->tableExists("glpi_plugin_consumables_requests")) {
            // Install script
            $DB->runFile(PLUGIN_CONSUMABLES_DIR . "/install/sql/empty-2.0.1.sql");
            include(PLUGIN_CONSUMABLES_DIR . "/install/install.php");
            install_notifications_consumables();
        } elseif (!$DB->tableExists("glpi_plugin_consumables_options")) {
            $DB->runFile(PLUGIN_CONSUMABLES_DIR . "/install/sql/update-1.2.2.sql");
        } elseif (!$DB->fieldExists("glpi_plugin_consumables_options", "consumableitems_id")) {
            $DB->runFile(PLUGIN_CONSUMABLES_DIR . "/install/sql/update-2.0.1.sql");
        }

        Profile::initProfile();
        $profile_id = null;
        $session_status = function_exists('session_status') ? session_status() : PHP_SESSION_DISABLED;
        if ($session_status === PHP_SESSION_ACTIVE && isset($_SESSION) && is_array($_SESSION)) {
            if (array_key_exists('glpiactiveprofile', $_SESSION) && is_array($_SESSION['glpiactiveprofile']) && array_key_exists('id', $_SESSION['glpiactiveprofile']) && !empty($_SESSION['glpiactiveprofile']['id'])) {
                $profile_id = $_SESSION['glpiactiveprofile']['id'];
            }
        }
        if ($profile_id !== null) {
            Profile::createFirstAccess($profile_id);
        } else if ($session_status === PHP_SESSION_ACTIVE && isset($_SESSION) && is_array($_SESSION) && array_key_exists('glpiname', $_SESSION)) {
            if (class_exists('Toolbox')) {
                Toolbox::logInFile('consumables', sprintf(
                    'WARNING [%s:%s] glpiactiveprofile not set or invalid during install, user=%s, session_keys=%s',
                    __FILE__, __FUNCTION__, $_SESSION['glpiname'], implode(',', array_keys($_SESSION))
                ));
            }
        } else {
            if (class_exists('Toolbox')) {
                Toolbox::logInFile('consumables', sprintf(
                    'WARNING [%s:%s] Session not active or missing, session_status=%s',
                    __FILE__, __FUNCTION__, (string)$session_status
                ));
            }
        }
        if (class_exists('Toolbox')) {
            Toolbox::logInFile('consumables', sprintf(
                'INFO [%s:%s] Plugin installed successfully by user=%s',
                __FILE__, __FUNCTION__, $_SESSION['glpiname'] ?? 'unknown'
            ));
        }
        return true;
    } catch (\Exception $e) {
        if (class_exists('Toolbox')) {
            Toolbox::logInFile('consumables', sprintf(
                'ERROR [%s:%s] Install error: %s, user=%s',
                __FILE__, __FUNCTION__, $e->getMessage(), $_SESSION['glpiname'] ?? 'unknown'
            ));
        }
        error_log("Consumables install error: " . $e->getMessage());
        return false;
    }
}


/**
 * Desinstala o plugin Consumables
 *
 * @return bool
 */
function plugin_consumables_uninstall(): bool
{
    global $DB;
    try {
        $tables = [
            "glpi_plugin_consumables_profiles",
            "glpi_plugin_consumables_requests",
            "glpi_plugin_consumables_options",
            "glpi_plugin_consumables_fields"
        ];

        foreach ($tables as $table) {
            $DB->dropTable($table, true);
        }

        $notif   = new Notification();
        $options = ['itemtype' => Request::class];
        foreach ($DB->request([
            'FROM' => 'glpi_notifications',
            'WHERE' => $options]) as $data) {
            $notif->delete($data);
        }

        //templates
        $template       = new NotificationTemplate();
        $translation    = new NotificationTemplateTranslation();
        $notif_template = new Notification_NotificationTemplate();
        $options        = ['itemtype' => Request::class];
        foreach ($DB->request([
            'FROM' => 'glpi_notificationtemplates',
            'WHERE' => $options]) as $data) {
            $options_template = [
                'notificationtemplates_id' => $data['id']
            ];

            foreach ($DB->request([
                'FROM' => 'glpi_notificationtemplatetranslations',
                'WHERE' => $options_template]) as $data_template) {
                $translation->delete($data_template);
            }
            $template->delete($data);

            foreach ($DB->request([
                'FROM' => 'glpi_notifications_notificationtemplates',
                'WHERE' => $options_template]) as $data_template) {
                $notif_template->delete($data_template);
            }
        }

        $itemtypes = [
            'Alert',
            'DisplayPreference',
            'Document_Item',
            'ImpactItem',
            'Item_Ticket',
            'Link_Itemtype',
            'Notepad',
            'SavedSearch',
            'DropdownTranslation',
            'NotificationTemplate',
            'Notification'
        ];
        foreach ($itemtypes as $itemtype) {
            $item = new $itemtype;
            $item->deleteByCriteria(['itemtype' => Request::class]);
        }

        // Delete rights associated with the plugin
        $profileRight = new ProfileRight();
        foreach (Profile::getAllRights() as $right) {
            $profileRight->deleteByCriteria(['name' => $right['field']]);
        }

        Menu::removeRightsFromSession();
        Profile::removeRightsFromSession();

        if (class_exists('Toolbox')) {
            Toolbox::logInFile('consumables', sprintf(
                'INFO [%s:%s] Plugin uninstalled successfully by user=%s',
                __FILE__, __FUNCTION__, $_SESSION['glpiname'] ?? 'unknown'
            ));
        }
        return true;
    } catch (\Exception $e) {
        if (class_exists('Toolbox')) {
            Toolbox::logInFile('consumables', sprintf(
                'ERROR [%s:%s] Uninstall error: %s, user=%s',
                __FILE__, __FUNCTION__, $e->getMessage(), $_SESSION['glpiname'] ?? 'unknown'
            ));
        }
        error_log("Consumables uninstall error: " . $e->getMessage());
        return false;
    }
}

// Hook done on purge item case

/**
 * Hook executado ao purgar item
 *
 * @param object $item
 * @return void
 */
function plugin_item_purge_consumables(object $item): void
{
    switch (get_class($item)) {
        case 'ConsumableItem':
            $temp = new Request();
            $temp->deleteByCriteria(['consumableitems_id' => $item->getField('id')], 1);
            break;
    }
}

// Define dropdown relations

/**
 * Define relações de dropdown
 *
 * @return array
 */
function plugin_consumables_getDatabaseRelations(): array
{
    if (\Plugin::isPluginActive("consumables")) {
            return [
            "glpi_consumableitems" => [
                "glpi_plugin_consumables_options" => "consumableitems_id"
            ]
        ];
    }
    return [];
}

// Define search option for types of the plugins

/**
 * Define opções de busca para tipos do plugin
 *
 * @param string $itemtype
 * @return array
 */
function plugin_consumables_getAddSearchOptions(string $itemtype): array
{
    $sopt = [];

    if ($itemtype === "ConsumableItem") {
        if (Session::haveRight("plugin_consumables", READ)) {
            $sopt[185]['table']         = 'glpi_plugin_consumables_fields';
            $sopt[185]['field']         = 'order_ref';
            $sopt[185]['name']          = __('Order reference', 'consumables');
            $sopt[185]['datatype']      = "text";
            $sopt[185]['joinparams']    = ['jointype'  => 'child', 'linkfield' => 'consumableitems_id'];
            $sopt[185]['massiveaction'] = false;

            $sopt[186]['table']         = 'glpi_plugin_consumables_options';
            $sopt[186]['field']         = 'max_cart';
            $sopt[186]['name']          = __('Maximum number allowed for request', 'consumables');
            $sopt[186]['datatype']      = "number";
            $sopt[186]['linkfield']     = "consumableitems_id";
            $sopt[186]['joinparams']    = ['jointype'  => 'child', 'linkfield' => 'consumableitems_id'];
            $sopt[186]['massiveaction'] = false;

            $sopt[187]['table']         = 'glpi_plugin_consumables_options';
            $sopt[187]['field']         = 'groups';
            $sopt[187]['name']          = __('Allowed groups for request', 'consumables');
            $sopt[187]['datatype']      = "specific";
            $sopt[187]['linkfield']     = "consumableitems_id";
            $sopt[187]['joinparams']    = ['jointype'  => 'child', 'linkfield' => 'consumableitems_id'];
            $sopt[187]['massiveaction'] = false;
            $sopt[187]['nosearch']      = true;
        }
    }

    return $sopt;
}


/**
 * Define ações em massa para o plugin
 *
 * @param string $type
 * @return array
 */
function plugin_consumables_MassiveActions(string $type): array
{
    switch ($type) {
        case 'ConsumableItem':
            return [
                Option::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_number' => __('Maximum number allowed for request', 'consumables'),
                Option::class . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_groups'  => __('Add a group for request', 'consumables')
            ];
    }
    return [];
}
