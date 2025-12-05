-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_requests'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_requests`;
CREATE TABLE `glpi_plugin_consumables_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
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
