<?php
/*declare(strict_types=1);
if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }
/**
 * Handle plugin database schema and data migrations
 * REQUIRED for GLPI 11+
 *
 * @return array
 */
function plugin_version_modifications() {
    return [
        // Example: [ 'version' => '1.0.0', 'query' => 'CREATE TABLE ...' ]
        // Add migration steps here as needed for future versions
    ];
}
// Fallback for Session during static analysis
if (!class_exists('Session')) {
    class Session {
        public static function getLoginUserID() { return 0; }
        public static function haveRight($item, $right) { return true; }
    }
}
// Fallbacks for static analysis and static analyzers (core classes, global namespace)
if (!class_exists('Plugin')) {
    class Plugin {
        public static function getPhpDir($plugin) { return ''; }
        public static function registerClass($class, $options = []) { return true; }
    }
}
if (!class_exists('TilesManager')) {
    class TilesManager {
        public static function getInstance() { return new self(); }
        public function registerPluginTileType($tile) { return true; }
    }
}

use GlpiPlugin\Consumables\Field;
// use GlpiPlugin\Consumables\Helpdesk\Tile\ConsumablesPageTile;
use GlpiPlugin\Consumables\Menu;
use GlpiPlugin\Consumables\Option;
use GlpiPlugin\Consumables\Profile;
use GlpiPlugin\Consumables\Request;
use GlpiPlugin\Consumables\Servicecatalog;
use GlpiPlugin\Consumables\Validation;
use GlpiPlugin\Servicecatalog\Main;

define('PLUGIN_CONSUMABLES_VERSION', '2.1.2');

// Get the name and the version of the plugin - Needed


/**
 * Retorna nome e versão do plugin
 *
 * @return array
 */
function plugin_version_consumables(): array
{
    return [
        'name'         => 'Consumable request',
        'version'      => PLUGIN_CONSUMABLES_VERSION,
        'author'       => 'Infotel, Xavier CAILLAUD',
        'license'      => 'GPLv2+',
        'homepage'     => 'https://github.com/InfotelGLPI/consumables',
        'requirements' => [
            'glpi' => [
                'min' => '11.0',
                'max' => '12.0',
                'dev' => false,
            ],
        ],
    ];
}

// Init the hooks of the plugins - Needed
function plugin_init_consumables(): void
{
    if (!defined("PLUGIN_CONSUMABLES_DIR")) {
        define("PLUGIN_CONSUMABLES_DIR", Plugin::getPhpDir("consumables"));
        // Fix: $root is undefined. Use GLPI_ROOT or set to empty string if not needed.
        define("PLUGIN_CONSUMABLES_WEBDIR", defined('GLPI_ROOT') ? GLPI_ROOT : '');
    }

    global $PLUGIN_HOOKS, $CFG_GLPI;

    // $tiles_manager = TilesManager::getInstance();
    // $tiles_manager->registerPluginTileType(new ConsumablesPageTile());

    $CFG_GLPI['glpitablesitemtype'][Validation::class] = 'glpi_plugin_consumables_requests';
    $CFG_GLPI['glpitablesitemtype'][Option::class] = 'glpi_plugin_consumables_options';
    $PLUGIN_HOOKS['csrf_compliant']['consumables'] = true;
    $PLUGIN_HOOKS['change_profile']['consumables'] = [Profile::class, 'initProfile'];
    // Avoid referencing Glpi\Plugin\Hooks constants during init; use literal hook names
    $PLUGIN_HOOKS['add_css']['consumables'] = 'public/css/consumables.css';
    $PLUGIN_HOOKS['add_javascript']['consumables'] = 'public/js/consumables.js';

    Plugin::registerClass(Profile::class, ['addtabon' => 'Profile']);
    Plugin::registerClass('GlpiPlugin\\Consumables\\Request', ['addtabon' => 'User', 'notificationtemplates_types' => true]);
    Plugin::registerClass('GlpiPlugin\\Consumables\\Request', ['addtabon' => 'Group', 'notificationtemplates_types' => true]);
    Plugin::registerClass('GlpiPlugin\\Consumables\\Request', ['addtabon' => 'ConsumableItem']);
    Plugin::registerClass(Option::class, ['addtabon' => 'ConsumableItem']);

    if (Session::getLoginUserID()) {
        $PLUGIN_HOOKS['post_item_form']['consumables'] = [Field::class, 'addFieldOrderReference'];

        $PLUGIN_HOOKS['item_add']['consumables'] = ['ConsumableItem' => [Field::class, 'postAddConsumable']];
        $PLUGIN_HOOKS['pre_item_update']['consumables'] = ['ConsumableItem' => [Field::class, 'preUpdateConsumable']];

        if (Session::haveRight("plugin_consumables", UPDATE)) {
            $PLUGIN_HOOKS['use_massive_action']['consumables'] = 1;
        }

        $PLUGIN_HOOKS['servicecatalog']['consumables'] = [Servicecatalog::class];

        if (Session::haveRight("plugin_consumables", READ)) {
            $PLUGIN_HOOKS['menu_toadd']['consumables'] = ['management' => Menu::class];
        }
        if (
            Session::haveRight("plugin_consumables", READ)
            || Session::haveRight("plugin_consumables_request", 1)
            && !class_exists(Main::class)
        ) {
            $PLUGIN_HOOKS['helpdesk_menu_entry']['consumables'] = PLUGIN_CONSUMABLES_WEBDIR . '/front/wizard.php';
            // Avoid invoking plugin classes at init time (may not be autoloaded yet)
            $PLUGIN_HOOKS['helpdesk_menu_entry_icon']['consumables'] = 'ti ti-shopping-cart';
        }

        // Post item purge
        $PLUGIN_HOOKS['item_purge']['consumables'] = ['ConsumableItem' => 'plugin_item_purge_consumables'];
    }
}
