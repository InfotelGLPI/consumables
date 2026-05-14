<?php

namespace GlpiPlugin\Consumables\Tests\Integration;

use CommonITILValidation;
use ConsumableItem;
use ConsumableItemType;
use Glpi\Tests\DbTestCase;
use GlpiPlugin\Consumables\Request;
use GlpiPlugin\Consumables\Validation;
use Session;

class ValidationTest extends DbTestCase
{
    private int $requestId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->login();

        $type = new ConsumableItemType();
        $typeId = (int) $type->add(['name' => 'Val Type', 'entities_id' => 0]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $consumableItem = new ConsumableItem();
        $itemId = (int) $consumableItem->add([
            'name'                   => 'Val Item',
            'entities_id'            => 0,
            'consumableitemtypes_id' => $typeId,
        ]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $request = new Request();
        $this->requestId = (int) $request->add([
            'consumableitems_id'     => $itemId,
            'consumableitemtypes_id' => $typeId,
            'number'                 => 1,
            'requesters_id'          => Session::getLoginUserID(),
            'validators_id'          => 0,
            'status'                 => CommonITILValidation::WAITING,
            'give_items_id'          => Session::getLoginUserID(),
            'give_itemtype'          => 'User',
            'date_mod'               => date('Y-m-d H:i:s'),
        ]);
        $_SESSION['MESSAGE_AFTER_REDIRECT'] = [];

        $this->assertGreaterThan(0, $this->requestId);
    }

    public function testValidationConsumableReturnsDeniedWithoutRight(): void
    {
        $_SESSION['glpiactiveprofile']['plugin_consumables_validation'] = 0;

        $validation = new Validation();
        $result     = $validation->validationConsumable(['id' => $this->requestId]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame('Access denied', $result['error']);
    }

    public function testValidationConsumableReturnsErrorForNonExistentId(): void
    {
        $_SESSION['glpiactiveprofile']['plugin_consumables_validation'] = 1;

        $validation = new Validation();
        $result     = $validation->validationConsumable(['id' => 999999]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
    }

    public function testValidationConsumableAcceptsRequest(): void
    {
        $_SESSION['glpiactiveprofile']['plugin_consumables_validation'] = 1;

        $validation = new Validation();
        $result     = $validation->validationConsumable(
            ['id' => $this->requestId],
            CommonITILValidation::ACCEPTED
        );

        $this->assertSame(CommonITILValidation::ACCEPTED, $result);

        $request = new Request();
        $this->assertTrue($request->getFromDB($this->requestId));
        $this->assertSame(CommonITILValidation::ACCEPTED, (int) $request->fields['status']);
        $this->assertSame((int) Session::getLoginUserID(), (int) $request->fields['validators_id']);
    }

    public function testValidationConsumableRefusesRequest(): void
    {
        $_SESSION['glpiactiveprofile']['plugin_consumables_validation'] = 1;

        $validation = new Validation();
        $result     = $validation->validationConsumable(
            ['id' => $this->requestId],
            CommonITILValidation::REFUSED
        );

        $this->assertSame(CommonITILValidation::REFUSED, $result);

        $request = new Request();
        $this->assertTrue($request->getFromDB($this->requestId));
        $this->assertSame(CommonITILValidation::REFUSED, (int) $request->fields['status']);
        $this->assertSame((int) Session::getLoginUserID(), (int) $request->fields['validators_id']);
    }

    public function testValidationConsumableDefaultStateIsWaiting(): void
    {
        $_SESSION['glpiactiveprofile']['plugin_consumables_validation'] = 1;

        $validation = new Validation();
        $result     = $validation->validationConsumable(['id' => $this->requestId]);

        $this->assertSame(CommonITILValidation::WAITING, $result);

        $request = new Request();
        $this->assertTrue($request->getFromDB($this->requestId));
        $this->assertSame(CommonITILValidation::WAITING, (int) $request->fields['status']);
    }
}
