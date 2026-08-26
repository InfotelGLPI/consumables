<?php

/**
 * -------------------------------------------------------------------------
 * consumables plugin for GLPI
 * Copyright (C) 2015-2026 by the consumables Development Team.
 *
 * https://github.com/InfotelGLPI/consumables
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of consumables.
 *
 * consumables is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * consumables is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with consumables. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

use GlpiPlugin\Consumables\Menu;
use GlpiPlugin\Consumables\Validation;
use GlpiPlugin\Consumables\Wizard;
use GlpiPlugin\Servicecatalog\Main;

Session::checkRight('plugin_consumables_validation', 1);

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
    Html::header(Wizard::getTypeName(2), '', "management", Menu::class);
} else {
    if (Plugin::isPluginActive('servicecatalog')) {
        Main::showDefaultHeaderHelpdesk(Wizard::getTypeName(2));
    } else {
        Html::helpHeader(Wizard::getTypeName(2));
    }
}

// Route through showConsumableValidation() rather than Search::showList(): the
// requests table has no entities_id column, so the search engine adds no entity
// restriction and Search::showList() would leak every entity's requests (requester,
// consumable and beneficiary). This method filters each row through
// requestHasEntityAccess() — the same entity boundary the validate/refuse mass
// actions already enforce.
$validation = new Validation();
$validation->showConsumableValidation();

if (Session::getCurrentInterface() != 'central'
    && Plugin::isPluginActive('servicecatalog')) {

    Main::showNavBarFooter('consumables');
}

if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
    Html::footer();
} else {
    Html::helpFooter();
}
