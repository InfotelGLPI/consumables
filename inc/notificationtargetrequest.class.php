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
class PluginConsumablesNotificationTargetRequest extends NotificationTarget
{

   const CONSUMABLE_REQUEST = "ConsumableRequest";
   const CONSUMABLE_RESPONSE = "ConsumableResponse";
   const VALIDATOR = 30;
   const REQUESTER = 31;

   /**
    * @return array
    */
   function getEvents()
   {
      return array(self::CONSUMABLE_REQUEST => __('Consumable request', 'consumables'),
         self::CONSUMABLE_RESPONSE => __('Consumable validation', 'consumables'));
   }

   /**
    * @param $event
    * @param array $options
    */
   function getDatasForTemplate($event, $options = array())
   {

      // Set labels
      $this->datas['##lang.consumable.entity##'] = __('Entity');
      $this->datas['##lang.consumable.id##'] = __('Consumable ID', 'consumables');
      switch ($event) {
         case self::CONSUMABLE_REQUEST:
            $this->datas['##consumable.action##'] = __('Consumable request', 'consumables');
            break;
         case self::CONSUMABLE_RESPONSE:
            $this->datas['##consumable.action##'] = __('Consumable validation', 'consumables');
            break;
      }
      $this->datas['##lang.consumablerequest.consumable##'] = _n('Consumable', 'Consumables', 1);
      $this->datas['##lang.consumablerequest.consumabletype##'] = _n('Consumable type', 'Consumable types', 1);
      $this->datas['##lang.consumablerequest.request_date##'] = __('Request date');
      $this->datas['##lang.consumablerequest.requester##'] = __('Requester');
      $this->datas['##lang.consumablerequest.status##'] = __('Status');
      $this->datas['##lang.consumablerequest.number##'] = __('Number of used consumables');
      $this->datas['##lang.consumablerequest.validator##'] = __('Approver');
      $this->datas['##lang.consumablerequest.comment##'] = __('Comments');

      $this->datas['##consumable.entity##'] = Dropdown::getDropdownName('glpi_entities', $options['entities_id']);
      //Set values
      foreach ($options['consumables'] as $id => $item) {
         $tmp = array();
         $tmp['##consumable.id##'] = $item['consumables_id'];
         $tmp['##consumablerequest.consumable##'] = Dropdown::getDropdownName(ConsumableItem::getTable(), $item['consumables_id']);
         $tmp['##consumablerequest.consumabletype##'] = Dropdown::getDropdownName(ConsumableItemType::getTable(), $item['consumableitemtypes_id']);
         $tmp['##consumablerequest.request_date##'] = Html::convDateTime($item['date_mod']);
         if (isset($item['end_date'])) {
            $tmp['##consumablerequest.end_date##'] = Html::convDateTime($item['end_date']);
         }
         $tmp['##consumablerequest.requester##'] = Html::clean(getUserName($item['requesters_id']));
         $tmp['##consumablerequest.validator##'] = Html::clean(getUserName($item['validators_id']));
         $tmp['##consumablerequest.number##'] = $item['number'];
         $tmp['##consumablerequest.status##'] = CommonITILValidation::getStatus($item['status']);
         $this->datas['consumabledatas'][] = $tmp;
      }
      if (isset($options['comment'])) {
         $this->datas['##consumablerequest.comment##'] = Html::clean($options['comment']);
      }
   }

   /**
    *
    */
   function getTags()
   {

      $tags = array('consumable.id' => __('Consumable ID', 'consumables'),
         'consumable.action' => __('Type of event', 'consumables'),
         'consumable.entity' => __('Entity'),
         'consumablerequest.consumable' => _n('Consumable', 'Consumables', 1),
         'consumablerequest.consumabletype' => _n('Consumable type', 'Consumable types', 1),
         'consumablerequest.request_date' => __('Request date'),
         'consumablerequest.requester' => __('Requester'),
         'consumablerequest.status' => __('Status'),
         'consumablerequest.number' => __('Number of used consumables'),
         'consumablerequest.validator' => __('Approver'),
         'consumablerequest.comment' => __('Comments'));

      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag' => $tag,
            'label' => $label,
            'lang' => true,
            'value' => true));
      }

      $this->addTagToList(array('tag' => 'consumabledatas',
         'label' => __('Display each consumable', 'consumables'),
         'lang' => true,
         'foreach' => true,
         'value' => true));

      asort($this->tag_descriptions);
   }

   /**
    * Get additionnals targets for Tickets
    * @param string $event
    */
   public function getAdditionalTargets($event = '')
   {
      $this->addTarget(self::VALIDATOR, __("Consumable approver", "consumables"));
      $this->addTarget(self::REQUESTER, __("Consumable requester", "consumables"));
   }

   /**
    * @param $data
    * @param $options
    */
   public function getSpecificTargets($data, $options)
   {

      switch ($data['items_id']) {
         case self::VALIDATOR:
            $this->getUserByField("validators_id");
            break;
         case self::REQUESTER:
            $this->getUserByField("requesters_id");
            break;
      }
   }

}