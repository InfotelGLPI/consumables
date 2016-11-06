function consumables_initJs(root_doc, consumableTypeID, consumableID) {
    this.usedConsumables = 0;
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
                        result += "<td>" + row.label.replace(/\\["|']/g, '"') + "<input type='hidden' id='" + index + "' name='consumables_cart[" + data.rowId + "][" + index + "]' value='" + row.value + "'></td>\n";

                        // Push used consumables
                        if (index == 'number' && row.value != 0) {
                            object.usedConsumables = object.usedConsumables + parseInt(row.label);
                        }
                    } else {
                        result += "<input type='hidden' id='" + index + "' name='consumables_cart[" + data.rowId + "][" + index + "]' value='" + row.value + "'>";
                    }
                });

                result += "<td><img style=\"cursor:pointer\" src=\"" + object.root_doc + "/plugins/consumables/pics/delete.png\" onclick=\"consumables_removeCart('consumables_cartRow" + data.rowId + "')\"></td></tr>";

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
            'used': this.usedConsumables,
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
            'used': this.usedConsumables,
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

    // Remove element from used consumables variable
    this.usedConsumables = this.usedConsumables - parseInt(value);
    if (this.usedConsumables < 0) {
        this.usedConsumables = 0;
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


/**
 *  Add elements in item forms
 */
function consumables_addelements(params) {
    var root_doc = params.root_doc;
    var glpi_tab = params.glpi_tab;

    $(document).ready(function () {
        $.urlParam = function (name) {
            var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
            if (results != null) {
                return results[1] || 0;
            }
            return undefined;
        };
        // get item id
        var items_id = $.urlParam('id');

        if (items_id == undefined) items_id = 0;

        // Launched on each complete Ajax load
        $(document).ajaxComplete(function (event, xhr, option) {
            setTimeout(function () {
                // We execute the code only if the ticket form display request is done
                if (option.url != undefined) {
                    var ajaxTab_param, tid;
                    var paramFinder = /[?&]?_glpi_tab=([^&]+)(&|$)/;

                    // We find the name of the current tab
                    ajaxTab_param = paramFinder.exec(option.url);

                    // Get the right tab
                    if (ajaxTab_param != undefined
                        && (ajaxTab_param[1] == glpi_tab)) {

                        $.ajax({
                            url: root_doc + '/plugins/consumables/ajax/field.php',
                            type: "POST",
                            dataType: "html",
                            data: {
                                'consumables_id': items_id,
                                'action': 'showOrderReference'
                            },
                            success: function (response, opts) {
                                // Get element where insert html
                                var inputName = 'update';
                                if (items_id == 0) {
                                    inputName = 'add';
                                }
                                var item_bloc = $("form table[id='mainformtable'] input[name='" + inputName + "']");
                                $(response).insertBefore(item_bloc.closest('tr'));
                            }
                        });
                    }
                }

            }, 100);
        }, this);
    });
}

