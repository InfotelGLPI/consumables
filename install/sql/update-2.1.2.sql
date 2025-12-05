CREATE TABLE `glpi_plugin_consumables_helpdesks_tiles_consumablespagetiles` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) DEFAULT NULL,
    `description` text DEFAULT null,
    `illustration` varchar(255) DEFAULT NULL,
    `url` text DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
