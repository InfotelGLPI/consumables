-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_options'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_options`;

CREATE TABLE `glpi_plugin_consumables_options` (
  `id`             INT(11)     NOT NULL                 AUTO_INCREMENT,
  `consumables_id` INT(11)     NOT NULL                 DEFAULT '0',
  `groups`         LONGTEXT COLLATE utf8_unicode_ci     DEFAULT NULL,
  `max_cart`       SMALLINT(6) NOT NULL                 DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 1;