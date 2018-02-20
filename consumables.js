function consumables_initJs(root_doc, consumableTypeID, consumableID) {
    this.usedConsumables = {};
    this.root_doc = root_doc;
    this.consumableTypeID = consumableTypeID;
    this.consumableID = consumableID;
}


/**
 * consumables_add_custom_values : add text input
 */
function consumables_addToCart(action, toobserve, toupdate) {

    var object = this;

    var formInput = getFormData(toobserve);

    $.ajax({
         url: this.root_doc + '/plugins/consumables/ajax/request.php',
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
                      result += "<td>" + row.label.replace(/\\["|']/g, '"') + "<input type='hidden' id='" + index +
                          "' name='consumables_cart[" + data.rowId + "][" + index + "]' value='" + row.value + "'></td>\n";

                  } else {
                      result += "<input type='hidden' id='" + index + "' " +
                          "name='consumables_cart[" + data.rowId + "][" + index + "]' value='" + row.value + "'>";
                  }
                });

                // Push used consumables
                var number = object.usedConsumables[data.fields.consumables_id.value];
                if(number === undefined){
                    object.usedConsumables[data.fields.consumables_id.value] = parseInt(data.fields.number.value);
                } else {
                    object.usedConsumables[data.fields.consumables_id.value] = object.usedConsumables[data.fields.consumables_id.value] + parseInt(data.fields.number.value);
                }

                result += "<td><img style=\"cursor:pointer\" " +
                    "src=\"" + object.root_doc + "/plugins/consumables/pics/delete.png\" " +
                    "onclick=\"consumables_removeCart('consumables_cartRow" + data.rowId + "')\"></td></tr>";

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
         url: this.root_doc + '/plugins/consumables/ajax/request.php',
         data: 'action=' + action + '&' + formInput,
         success: function (data) {
            consumables_showDialog(data.message, data.success);
         }
      });
}

function consumables_showDialog(message, reload) {

    $("#dialog-confirm").html(message);
    $("#dialog-confirm").dialog({
         resizable: false,
         height: 140,
         modal: true,
         buttons: {
            OK: function () {
                $(this).dialog("close");
               if (reload) {
                   window.location.reload();
               }
            }
         }
      });
}

function consumables_searchConsumables(action, toobserve, toupdate) {

    var formInput = getFormData(toobserve);
    var item_bloc = $('#' + toupdate);

    // Loading
    item_bloc.html('<div style="width:100%;text-align:center"><img src="' + this.root_doc + '/plugins/consumables/pics/large-loading.gif"></div>');

    $.ajax({
         type: "POST",
         dataType: "json",
         url: this.root_doc + '/plugins/consumables/ajax/request.php',
         data: 'action=' + action + '&' + formInput,
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
         url: this.root_doc + '/plugins/consumables/ajax/request.php',
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
         url: this.root_doc + '/plugins/consumables/ajax/request.php',
         data: {
            'action': 'reloadAvailableConsumablesNumber',
            'used': JSON.stringify(this.usedConsumables),
            'consumables_id': this.consumableID
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

/**
 * consumables_removeCart : delete text input
 *
 * @param field_id
 */
function consumables_removeCart(field_id) {
    var value = $("tr[id=" + field_id + "] input[id=number]").val();
    var consumables_id = $("tr[id=" + field_id + "] input[id=consumables_id]").val();

    // Remove element from used consumables variable
    this.usedConsumables[consumables_id] = this.usedConsumables[consumables_id] - parseInt(value);
   if (this.usedConsumables[consumables_id] < 0) {
       this.usedConsumables[consumables_id] = 0;
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
