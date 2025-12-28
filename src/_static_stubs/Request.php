<?php
namespace GlpiPlugin\Consumables;

if (!class_exists('GlpiPlugin\\Consumables\\Request')) {
    class Request extends \CommonDBTM {
        public $fields = [];
        public static function getTable(): string { return 'glpi_plugin_consumables_requests'; }
        public static function getIcon(): string { return 'ti ti-shopping-cart'; }
        public function getFromDB($id = 0) { return null; }
        public function update(array $data = [], ...$args): bool { return true; }
        public function getLink($opts = []) { return ''; }
        public function canRequest(): bool { return false; }
    }
}
