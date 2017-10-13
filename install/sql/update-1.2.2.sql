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
) ENGINE = MyISAM DEFAULT CHARSET = utf8 collate = utf8_unicode_ci AUTO_INCREMENT = 1;