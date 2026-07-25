-- --------------------------------------------------------
-- 2.1.3 : entity scoping for consumable requests
-- Add entities_id to the requests table and backfill it from the linked
-- consumable item so the validation list can be filtered per entity.
-- --------------------------------------------------------
ALTER TABLE `glpi_plugin_consumables_requests`
  ADD COLUMN `entities_id` int unsigned NOT NULL DEFAULT '0' AFTER `id`,
  ADD KEY `entities_id` (`entities_id`);

UPDATE `glpi_plugin_consumables_requests` `r`
  INNER JOIN `glpi_consumableitems` `c` ON `c`.`id` = `r`.`consumableitems_id`
  SET `r`.`entities_id` = `c`.`entities_id`;
