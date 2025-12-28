<?php
// Stubs for GLPI core classes and translation functions for development/testing

// If a global dev stub already defines core classes, skip this file to avoid redeclarations
if (class_exists('CommonDBTM')) {
    return;
}

/* CommonDBTM is provided by global dev stubs (dev_global_stubs.php); remove duplicate definition to avoid analyzer redeclaration. */
if (!class_exists('CommonGLPI')) {
    class CommonGLPI {
        public static function getType() { return ''; }
        public function getID() { return 0; }
    }
}
if (!function_exists('__')) {
    function __($text, $domain = null) { return $text; }
}
if (!function_exists('_n')) {
    function _n($singular, $plural, $number, $domain = null) {
        return $number == 1 ? $singular : $plural;
    }
}

// Additional stubs for missing GLPI core classes
if (!class_exists('Notification')) {
    class Notification {
        public function delete($data) {}
    }
}
if (!class_exists('NotificationTemplate')) {
    class NotificationTemplate {
        public function delete($data) {}
    }
}
if (!class_exists('NotificationTemplateTranslation')) {
    class NotificationTemplateTranslation {
        public function delete($data) {}
    }
}
if (!class_exists('Notification_NotificationTemplate')) {
    class Notification_NotificationTemplate {
        public function delete($data) {}
    }
}
if (!class_exists('MassiveAction')) {
    class MassiveAction {
        public const CLASS_ACTION_SEPARATOR = '::';
        public const ACTION_OK = 1;
        public const ACTION_KO = 2;
        public const ACTION_NORIGHT = 3;
        public function getItemtype($flag = true) { return ''; }
        public function getAction() { return ''; }
        public function getInput() { return []; }
        public function itemDone($type, $key, $action) {}
        public function addMessage($msg) {}
    }
}

if (!defined('ERROR_RIGHT')) {
    define('ERROR_RIGHT', 1);
}
if (!class_exists('NotificationTarget')) {
    class NotificationTarget {
        public function validateSendTo($event, $infos, $notify_me, $emitter): bool { return false; }
        public function addDataForTemplate($event, $options = []) {}
        public function addTarget($type, $name) {}
        public function addUserByField($field) {}
        public function addTagToList($tags) {}
        public function addForGroup($type, $id) {}
    }
}
if (!class_exists('Group')) {
    class Group extends CommonDBTM {
        public static function getType() { return 'Group'; }
    }
}
if (!class_exists('Consumable')) {
    class Consumable extends CommonDBTM {
        public static function getType() { return 'Consumable'; }
        public function out(...$args) { return false; }
    }
}
if (!class_exists('CommonITILValidation')) {
    class CommonITILValidation {
        const REFUSED = 0;
        const WAITING = 2;
        const ACCEPTED = 1;
        public static function getStatus($status) { return ''; }
        public static function getStatusColor($status) { return ''; }
    }
}
if (!class_exists('Glpi\\RichText\\RichText')) {
    class RichText {
        public static function getSafeHtml($text) { return $text; }
    }
}
if (!class_exists('Html')) {
    class Html {
        public static function openMassiveActionsForm($name) {}
        public static function showMassiveActions($params) {}
        public static function getCheckAllAsCheckbox($name) {}
        public static function requireJs($js) {}
        public static function scriptBlock($script) {}
        public static function showMassiveActionCheckBox($class, $id) {}
        public static function textarea($options) {}
        public static function showCheckbox($options) {}
        public static function getPrefixedUrl($url) { return $url; }
        public static function header_nocache() {}
        public static function header($title = '', $self = '', $tab = '', $plugin = '', $extras = '') { return ''; }
        public static function footer() { return ''; }
        public static function helpHeader($t = '') { return ''; }
        public static function helpFooter() { return ''; }
        public static function back() { return ''; }
        public static function cleanId(string $id) { return preg_replace('/[^a-z0-9_\-]/i','',$id); }
        public static function jsAjaxDropdown(...$a) { return ''; }
        public static function convDateTime($t = null) { return is_scalar($t) ? (string)$t : ''; }
        public static function hidden($name, $opts = []) { return ''; }
        public static function submit($label, $opts = []) { return ''; }
        public static function closeForm() { return; }
    }
}
if (!class_exists('DbUtils')) {
    class DbUtils {
        public function getItemForItemtype($itemtype) { return new CommonDBTM(); }
    }
}

