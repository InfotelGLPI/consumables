-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_profiles'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_profiles`;
CREATE TABLE `glpi_plugin_consumables_profiles` (
	`id` int(11) NOT NULL auto_increment,
	`profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
	`consumables` char(1) collate utf8_unicode_ci default NULL,
        `consumables_request` char(1) collate utf8_unicode_ci default NULL,
        `consumables_for_all` char(1) collate utf8_unicode_ci default NULL,
        `consumables_for_group` char(1) collate utf8_unicode_ci default NULL,
	`validate` char(1) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `profiles_id` (`profiles_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_requests'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_requests`;
CREATE TABLE `glpi_plugin_consumables_requests` (
	`id` int(11) NOT NULL auto_increment,
        `consumables_id` int(11) NOT NULL default '0',
        `consumableitemtypes_id` int(11) NOT NULL default '0',
        `requesters_id` int(11) NOT NULL default '0',
        `validators_id` int(11) NOT NULL default '0',
        `give_itemtype` varchar(255) default NULL,
        `give_items_id` int(11) NOT NULL default '0',
        `status` int(11) NOT NULL default '2',
	`number` int(11) NOT NULL default '0',
        `end_date` datetime default NULL,
        `date_mod` datetime default NULL,
	PRIMARY KEY  (`id`),
        KEY `consumables_id` (`consumables_id`),
        KEY `requesters_id` (`requesters_id`),
        KEY `validators_id` (`validators_id`),
        KEY `date_mod` (`date_mod`),
        KEY `end_date` (`end_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- Structure de la table 'glpi_plugin_consumables_fields'
-- --------------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_consumables_fields`;
CREATE TABLE `glpi_plugin_consumables_fields` (
	`id` int(11) NOT NULL auto_increment,
        `consumables_id` int(11) NOT NULL default '0',
        `order_ref` varchar(255) default NULL,
	PRIMARY KEY  (`id`),
        KEY `consumables_id` (`consumables_id`),
        UNIQUE KEY `unicity` (`consumables_id`, `order_ref`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;