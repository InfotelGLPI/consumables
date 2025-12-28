<?php

declare(strict_types=1);

/*
 * Wizard for consumables plugin
 */

namespace GlpiPlugin\Consumables;

use CommonDBTM;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class Wizard extends CommonDBTM
{
    /**
     * Get the type name for the wizard
     * @param int $nb
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('Consumables wizard', 'consumables');
    }

    /**
     * Show config menu
     * @return bool|null
     */
    public function showMenu(): ?bool
    {
        $request = new Request();
        if (method_exists($this, 'canView') && !$this->canView() && !$request->canRequest()) {
            return false;
        }

        echo "<h3><div class='alert alert-secondary' role='alert'>";
        echo "<i class='ti ti-shopping-cart-plus'></i>&nbsp;";
        echo __('Consumable request', 'consumables');
        echo "</div></h3>";

        echo "<div class='row consumables_wizard_row' style='margin: 0 auto;'>";
        if ($request->canRequest()) {
            echo "<div class='center col-md-5 consumables_wizard_rank'>";
            echo "<a class='consumables_menu_a' href='" . PLUGIN_CONSUMABLES_WEBDIR . "/front/wizard.form.php?action=consumablerequest'>";
            echo "<i class='thumbnail ti ti-shopping-cart-plus' style='font-size: 4.5em;'></i>";
            echo "<br><br>" . __('Consumable request', 'consumables') . "<br></a>";
            echo "</div>";
        }
        echo "<div class='center col-md-6 consumables_wizard_rank'>";
        echo "<a class='consumables_menu_a' href='" . PLUGIN_CONSUMABLES_WEBDIR . "/front/wizard.form.php?action=consumablevalidation'>";
        echo "<i class='thumbnail ti ti-clipboard-check' style='font-size: 4.5em;'></i>";
        echo "<br><br>" . __('Consumable validation', 'consumables') . "</a>";
        echo "</div>";
        echo "</div>";

        return true;
    }

    /**
     * Show wizard form of the current step
     * @param string $step
     * @return void
     */
    public function showWizard(string $step): void
    {
        echo "<div class='consumables_wizard'>";
        switch ($step) {
            case 'consumablerequest':
                $consumablerequest = new Request();
                if (method_exists($consumablerequest, 'showConsumableRequest')) {
                    $consumablerequest->showConsumableRequest();
                }
                break;
            case 'consumablevalidation':
                $consumablevalidation = new Validation();
                if (method_exists($consumablevalidation, 'showConsumableValidation')) {
                    $consumablevalidation->showConsumableValidation();
                }
                break;
        }
        echo "</div>";
    }
}
