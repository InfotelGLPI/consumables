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

DROP TABLE IF EXISTS `glpi_plugin_consumables_requests`;
CREATE TABLE `glpi_plugin_consumables_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entities_id` int unsigned NOT NULL DEFAULT '0',
  `consumableitems_id` int unsigned NOT NULL DEFAULT '0',
  `consumableitemtypes_id` int unsigned NOT NULL DEFAULT '0',
  `requesters_id` int unsigned NOT NULL DEFAULT '0',
  `validators_id` int unsigned NOT NULL DEFAULT '0',
  `give_itemtype` varchar(255) DEFAULT NULL,
  `give_items_id` int unsigned NOT NULL DEFAULT '0',
  `status` int unsigned NOT NULL DEFAULT '2',
  `number` int unsigned NOT NULL DEFAULT '0',
  `end_date` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entities_id` (`entities_id`),
  KEY `consumableitems_id` (`consumableitems_id`),
  KEY `requesters_id` (`requesters_id`),
  KEY `validators_id` (`validators_id`),
  KEY `date_mod` (`date_mod`),
  KEY `end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_fields'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_fields`;
CREATE TABLE `glpi_plugin_consumables_fields` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `consumableitems_id` int unsigned NOT NULL DEFAULT '0',
  `order_ref` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consumableitems_id` (`consumableitems_id`),
  UNIQUE KEY `unicity` (`consumableitems_id`, `order_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_options'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_options`;
CREATE TABLE `glpi_plugin_consumables_options` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `consumableitems_id` int unsigned NOT NULL DEFAULT '0',
  `groups` longtext collate utf8mb4_unicode_ci DEFAULT NULL,
  `max_cart` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_helpdesks_tiles_consumablespagetiles'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_helpdesks_tiles_consumablespagetiles`;
CREATE TABLE `glpi_plugin_consumables_helpdesks_tiles_consumablespagetiles` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) DEFAULT NULL,
    `description` text DEFAULT null,
    `illustration` varchar(255) DEFAULT NULL,
    `url` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
