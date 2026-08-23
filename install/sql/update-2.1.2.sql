--
-- -------------------------------------------------------------------------
-- consumables plugin for GLPI
-- Copyright (C) 2015-2026 by the consumables Development Team.
--
-- https://github.com/InfotelGLPI/consumables
-- -------------------------------------------------------------------------
--
-- LICENSE
--
-- This file is part of consumables.
--
-- consumables is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- consumables is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with consumables. If not, see <http://www.gnu.org/licenses/>.
-- --------------------------------------------------------------------------
--

CREATE TABLE `glpi_plugin_consumables_helpdesks_tiles_consumablespagetiles` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) DEFAULT NULL,
    `description` text DEFAULT null,
    `illustration` varchar(255) DEFAULT NULL,
    `url` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
