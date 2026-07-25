<?php

/*
 -------------------------------------------------------------------------
 consumables plugin for GLPI
 Copyright (C) 2015-2026 by the consumables Development Team.

 https://github.com/InfotelGLPI/consumables
 -------------------------------------------------------------------------

 LICENSE

 This file is part of consumables.

 consumables is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 consumables is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with consumables. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkRight('plugin_consumables_request', READ);

global $CFG_GLPI;

// Make a select box. The "give to" wizard only targets User and Group, so restrict
// the itemtype to that whitelist: never emit an IDOR token for a client-chosen itemtype.
$idtable = $_POST["idtable"] ?? '';
if (in_array($idtable, ['User', 'Group'], true)) {
    $dbu = new DbUtils();
    $table = $dbu->getTableForItemType($idtable);

   // Link to user for search only > normal users
    $link = "getDropdownValue.php";

    if ($idtable == 'User') {
        $link = "getDropdownUsers.php";
    }

    $rand = mt_rand();

    $field_id = Html::cleanId("dropdown_" . $_POST["name"] . $rand);

    $p        = [
      'value'               => 0,
      'valuename'           => Dropdown::EMPTY_VALUE,
      'itemtype'            => $idtable,
      'display_emptychoice' => true,
      'displaywith'         => ['otherserial', 'serial'],
      // Force the entity scope server-side; the client value is not trusted.
      'entity_restrict'     => $_SESSION['glpiactiveentities'],
      '_idor_token'         => Session::getNewIDORToken($idtable),
    ];
    if (isset($_POST['value'])) {
        $p['value'] = (int) $_POST['value'];
    }
    // Client-supplied entity_restrict / condition are intentionally ignored (IDOR hardening).
    if ($idtable == 'Group') {
        $groups      = Group_User::getUserGroups(Session::getLoginUserID());
        $user_groups = [];
        foreach ($groups as $group) {
            $user_groups[] = $group['id'];
        }
        $p['condition'] = Dropdown::addNewCondition(["id" =>$user_groups]);
    }

    echo Html::jsAjaxDropdown(
        $_POST["name"],
        $field_id,
        $CFG_GLPI['root_doc'] . "/ajax/" . $link,
        $p
    );

    if (!empty($_POST['showItemSpecificity'])) {
        $params = ['items_id'        => '__VALUE__',
                 'itemtype'          => $idtable,
                 'entity_restrict'   => $_SESSION['glpiactiveentities']];

        Ajax::updateItemOnSelectEvent(
            $field_id,
            "showItemSpecificity_" . $_POST["name"] . "$rand",
            $_POST['showItemSpecificity'],
            $params
        );

        echo \Glpi\Application\View\TemplateRenderer::getInstance()->render('@consumables/select_item_span.html.twig', [
            'show_id' => "showItemSpecificity_" . $_POST["name"] . $rand,
        ]);
    }
}