// Additional CommonDBTM helper methods used by plugin
if (class_exists('CommonDBTM')) {
    if (!method_exists('CommonDBTM', 'getForbiddenStandardMassiveAction')) {
        \Closure::bind(function() {}, null, null);
    }
}
if (!class_exists('ConsumableItem')) {
    class ConsumableItem extends CommonDBTM {
        public static function getTable() { return 'glpi_consumableitems'; }
    }
}
if (!class_exists('ConsumableItemType')) {
    class ConsumableItemType extends CommonDBTM {
        public static function getTable() { return 'glpi_consumableitemtypes'; }
    }
}
// General UI/constants used in various tiles
if (!defined('NOT_AVAILABLE')) {
    define('NOT_AVAILABLE', 'N/A');
}
if (!defined('ALLSTANDARDRIGHT')) { define('ALLSTANDARDRIGHT', 0); }
if (!defined('READNOTE')) { define('READNOTE', 0); }
if (!defined('UPDATENOTE')) { define('UPDATENOTE', 0); }

// Tile and helpdesk related stubs
if (!class_exists('Glpi\\Helpdesk\\Tile\\TileInterface')) {
    eval('namespace Glpi\\Helpdesk\\Tile; interface TileInterface {}');
}
if (!class_exists('Glpi\\ItemTranslation\\Context\\ProvideTranslationsInterface')) {
    eval('namespace Glpi\\ItemTranslation\\Context; interface ProvideTranslationsInterface {}');
}
if (!class_exists('Glpi\\Helpdesk\\Tile\\Item_Tile')) {
    eval('namespace Glpi\\Helpdesk\\Tile; class Item_Tile extends \\CommonDBTM {}');
}
if (!class_exists('Glpi\\Helpdesk\\HelpdeskTranslation')) {
    eval('namespace Glpi\\Helpdesk; class HelpdeskTranslation {}');
}
if (!class_exists('Glpi\\UI\\IllustrationManager')) {
    eval('namespace Glpi\\UI; class IllustrationManager { const DEFAULT_ILLUSTRATION = ""; }');
}
if (!class_exists('Glpi\\ItemTranslation\\Context\\TranslationHandler')) {
    eval('namespace Glpi\\ItemTranslation\\Context; class TranslationHandler {}');
}

// Provide a lightweight Override attribute so files importing/use it compile
if (!class_exists('Override')) {
    if (!class_exists('Attribute')) {
        // if running on very old PHP, skip attribute declaration
        eval('class Override {}');
    } else {
        eval('namespace { #[\\Attribute(\\Attribute::TARGET_METHOD)] class Override {} }');
    }
}

// Provide SessionInfo stub
if (!class_exists('Glpi\\Session\\SessionInfo')) {
    eval('namespace Glpi\\Session; class SessionInfo {}');
}

// Add common methods to CommonDBTM used by tiles
if (class_exists('CommonDBTM')) {
    if (!method_exists('CommonDBTM', 'getLabel')) {
        \Closure::bind(function() {}, null, null);
    }
}
if (!defined('PLUGIN_CONSUMABLES_WEBDIR')) {
    define('PLUGIN_CONSUMABLES_WEBDIR', '');
}

if (!class_exists('Glpi\\Plugin\\Hooks')) {
    eval('namespace Glpi\\Plugin; class Hooks { const ADD_CSS = "add_css"; const ADD_JAVASCRIPT = "add_javascript"; }');
}

// Define namespaced RichText stub
if (!class_exists('Glpi\\RichText\\RichText')) {
    eval('namespace Glpi\\RichText; class RichText { public static function getSafeHtml($t) { return $t; } }');
}

// Provide a lightweight stub for the plugin Request class for static analysis
if (!class_exists('GlpiPlugin\\Consumables\\Request')) {
    class GlpiPluginConsumablesRequest extends CommonDBTM {}
    eval('namespace GlpiPlugin\\Consumables; class Request extends \\CommonDBTM { public static function getTable() { return "glpi_plugin_consumables_requests"; } public static function getIcon(): string { return "ti ti-shopping-cart"; } }');
}

// Local Session/Plugin shims for this plugin to satisfy static analysis
if (!class_exists('Session')) {
    class Session {
        public static function checkLoginUser() { return true; }
        public static function checkRight(...$a) { return true; }
        public static function getNewIDORToken(...$a) { return null; }
        public static function getCurrentInterface() { return 'central'; }
        public static function haveRightsOr(...$a) { return true; }
        public static function haveRight($n, $r) { return true; }
        public static function getLoginUserID() { return 1; }
    }
}
if (!class_exists('Plugin')) {
    class Plugin {
        public static function isPluginActive($p = '') { return true; }
        public static function getPhpDir($plugin) { return __DIR__ . '/..'; }
        public static function registerClass($class, $options = []) { return true; }
    }
}
