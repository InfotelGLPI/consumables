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

DROP TABLE IF EXISTS `glpi_plugin_consumables_profiles`;
CREATE TABLE `glpi_plugin_consumables_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profiles_id` int(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_profiles (id)',
  `consumables` char(1) collate utf8_unicode_ci DEFAULT NULL,
  `consumables_request` char(1) collate utf8_unicode_ci DEFAULT NULL,
  `consumables_for_all` char(1) collate utf8_unicode_ci DEFAULT NULL,
  `consumables_for_group` char(1) collate utf8_unicode_ci DEFAULT NULL,
  `validate`  char(1) collate utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_id` (`profiles_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 collate = utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_requests'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_requests`;
CREATE TABLE `glpi_plugin_consumables_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumables_id` int(11) NOT NULL DEFAULT '0',
  `consumableitemtypes_id` int(11) NOT NULL DEFAULT '0',
  `requesters_id` int(11) NOT NULL DEFAULT '0',
  `validators_id` int(11) NOT NULL DEFAULT '0',
  `give_itemtype` varchar(255) DEFAULT NULL,
  `give_items_id` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '2',
  `number` int(11) NOT NULL DEFAULT '0',
  `end_date` datetime DEFAULT NULL,
  `date_mod` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consumables_id` (`consumables_id`),
  KEY `requesters_id` (`requesters_id`),
  KEY `validators_id` (`validators_id`),
  KEY `date_mod` (`date_mod`),
  KEY `end_date` (`end_date`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 collate = utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_fields'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_fields`;
CREATE TABLE `glpi_plugin_consumables_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumables_id` int(11) NOT NULL DEFAULT '0',
  `order_ref` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consumables_id` (`consumables_id`),
  UNIQUE KEY `unicity` (`consumables_id`, `order_ref`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 collate = utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_options'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_options`;
CREATE TABLE `glpi_plugin_consumables_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumables_id` int(11) NOT NULL DEFAULT '0',
  `groups` longtext collate utf8_unicode_ci DEFAULT NULL,
  `max_cart` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 collate = utf8_unicode_ci AUTO_INCREMENT = 1;