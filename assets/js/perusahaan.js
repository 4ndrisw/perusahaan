// Init single perusahaan
function init_perusahaan(id) {
    load_small_table_item(id, '#perusahaan', 'perusahaan_id', 'perusahaan/get_perusahaan_data_ajax', '.table-perusahaan');
}

/*
if ($("body").hasClass('perusahaan-pipeline')) {
    var perusahaan_id = $('input[name="perusahaan_id"]').val();
    perusahaan_pipeline_open(perusahaan_id);
}
*/

// Ajax unit search but only for specific instansi
function init_ajax_unit_search_by_instansi_id(selector) {
    selector = typeof (selector) == 'undefined' ? '#unit_id.ajax-search' : selector;
    init_ajax_search('unit', selector, {
        instansi_id: function () {
            return $('#instansi_id').val();
        }
    });
}

function add_perusahaan_comment() {
    var comment = $('#comment').val();
    if (comment == '') {
        return;
    }
    var data = {};
    data.content = comment;
    data.perusahaan_id = perusahaan_id;
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'perusahaan/add_perusahaan_comment', data).done(function (response) {
        response = JSON.parse(response);
        $('body').find('.dt-loader').remove();
        if (response.success == true) {
            $('#comment').val('');
            get_perusahaan_comments();
        }
    });
}

function get_perusahaan_comments() {
    if (typeof (perusahaan_id) == 'undefined') {
        return;
    }
    requestGet('perusahaan/get_perusahaan_comments/' + perusahaan_id).done(function (response) {
        $('body').find('#perusahaan-comments').html(response);
        update_comments_count('perusahaan')
    });
}

function remove_perusahaan_comment(commentid) {
    if (confirm_delete()) {
        requestGetJSON('perusahaan/remove_comment/' + commentid).done(function (response) {
            if (response.success == true) {
                $('[data-commentid="' + commentid + '"]').remove();
                update_comments_count('perusahaan')
            }
        });
    }
}

function edit_perusahaan_comment(id) {
    var content = $('body').find('[data-perusahaan-comment-edit-textarea="' + id + '"] textarea').val();
    if (content != '') {
        $.post(admin_url + 'perusahaan/edit_comment/' + id, {
            content: content
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $('body').find('[data-perusahaan-comment="' + id + '"]').html(nl2br(content));
            }
        });
        toggle_perusahaan_comment_edit(id);
    }
}

function toggle_perusahaan_comment_edit(id) {
    $('body').find('[data-perusahaan-comment="' + id + '"]').toggleClass('hide');
    $('body').find('[data-perusahaan-comment-edit-textarea="' + id + '"]').toggleClass('hide');
}

function perusahaan_convert_template(invoker) {
    var template = $(invoker).data('template');
    var html_helper_selector;
    if (template == 'perusahaan') {
        html_helper_selector = 'perusahaan';
    } else if (template == 'invoice') {
        html_helper_selector = 'invoice';
    } else {
        return false;
    }

    requestGet('perusahaan/get_' + html_helper_selector + '_convert_data/' + perusahaan_id).done(function (data) {
        if ($('.perusahaan-pipeline-modal').is(':visible')) {
            $('.perusahaan-pipeline-modal').modal('hide');
        }
        $('#convert_helper').html(data);
        $('#convert_to_' + html_helper_selector).modal({
            show: true,
            backdrop: 'static'
        });
        reorder_items();
    });

}

function save_perusahaan_content(manual) {
    var editor = tinyMCE.activeEditor;
    var data = {};
    data.perusahaan_id = perusahaan_id;
    data.content = editor.getContent();
    $.post(admin_url + 'perusahaan/save_perusahaan_data', data).done(function (response) {
        response = JSON.parse(response);
        if (typeof (manual) != 'undefined') {
            // Show some message to the user if saved via CTRL + S
            alert_float('success', response.message);
        }
        // Invokes to set dirty to false
        editor.save();
    }).fail(function (error) {
        var response = JSON.parse(error.responseText);
        alert_float('danger', response.message);
    });
}

// Proposal sync data in case eq mail is changed, shown for lead and customers.
function sync_perusahaan_data(rel_id, rel_type) {
    var data = {};
    var modal_sync = $('#sync_data_perusahaan_data');
    data.country = modal_sync.find('select[name="country"]').val();
    data.zip = modal_sync.find('input[name="zip"]').val();
    data.state = modal_sync.find('input[name="state"]').val();
    data.city = modal_sync.find('input[name="city"]').val();
    data.address = modal_sync.find('textarea[name="address"]').val();
    data.phone = modal_sync.find('input[name="phone"]').val();
    data.rel_id = rel_id;
    data.rel_type = rel_type;
    $.post(admin_url + 'perusahaan/sync_data', data).done(function (response) {
        response = JSON.parse(response);
        alert_float('success', response.message);
        modal_sync.modal('hide');
    });
}


