<?php

$glpiRoot = dirname(__DIR__, 3);
$loader   = require $glpiRoot . '/vendor/autoload.php';

if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', $glpiRoot);
}

$loader->addPsr4('GlpiPlugin\\Consumables\\', dirname(__DIR__) . '/src/');
$loader->addPsr4('GlpiPlugin\\Consumables\\Tests\\', dirname(__DIR__) . '/tests/');
