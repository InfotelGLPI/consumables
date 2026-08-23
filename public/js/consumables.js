/**
 * -------------------------------------------------------------------------
 * consumables plugin for GLPI
 * Copyright (C) 2015-2026 by the consumables Development Team.
 *
 * https://github.com/InfotelGLPI/consumables
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of consumables.
 *
 * consumables is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * consumables is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with consumables. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

function consumables_initJs(root_doc, consumableTypeID, consumableID) {
   this.usedConsumables = {};
   this.root_doc = root_doc;
   this.consumableTypeID = consumableTypeID;
   this.consumableID = consumableID;
}

/**
 * Escape a server-supplied value before it is concatenated into an HTML string.
 * Labels (consumable/type names) and values are not trusted: names are stored raw
 * by GLPI and a requester can reflect an arbitrary "number" back, so both must be
 * neutralized to prevent stored/reflected DOM XSS.
 */
function consumables_escapeHtml(value) {
   return String(value === undefined || value === null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
}

/**
 * Bound to the consumable-type dropdown on_change; reloads the consumable list.
 */
function loadAvailableConsumables(object) {
   this.consumableTypeID = object.value;
   consumables_reloadAvailableConsumables();
}

/**
 * Bound to the consumable dropdown on_change; reloads the number field and pictures.
 */
function loadAvailableConsumablesNumber(object) {
   this.consumableID = object.value;
   consumables_reloadAvailableConsumablesNumber();
   consumables_seeConsumablesInfos();
}

/**
 * Bootstrap the wizard once the DOM is ready.
 * The GLPI web dir is read from the [data-root-doc] attribute placed on the
 * request form, the user/group search form and the validation list.
 */
$(function () {
   var $root = $('[data-root-doc]').first();
   if ($root.length) {
      consumables_initJs($root.data('rootDoc'));
   }
});


/**
 * consumables_add_custom_values : add text input
 */
function consumables_addToCart(action, toobserve, toupdate) {

   var object = this;

   var formInput = getFormData(toobserve);

   $.ajax({
      url: this.root_doc + '/ajax/request.php',
      type: "POST",
      dataType: "json",
      data: 'action=' + action + '&' + formInput,
      success: function (data) {
         if (data.success) {
            var item_bloc = $('#' + toupdate);
            var result = "<tr id='consumables_cartRow" + data.rowId + "'>\n";
            // Insert row in cart
            $.each(data.fields, function (index, row) {
               if (row.hidden == undefined || !row.hidden) { // IS hidden row ?
                  result += "<td>" + consumables_escapeHtml(row.label) + "<input type='hidden' id='" + index +
                     "' name='consumables_cart[" + data.rowId + "][" + index + "]' value='" + consumables_escapeHtml(row.value) + "'></td>\n";

               } else {
                  result += "<input type='hidden' id='" + index + "' " +
                     "name='consumables_cart[" + data.rowId + "][" + index + "]' value='" + consumables_escapeHtml(row.value) + "'>";
               }
            });

            // Push used consumables
            var number = object.usedConsumables[data.fields.consumableitems_id.value];
            if (number === undefined) {
               object.usedConsumables[data.fields.consumableitems_id.value] = parseInt(data.fields.number.value);
            } else {
               object.usedConsumables[data.fields.consumableitems_id.value] = object.usedConsumables[data.fields.consumableitems_id.value] + parseInt(data.fields.number.value);
            }

            result += "<td><a href='#' onclick=\"consumables_removeCart('consumables_cartRow" + data.rowId + "')\"><i class='ti ti-circle-x fa-2x' style='color:darkred'></i></a>" +
               "</td></tr>";

            item_bloc.append(result);
            item_bloc.css({"display": 'table'});

            // Reload consumable list
            consumables_reloadAvailableConsumablesNumber();
         } else {
            consumables_showDialog(data.message, false);
         }
      }
   });
}

function consumables_addConsumables(action, toobserve) {

   var formInput = getFormData(toobserve);

   $.ajax({
      type: "POST",
      dataType: "json",
      url: this.root_doc + '/ajax/request.php',
      data: 'action=' + action + '&' + formInput,
      success: function (data) {
         consumables_showDialog(data.message, data.success);
      }
   });
}

function consumables_showDialog(message, reload) {

   glpi_html_dialog({
      title: __("Add to cart", "consumables"),
      body: message,
      id: 'add_badges',
      buttons: [{
         label: __("Close"),
         click: function(event) {
            window.location.reload();
         }
      }],
   })
}

function consumables_searchConsumables(action, toobserve, toupdate,type) {

   var formInput = getFormData(toobserve);
   var item_bloc = $('#' + toupdate);

   // Loading
   item_bloc.html('<div style="width:100%;text-align:center"><img src="' + this.root_doc + '/pics/large-loading.gif"></div>');

   $.ajax({
      type: "POST",
      dataType: "json",
      url: this.root_doc + '/ajax/request.php',
      data: 'action=' + action + '&'+'type='+type+'&' + formInput,
      success: function (data) {
         var result = data.message;

         item_bloc.html(result);

         var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
         while (scripts = scriptsFinder.exec(result)) {
            eval(scripts[1]);
         }
      }
   });

}

function consumables_reloadAvailableConsumables() {

   var type = this.consumableTypeID;

   $.ajax({
      type: "POST",
      url: this.root_doc + '/ajax/request.php',
      data: {
         'action': 'reloadAvailableConsumables',
         'type': type
      },
      success: function (result) {
         var item_bloc = $('#loadAvailableConsumables');
         item_bloc.html(result);

         var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
         while (scripts = scriptsFinder.exec(result)) {
            eval(scripts[1]);
         }
      }
   });
}

function consumables_reloadAvailableConsumablesNumber() {

   $.ajax({
      type: "POST",
      url: this.root_doc + '/ajax/request.php',
      data: {
         'action': 'reloadAvailableConsumablesNumber',
         'used': JSON.stringify(this.usedConsumables),
         'consumableitems_id': this.consumableID
      },
      success: function (result) {
         var item_bloc = $('#loadAvailableConsumablesNumber');
         item_bloc.html(result);

         var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
         while (scripts = scriptsFinder.exec(result)) {
            eval(scripts[1]);
         }
      }
   });
}

function consumables_seeConsumablesInfos() {

   $.ajax({
      type: "POST",
      url: this.root_doc + '/ajax/request.php',
      data: {
         'action': 'seeConsumablesInfos',
         'used': JSON.stringify(this.usedConsumables),
         'consumableitems_id': this.consumableID
      },
      success: function (result) {
         var item_bloc = $('#seeConsumablesInfos');
         item_bloc.html(result);

         var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
         while (scripts = scriptsFinder.exec(result)) {
            eval(scripts[1]);
         }
      }
   });
}

/**
 * consumables_removeCart : delete text input
 *
 * @param field_id
 */
function consumables_removeCart(field_id) {
   var value = $("tr[id=" + field_id + "] input[id=number]").val();
   var consumableitems_id = $("tr[id=" + field_id + "] input[id=consumableitems_id]").val();

   // Remove element from used consumables variable
   this.usedConsumables[consumableitems_id] = this.usedConsumables[consumableitems_id] - parseInt(value);
   if (this.usedConsumables[consumableitems_id] < 0) {
      this.usedConsumables[consumableitems_id] = 0;
   }

   // Reload consumable list
   consumables_reloadAvailableConsumablesNumber();

   // Remove cart row
   $('#' + field_id).remove();
}

function consumables_cancel(url) {
   window.location.href = url;
}


/**
 *  Get the form values and construct data url
 *
 * @param form
 */
function getFormData(form) {

   if (typeof(form) !== 'object') {
      var form = $('#' + form);
   }

   return encodeParameters(form[0]);
}

/**
 * Encode form parameters for URL
 *
 * @param elements
 */
function encodeParameters(elements) {
   var kvpairs = [];

   $.each(elements, function (index, e) {
      if (e.name != '') {
         switch (e.type) {
            case 'radio':
            case 'checkbox':
               if (e.checked) {
                  kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
               }
               break;
            case 'select-multiple':
               var name = e.name.replace("[", "").replace("]", "");
               $.each(e.selectedOptions, function (index, option) {
                  kvpairs.push(encodeURIComponent(name + '[' + option.index + ']') + '=' + encodeURIComponent(option.value));
               });
               break;
            default:
               kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
               break;
         }
      }
   });

   return kvpairs.join("&");
}
