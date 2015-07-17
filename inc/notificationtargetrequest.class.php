<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
  -------------------------------------------------------------------------
  Consumables plugin for GLPI
  Copyright (C) 2003-2011 by the consumables Development Team.

  https://forge.indepnet.net/projects/consumables
  -------------------------------------------------------------------------

  LICENSE

  This file is part of consumables.

  Consumables is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  Consumables is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Consumables. If not, see <http://www.gnu.org/licenses/>.
  --------------------------------------------------------------------------
 */


if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginConsumablesNotificationTargetRequest extends NotificationTarget {

   const CONSUMABLE_REQUEST  = "ConsumableRequest";
   const CONSUMABLE_RESPONSE = "ConsumableResponse";
   
   const VALIDATOR           = 30;
   const REQUESTER           = 31;

   function getEvents() {
      return array(self::CONSUMABLE_REQUEST    => __('Consumable request', 'consumables'), 
                   self::CONSUMABLE_RESPONSE   => __('Consumable validation', 'consumables'));
   }

   function getDatasForTemplate($event,$options=array()) {

      $this->datas['##consumable.entity##']      = Dropdown::getDropdownName('glpi_entities', $options['entities_id']);
      $this->datas['##lang.consumable.entity##'] = __('Entity');

      $this->datas['##consumable.entity##']      = Dropdown::getDropdownName('glpi_entities', $options['entities_id']);
      $this->datas['##lang.consumable.entity##'] = __('Entity');
      switch ($event) {
         case self::CONSUMABLE_REQUEST:
            $this->datas['##consumable.action##'] = __('Consumable request', 'consumables');
            break;
         case self::CONSUMABLE_RESPONSE:
            $this->datas['##consumable.action##'] = __('Consumable validation', 'consumables');
            break;
      }

      // Consumable request
      $this->datas['##lang.consumablerequest.consumable##']       = _n('Consumable', 'Consumables', 1);
      $this->datas['##lang.consumablerequest.consumabletype##']   = _n('Consumable type', 'Consumable types', 1);
      $this->datas['##lang.consumablerequest.request_date##']     = __('Request date');
      $this->datas['##lang.consumablerequest.requester##']        = __('Requester');
      $this->datas['##lang.consumablerequest.status##']           = __('Status');
      $this->datas['##lang.consumablerequest.number##']           = __('Number of used consumables');
      $this->datas['##lang.consumablerequest.validator##']        = __('Approver');
      $this->datas['##lang.consumablerequest.comment##']          = __('Comments');

      $consumable = $options['consumablerequest'];

      $this->datas['##consumablerequest.consumable##']     = Dropdown::getDropdownName(ConsumableItem::getTable(), $consumable['consumables_id']);
      $this->datas['##consumablerequest.consumabletype##'] = Dropdown::getDropdownName(ConsumableItemType::getTable(), $consumable['consumableitemtypes_id']);
      $this->datas['##consumablerequest.request_date##']   = Html::convDateTime($consumable['date_mod']);
      $this->datas['##consumablerequest.end_date##']       = Html::convDateTime($consumable['end_date']);
      $this->datas['##consumablerequest.requester##']      = Html::clean(getUserName($consumable['requesters_id']));
      $this->datas['##consumablerequest.validator##']      = Html::clean(getUserName($consumable['validators_id']));
      $this->datas['##consumablerequest.number##']         = $consumable['number'];
      $this->datas['##consumablerequest.status##']         = CommonITILValidation::getStatus($consumable['status']);
      if (isset($options['comment'])) {
         $this->datas['##consumablerequest.comment##'] = Html::clean($options['comment']);
      }
   }
   
   function getTags() {

      $tags = array('consumable.action'                => __('Type of event', 'consumables'),
                    'consumable.entity'                => __('Entity'),
                    'consumablerequest.consumable'     => _n('Consumable', 'Consumables', 1),
                    'consumablerequest.consumabletype' => _n('Consumable type', 'Consumable types', 1),
                    'consumablerequest.request_date'   => __('Request date'),
                    'consumablerequest.requester'      => __('Requester'),
                    'consumablerequest.status'         => __('Status'),
                    'consumablerequest.number'         => __('Number of used consumables'),
                    'consumablerequest.validator'      => __('Approver'),
                    'consumablerequest.comment'        => __('Comments'));

      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'   => $tag,
                                   'label' => $label,
                                   'lang'  => true,
                                   'value' => true));
      }
         
      asort($this->tag_descriptions);
   }
   
   /**
    * Get additionnals targets for Tickets
    */
   public function getAdditionalTargets($event = '') {
      $this->addTarget(self::VALIDATOR, __("Consumable approver", "consumables"));
      $this->addTarget(self::REQUESTER, __("Consumable requester", "consumables"));
   }

   public function getSpecificTargets($data, $options) {

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

?>