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
 * Class PluginConsumablesProfile
 */
class PluginConsumablesProfile extends Profile
{
    /**
     * @param CommonGLPI $item
     * @param int        $withtemplate
     *
     * @return string|translated
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            return PluginConsumablesMenu::getMenuName();
        }
        return '';
    }

    /**
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            $ID   = $item->getID();
            $prof = new self();

            self::addDefaultProfileInfos($ID, ['plugin_consumables'            => 0,
                                               'plugin_consumables_request'    => 0,
                                               'plugin_consumables_user'       => 0,
                                               'plugin_consumables_group'      => 0,
                                               'plugin_consumables_validation' => 0]);
            $prof->showForm($ID);
        }

        return true;
    }

    /**
     * @param $ID
     */
    public static function createFirstAccess($ID)
    {
        //85
        self::addDefaultProfileInfos(
            $ID,
            ['plugin_consumables'            => ALLSTANDARDRIGHT,
             'plugin_consumables_request'    => 1,
             'plugin_consumables_user'       => 1,
             'plugin_consumables_group'      => 1,
             'plugin_consumables_validation' => 1],
            true
        );
    }

    /**
     * @param      $profiles_id
     * @param      $rights
     * @param bool $drop_existing
     *
     * @internal param $profile
     */
    public static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false)
    {
        $dbu          = new DbUtils();
        $profileRight = new ProfileRight();
        foreach ($rights as $right => $value) {
            if ($dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right]
            ) && $drop_existing) {
                $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
            }
            if (!$dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id, "name" => $right]
            )) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name']        = $right;
                $myright['rights']      = $value;
                $profileRight->add($myright);

                //Add right to the current session
                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }

    /**
     * Show profile form
     *
     * @param int   $profiles_id
     * @param array $options
     *
     * @return nothing
     * @internal param int $items_id id of the profile
     * @internal param value $target url of target
     */
    public function showForm($profiles_id = 0, $openform = true, $closeform = true)
    {
        $profile = new Profile();
        echo "<div class='firstbloc'>";
        if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) && $openform) {
            echo "<form method='post' action='" . $profile->getFormURL() . "'>";
        }

        $profile->getFromDB($profiles_id);

        $rights = $this->getAllRights();
        $profile->displayRightsChoiceMatrix($rights, ['default_class' => 'tab_bg_2',
                                                      'title'         => __('General')]);

        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr class='tab_bg_1'><th colspan='4'>" . __('Advanced', 'consumables') . "</th></tr>\n";

        $effective_rights = ProfileRight::getProfileRights($profiles_id, ['plugin_consumables_user',
                                                                          'plugin_consumables_group',
                                                                          'plugin_consumables_validation',
                                                                          'plugin_consumables_request']);

        echo "<tr class='tab_bg_2'>";
        echo "<td>" . __('Consumable validation', 'consumables') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name'    => '_plugin_consumables_validation[1_0]',
                            'checked' => $effective_rights['plugin_consumables_validation']]);
        echo "<td>" . __('Make a consumable request', 'consumables') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name'    => '_plugin_consumables_request[1_0]',
                            'checked' => $effective_rights['plugin_consumables_request']]);
        echo "</td>";
        echo "</tr>\n";

        echo "<tr class='tab_bg_2'>";
        echo "<td>" . __('Make a consumable request for all users', 'consumables') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name'    => '_plugin_consumables_user[1_0]',
                            'checked' => $effective_rights['plugin_consumables_user']]);
        echo "</td>";
        echo "<td>" . __('Make a consumable request for my groups', 'consumables') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name'    => '_plugin_consumables_group[1_0]',
                            'checked' => $effective_rights['plugin_consumables_group']]);
        echo "</td>";
        echo "</tr>\n";
        echo "</table>";
        if ($canedit && $closeform) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $profiles_id]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'class' => 'btn btn-primary']);
            echo "</div>\n";
            Html::closeForm();
        }

        echo "</div>";
    }

    /**
     * @param bool $all
     *
     * @return array
     */
    public static function getAllRights($all = false)
    {
        $rights = [
           ['itemtype' => 'PluginConsumablesRequest',
            'label'    => _n('Consumable', 'Consumables', 2, 'consumables'),
            'field'    => 'plugin_consumables'
           ],
        ];

        if ($all) {
            $rights[] = ['itemtype' => 'PluginConsumablesRequest',
                         'label'    => __('Make a consumable request for users', 'consumables'),
                         'field'    => 'plugin_consumables_user'];

            $rights[] = ['itemtype' => 'PluginConsumablesRequest',
                         'label'    => __('Make a consumable request', 'consumables'),
                         'field'    => 'plugin_consumables_request'];

            $rights[] = ['itemtype' => 'PluginConsumablesRequest',
                         'label'    => __('Make a consumable request for groups', 'consumables'),
                         'field'    => 'plugin_consumables_group'];

            $rights[] = ['itemtype' => 'PluginConsumablesRequest',
                         'label'    => __('Consumable validation', 'consumables'),
                         'field'    => 'plugin_consumables_validation'];
        }

        return $rights;
    }

    /**
     * Init profiles
     *
     * @param $old_right
     *
     * @return int
     */

    public static function translateARight($old_right)
    {
        switch ($old_right) {
            case '':
                return 0;
            case 'r':
                return READ;
            case 'w':
                return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
            case '0':
            case '1':
                return $old_right;

            default:
                return 0;
        }
    }

    /**
     * @since 0.85
     * Migration rights from old system to the new one for one profile
     *
     * @param $profiles_id the profile ID
     *
     * @return bool
     */
    public static function migrateOneProfile($profiles_id)
    {
        global $DB;
        //Cannot launch migration if there's nothing to migrate...
        if (!$DB->tableExists('glpi_plugin_consumables_profiles')) {
            return true;
        }

        $it = $DB->request([
            'FROM' => 'glpi_plugin_consumables_profiles',
            'WHERE' => ['profiles_id' => $profiles_id]
        ]);
        foreach ($it as $profile_data) {
            $matching       = ['consumables' => 'plugin_consumables',
                               'user'        => 'plugin_consumables_user',
                               'request'     => 'plugin_consumables_request',
                               'group'       => 'plugin_consumables_group',
                               'validation'  => 'plugin_consumables_validation'];
            $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
            foreach ($matching as $old => $new) {
                if (!isset($current_rights[$old])) {
                    $DB->update('glpi_profilerights', ['rights' => self::translateARight($profile_data[$old])], [
                        'name'        => $new,
                        'profiles_id' => $profiles_id
                    ]);
                }
            }
        }
    }

    /**
     * Initialize profiles, and migrate it necessary
     */
    public static function initProfile()
    {
        global $DB;
        $profile = new self();
        $dbu     = new DbUtils();
        //Add new rights in glpi_profilerights table
        foreach ($profile->getAllRights(true) as $data) {
            if ($dbu->countElementsInTable(
                "glpi_profilerights",
                ["name" => $data['field']]
            ) == 0) {
                ProfileRight::addProfileRights([$data['field']]);
            }
        }

        //Migration old rights in new ones
        $it = $DB->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_profiles'
        ]);
        foreach ($it as $prof) {
            self::migrateOneProfile($prof['id']);
        }
        $it = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $_SESSION['glpiactiveprofile']['id'],
                'name' => ['LIKE', '%plugin_consumables%']
            ]
        ]);
        foreach ($it as $prof) {
            if (isset($_SESSION['glpiactiveprofile'])) {
                $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
            }
        }
    }


    public static function removeRightsFromSession()
    {
        foreach (self::getAllRights(true) as $right) {
            if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
                unset($_SESSION['glpiactiveprofile'][$right['field']]);
            }
        }
    }
}
