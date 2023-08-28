ALTER TABLE `glpi_plugin_consumables_requests` CHANGE `consumables_id` `consumableitems_id` int unsigned NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_consumables_fields` CHANGE `consumables_id` `consumableitems_id` int unsigned NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_consumables_options` CHANGE `consumables_id` `consumableitems_id` int unsigned NOT NULL DEFAULT '0';