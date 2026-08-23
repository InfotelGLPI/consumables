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

DROP TABLE IF EXISTS `glpi_plugin_consumables_options`;
CREATE TABLE `glpi_plugin_consumables_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumables_id` int(11) NOT NULL DEFAULT '0',
  `groups` longtext collate utf8_unicode_ci DEFAULT NULL,
  `max_cart` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE = MyISAM DEFAULT CHARSET = utf8 collate = utf8_unicode_ci AUTO_INCREMENT = 1;