<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 consumables plugin for GLPI
 Copyright (C) 2009-2016 by the consumables Development Team.

 https://github.com/InfotelGLPI/consumables
 -------------------------------------------------------------------------

 LICENSE

 This file is part of consumables.

 consumables is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 consumables is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with consumables. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget

/**
 * Class PluginConsumablesNotificationTargetRequest
 */
class PluginConsumablesNotificationTargetRequest extends NotificationTarget {

   const CONSUMABLE_REQUEST  = "ConsumableRequest";
   const CONSUMABLE_RESPONSE = "ConsumableResponse";
   const VALIDATOR           = 30;
   const REQUESTER           = 31;

   /**
    * @return array
    */
   function getEvents() {
      return [self::CONSUMABLE_REQUEST  => __('Consumable request', 'consumables'),
              self::CONSUMABLE_RESPONSE => __('Consumable validation', 'consumables')];
   }

   /**
    * @param       $event
    * @param array $options
    */
   function addDataForTemplate($event, $options = []) {

      // Set labels
      $this->data['##lang.consumable.entity##'] = __('Entity');
      $this->data['##lang.consumable.id##']     = __('Consumable ID', 'consumables');
      switch ($event) {
         case self::CONSUMABLE_REQUEST:
            $this->data['##consumable.action##'] = __('Consumable request', 'consumables');
            break;
         case self::CONSUMABLE_RESPONSE:
            $this->data['##consumable.action##'] = __('Consumable validation', 'consumables');
            break;
      }
      $this->data['##lang.consumablerequest.consumable##']     = _n('Consumable', 'Consumables', 1);
      $this->data['##lang.consumablerequest.consumabletype##'] = _n('Consumable type', 'Consumable types', 1);
      $this->data['##lang.consumablerequest.requestdate##']    = __('Request date');
      $this->data['##lang.consumablerequest.requester##']      = __('Requester');
      $this->data['##lang.consumablerequest.status##']         = __('Status');
      $this->data['##lang.consumablerequest.number##']         = __('Number of used consumables');
      $this->data['##lang.consumablerequest.validator##']      = __('Approver');
      $this->data['##lang.consumablerequest.comment##']        = __('Comments');

      $this->data['##consumable.entity##'] = Dropdown::getDropdownName('glpi_entities', $options['entities_id']);
      //Set values
      foreach ($options['consumables'] as $id => $item) {
         $tmp                                         = [];
         $tmp['##consumable.id##']                    = $item['consumables_id'];
         $tmp['##consumablerequest.consumable##']     = Dropdown::getDropdownName(ConsumableItem::getTable(), $item['consumables_id']);
         $tmp['##consumablerequest.consumabletype##'] = Dropdown::getDropdownName(ConsumableItemType::getTable(), $item['consumableitemtypes_id']);
         $tmp['##consumablerequest.requestdate##']    = Html::convDateTime($item['datemod']);
         if (isset($item['end_date'])) {
            $tmp['##consumablerequest.enddate##'] = Html::convDateTime($item['enddate']);
         }
         $dbu = new DbUtils();
         $tmp['##consumablerequest.requester##'] = Html::clean($dbu->getUserName($item['requesters_id']));
         $tmp['##consumablerequest.validator##'] = Html::clean($dbu->getUserName($item['validators_id']));
         $tmp['##consumablerequest.number##']    = $item['number'];
         $tmp['##consumablerequest.status##']    = CommonITILValidation::getStatus($item['status']);
         $this->data['consumabledata'][]         = $tmp;
      }
      if (isset($options['comment'])) {
         $this->data['##consumablerequest.comment##'] = Html::clean($options['comment']);
      }
   }

   /**
    *
    */
   function getTags() {

      $tags = ['consumable.id'                    => __('Consumable ID', 'consumables'),
               'consumable.action'                => __('Type of event', 'consumables'),
               'consumable.entity'                => __('Entity'),
               'consumablerequest.consumable'     => _n('Consumable', 'Consumables', 1),
               'consumablerequest.consumabletype' => _n('Consumable type', 'Consumable types', 1),
               'consumablerequest.requestdate'    => __('Request date'),
               'consumablerequest.enddate'        => __('End date'),
               'consumablerequest.requester'      => __('Requester'),
               'consumablerequest.status'         => __('Status'),
               'consumablerequest.number'         => __('Number of used consumables'),
               'consumablerequest.validator'      => __('Approver'),
               'consumablerequest.comment'        => __('Comments')];

      foreach ($tags as $tag => $label) {
         $this->addTagToList(['tag'   => $tag,
                              'label' => $label,
                              'lang'  => true,
                              'value' => true]);
      }

      $this->addTagToList(['tag'     => 'consumabledata',
                           'label'   => __('Display each consumable', 'consumables'),
                           'lang'    => true,
                           'foreach' => true,
                           'value'   => true]);

      asort($this->tag_descriptions);
   }

   /**
    * Get additionnals targets for Tickets
    *
    * @param string $event
    */
   public function addAdditionalTargets($event = '') {

      $this->addTarget(self::VALIDATOR, __("Consumable approver", "consumables"));
      $this->addTarget(self::REQUESTER, __("Consumable requester", "consumables"));
   }

   /**
    * @param $data
    * @param $options
    */
   public function addSpecificTargets($data, $options) {

      switch ($data['items_id']) {
         case self::VALIDATOR:
            $this->addUserByField("validators_id");
            break;
         case self::REQUESTER:
            $this->addUserByField("requesters_id");
            break;
      }
   }

}
