-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_profiles'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_profiles`;
CREATE TABLE `glpi_plugin_consumables_profiles` (
  `id`                    INT(11) NOT NULL        AUTO_INCREMENT,
  `profiles_id`           INT(11) NOT NULL        DEFAULT '0'
  COMMENT 'RELATION to glpi_profiles (id)',
  `consumables`           CHAR(1)
                          COLLATE utf8_unicode_ci DEFAULT NULL,
  `consumables_request`   CHAR(1)
                          COLLATE utf8_unicode_ci DEFAULT NULL,
  `consumables_for_all`   CHAR(1)
                          COLLATE utf8_unicode_ci DEFAULT NULL,
  `consumables_for_group` CHAR(1)
                          COLLATE utf8_unicode_ci DEFAULT NULL,
  `validate`              CHAR(1)
                          COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_id` (`profiles_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_requests'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_requests`;
CREATE TABLE `glpi_plugin_consumables_requests` (
  `id`                     INT(11) NOT NULL AUTO_INCREMENT,
  `consumables_id`         INT(11) NOT NULL DEFAULT '0',
  `consumableitemtypes_id` INT(11) NOT NULL DEFAULT '0',
  `requesters_id`          INT(11) NOT NULL DEFAULT '0',
  `validators_id`          INT(11) NOT NULL DEFAULT '0',
  `give_itemtype`          VARCHAR(255)     DEFAULT NULL,
  `give_items_id`          INT(11) NOT NULL DEFAULT '0',
  `status`                 INT(11) NOT NULL DEFAULT '2',
  `number`                 INT(11) NOT NULL DEFAULT '0',
  `end_date`               DATETIME         DEFAULT NULL,
  `date_mod`               DATETIME         DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consumables_id` (`consumables_id`),
  KEY `requesters_id` (`requesters_id`),
  KEY `validators_id` (`validators_id`),
  KEY `date_mod` (`date_mod`),
  KEY `end_date` (`end_date`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_fields'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_fields`;
CREATE TABLE `glpi_plugin_consumables_fields` (
  `id`             INT(11) NOT NULL AUTO_INCREMENT,
  `consumables_id` INT(11) NOT NULL DEFAULT '0',
  `order_ref`      VARCHAR(255)     DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consumables_id` (`consumables_id`),
  UNIQUE KEY `unicity` (`consumables_id`, `order_ref`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;