// Delete perusahaan attachment
function delete_perusahaan_attachment(id) {
    if (confirm_delete()) {
        requestGet('perusahaan/delete_attachment/' + id).done(function (success) {
            if (success == 1) {
                var rel_id = $("body").find('input[name="_attachment_sale_id"]').val();
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                $("body").hasClass('perusahaan-pipeline') ? perusahaan_pipeline_open(rel_id) : init_perusahaan(rel_id);
            }
        }).fail(function (error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Used when perusahaan is updated from pipeline. eq changed order or moved to another status
function perusahaan_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {
            perusahaan_id: $(ui.item).attr('data-perusahaan-id'),
            status: $(ui.item.parent()[0]).attr('data-status-id'),
            order: [],
        };

        $.each($(ui.item).parents('.pipeline-status').find('li'), function (idx, el) {
            var id = $(el).attr('data-perusahaan-id');
            if(id){
                data.order.push([id, idx+1]);
            }
        });

        check_kanban_empty_col('[data-perusahaan-id]');

        setTimeout(function () {
             $.post(admin_url + 'perusahaan/update_pipeline', data).done(function (response) {
                update_kan_ban_total_when_moving(ui,data.status);
                perusahaan_pipeline();
            });
        }, 200);
    }
}

// Used when perusahaan is updated from pipeline. eq changed order or moved to another status
function perusahaan_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {
            order: [],
            status: $(ui.item.parent()[0]).attr('data-status-id'),
            perusahaan_id: $(ui.item).attr('data-perusahaan-id'),
        };

        $.each($(ui.item).parents('.pipeline-status').find('li'), function (idx, el) {
            var id = $(el).attr('data-perusahaan-id');
            if(id){
                data.order.push([id, idx+1]);
            }
        });

        check_kanban_empty_col('[data-perusahaan-id]');

        setTimeout(function () {
            $.post(admin_url + 'perusahaan/update_pipeline', data).done(function (response) {
                update_kan_ban_total_when_moving(ui,data.status);
                perusahaan_pipeline();
            });
        }, 200);
    }
}

// Init perusahaan pipeline
function perusahaan_pipeline() {
    init_kanban('perusahaan/get_pipeline', perusahaan_pipeline_update, '.pipeline-status', 347, 360);
}

// Open single perusahaan in pipeline
function perusahaan_pipeline_open(id) {
    if (id === '') {
        return;
    }
    requestGet('perusahaan/pipeline_open/' + id).done(function (response) {
        var visible = $('.perusahaan-pipeline-modal:visible').length > 0;
        $('#perusahaan').html(response);
        if (!visible) {
            $('.perusahaan-pipeline-modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
        } else {
            $('#perusahaan').find('.modal.perusahaan-pipeline-modal')
                .removeClass('fade')
                .addClass('in')
                .css('display', 'block');
        }
    });
}

// Sort perusahaan in the pipeline view / switching sort type by click
function perusahaan_pipeline_sort(type) {
    kan_ban_sort(type, perusahaan_pipeline);
}

// Validates perusahaan add/edit form
function validate_perusahaan_form(selector) {

    selector = typeof (selector) == 'undefined' ? '#perusahaan-form' : selector;

    appValidateForm($(selector), {
        client_id: {
            required: {
                depends: function () {
                    var customerRemoved = $('select#client_id').hasClass('customer-removed');
                    return !customerRemoved;
                }
            }
        },
        date: 'required',
        office_id: 'required',
        number: {
            required: true
        }
    });

    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "perusahaan/validate_perusahaan_number",
            type: 'post',
            data: {
                number: function () {
                    return $('input[name="number"]').val();
                },
                isedit: function () {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function () {
                    return $('input[name="number"]').data('original-number');
                },
                date: function () {
                    return $('body').find('.perusahaan input[name="date"]').val();
                },
            }
        },
        messages: {
            remote: app.lang.perusahaan_number_exists,
        }
    });

}


// Get the preview main values
function get_perusahaan_item_preview_values() {
    var response = {};
    response.description = $('.main textarea[name="description"]').val();
    response.long_description = $('.main textarea[name="long_description"]').val();
    response.qty = $('.main input[name="quantity"]').val();
    return response;
}

// Append the added items to the preview to the table as items
function add_perusahaan_item_to_table(data, itemid){

  // If not custom data passed get from the preview
  data = typeof (data) == 'undefined' || data == 'undefined' ? get_perusahaan_item_preview_values() : data;
  if (data.description === "" && data.long_description === "") {
     return;
  }

  var table_row = '';
  var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('tbody .item').length + 1;
  lastAddedItemKey = item_key;

  table_row += '<tr class="sortable item">';

  table_row += '<td class="dragger">';

  // Check if quantity is number
  if (isNaN(data.qty)) {
     data.qty = 1;
  }

  $("body").append('<div class="dt-loader"></div>');
  var regex = /<br[^>]*>/gi;

     table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

     table_row += '</td>';

     table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description + '</textarea></td>';

     table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';
   //table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description + '</textarea></td>';


     table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + item_key + '][qty]" value="' + data.qty + '" class="form-control">';

     if (!data.unit || typeof (data.unit) == 'undefined') {
        data.unit = '';
     }

     table_row += '<input type="text" placeholder="' + app.lang.unit + '" name="newitems[' + item_key + '][unit]" class="form-control input-transparent text-right" value="' + data.unit + '">';

     table_row += '</td>';


     table_row += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + itemid + '); return false;"><i class="fa fa-trash"></i></a></td>';

     table_row += '</tr>';

     $('table.items tbody').append(table_row);

     $(document).trigger({
        type: "item-added-to-table",
        data: data,
        row: table_row
     });


     clear_item_preview_values();
     reorder_items();

     $('body').find('#items-warning').remove();
     $("body").find('.dt-loader').remove();

  return false;
}


// From perusahaan table mark as
function perusahaan_mark_as(status_id, perusahaan_id) {
    var data = {};
    data.status = status_id;
    data.perusahaan_id = perusahaan_id;
    $.post(admin_url + 'perusahaan/update_perusahaan_status', data).done(function (response) {
        //table_perusahaan.DataTable().ajax.reload(null, false);
        reload_perusahaan_tables();
    });
}

// Reload all perusahaan possible table where the table data needs to be refreshed after an action is performed on task.
function reload_perusahaan_tables() {
    var av_perusahaan_tables = ['.table-perusahaan', '.table-rel-perusahaan'];
    $.each(av_perusahaan_tables, function (i, selector) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload(null, false);
        }
    });
}
