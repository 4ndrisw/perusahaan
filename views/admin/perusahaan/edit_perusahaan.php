<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content accounting-template perusahaan">
    <div class="row">
      <?php
      if (isset($perusahaan)) {
        echo form_hidden('isedit', $perusahaan->id);
      }
      $rel_type = '';
      $rel_id = '';
      if (isset($perusahaan) || ($this->input->get('rel_id') && $this->input->get('rel_type'))) {
        if ($this->input->get('rel_id')) {
          $rel_id = $this->input->get('rel_id');
          $rel_type = $this->input->get('rel_type');
        } else {
          $rel_id = $perusahaan->rel_id;
          $rel_type = $perusahaan->rel_type;
        }
      }
      ?>
      <?php
      echo form_open($this->uri->uri_string(), array('id' => 'perusahaan-form', 'class' => '_transaction_form perusahaan-form'));

      if ($this->input->get('perusahaan_request_id')) {
        echo form_hidden('perusahaan_request_id', $this->input->get('perusahaan_request_id'));
      }
      ?>

      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="row">
              <?php if (isset($perusahaan)) { ?>
                <div class="col-md-12">
                  <?php echo format_perusahaan_status($perusahaan->status); ?>
                </div>
                <div class="clearfix"></div>
                <hr />
              <?php } ?>
              <div class="col-md-6 border-right">
                <?php $value = (isset($perusahaan) ? $perusahaan->subject : ''); ?>
                <?php $attrs = (isset($perusahaan) ? array() : array('autofocus' => true)); ?>
                <?php echo render_input('subject', 'perusahaan_subject', $value, 'text', $attrs); ?>
                <div class="form-group select-placeholder">
                  <label for="rel_type" class="control-label"><?php echo _l('perusahaan_related'); ?></label>
                  <select name="rel_type" id="rel_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                    <option value=""></option>
                    <option value="lead" <?php if ((isset($perusahaan) && $perusahaan->rel_type == 'lead') || $this->input->get('rel_type')) {
                                            if ($rel_type == 'lead') {
                                              echo 'selected';
                                            }
                                          } ?>><?php echo _l('perusahaan_for_lead'); ?></option>
                    <option value="customer" <?php if ((isset($perusahaan) &&  $perusahaan->rel_type == 'customer') || $this->input->get('rel_type')) {
                                                if ($rel_type == 'customer') {
                                                  echo 'selected';
                                                }
                                              } ?>><?php echo _l('perusahaan_for_customer'); ?></option>
                  </select>
                </div>
                <div class="form-group select-placeholder<?php if ($rel_id == '') {
                                                            echo ' hide';
                                                          } ?> " id="rel_id_wrapper">
                  <label for="rel_id"><span class="rel_id_label"></span></label>
                  <div id="rel_id_select">
                    <select name="rel_id" id="rel_id" class="ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                      <?php if ($rel_id != '' && $rel_type != '') {
                        $rel_data = get_relation_data($rel_type, $rel_id);
                        $rel_val = get_relation_values($rel_data, $rel_type);
                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                      } ?>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <?php $value = (isset($perusahaan) ? _d($perusahaan->date) : _d(date('Y-m-d'))) ?>
                    <?php echo render_date_input('date', 'perusahaan_date', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php
                    $value = '';
                    if (isset($perusahaan)) {
                      $value = _d($perusahaan->open_till);
                    } else {
                      if (get_option('perusahaan_due_after') != 0) {
                        $value = _d(date('Y-m-d', strtotime('+' . get_option('perusahaan_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                      }
                    }
                    echo render_date_input('open_till', 'perusahaan_open_till', $value); ?>
                  </div>
                </div>
                <?php
                $selected = '';
                $currency_attr = array('data-show-subtext' => true);
                foreach ($currencies as $currency) {
                  if ($currency['isdefault'] == 1) {
                    $currency_attr['data-base'] = $currency['id'];
                  }
                  if (isset($perusahaan)) {
                    if ($currency['id'] == $perusahaan->currency) {
                      $selected = $currency['id'];
                    }
                    if ($perusahaan->rel_type == 'customer') {
                      $currency_attr['disabled'] = true;
                    }
                  } else {
                    if ($rel_type == 'customer') {
                      $customer_currency = $this->clients_model->get_customer_default_currency($rel_id);
                      if ($customer_currency != 0) {
                        $selected = $customer_currency;
                      } else {
                        if ($currency['isdefault'] == 1) {
                          $selected = $currency['id'];
                        }
                      }
                      $currency_attr['disabled'] = true;
                    } else {
                      if ($currency['isdefault'] == 1) {
                        $selected = $currency['id'];
                      }
                    }
                  }
                }
                $currency_attr = apply_filters_deprecated('perusahaan_currency_disabled', [$currency_attr], '2.3.0', 'perusahaan_currency_attributes');
                $currency_attr = hooks()->apply_filters('perusahaan_currency_attributes', $currency_attr);
                ?>
                <div class="row">
                  <div class="col-md-6">
                    <?php
                    echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'perusahaan_currency', $selected, $currency_attr);
                    ?>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group select-placeholder">
                      <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                      <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value="" selected><?php echo _l('no_discount'); ?></option>
                        <option value="before_tax" <?php
                                                    if (isset($perusahaan)) {
                                                      if ($perusahaan->discount_type == 'before_tax') {
                                                        echo 'selected';
                                                      }
                                                    } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                        <option value="after_tax" <?php if (isset($perusahaan)) {
                                                    if ($perusahaan->discount_type == 'after_tax') {
                                                      echo 'selected';
                                                    }
                                                  } ?>><?php echo _l('discount_type_after_tax'); ?></option>
                      </select>
                    </div>
                  </div>
                </div>
                <?php $fc_rel_id = (isset($perusahaan) ? $perusahaan->id : false); ?>
                <?php echo render_custom_fields('perusahaan', $fc_rel_id); ?>
                <div class="form-group no-mbot">
                  <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                  <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($perusahaan) ? prep_tags_input(get_tags_in($perusahaan->id, 'perusahaan')) : ''); ?>" data-role="tagsinput">
                </div>
                <div class="form-group mtop10 no-mbot">
                  <p><?php echo _l('perusahaan_allow_comments'); ?></p>
                  <div class="onoffswitch">
                    <input type="checkbox" id="allow_comments" class="onoffswitch-checkbox" <?php if ((isset($perusahaan) && $perusahaan->allow_comments == 1) || !isset($perusahaan)) {
                                                                                              echo 'checked';
                                                                                            }; ?> value="on" name="allow_comments">
                    <label class="onoffswitch-label" for="allow_comments" data-toggle="tooltip" title="<?php echo _l('perusahaan_allow_comments_help'); ?>"></label>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group select-placeholder">
                      <label for="status" class="control-label"><?php echo _l('perusahaan_status'); ?></label>
                      <?php
                      $disabled = '';
                      if (isset($perusahaan)) {
                        if ($perusahaan->id != NULL || $perusahaan->invoice_id != NULL) {
                          $disabled = 'disabled';
                        }
                      }
                      ?>
                      <select name="status" class="selectpicker" data-width="100%" <?php echo $disabled; ?> data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <?php foreach ($statuses as $status) { ?>
                          <option value="<?php echo $status; ?>" <?php if ((isset($perusahaan) && $perusahaan->status == $status) || (!isset($perusahaan) && $status == 0)) {
                                                                    echo 'selected';
                                                                  } ?>><?php echo format_perusahaan_status($status, '', false); ?></option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <?php
                    $i = 0;
                    $selected = '';
                    foreach ($staff as $member) {
                      if (isset($perusahaan)) {
                        if ($perusahaan->assigned == $member['staffid']) {
                          $selected = $member['staffid'];
                        }
                      }
                      $i++;
                    }
                    echo render_select('assigned', $staff, array('staffid', array('firstname', 'lastname')), 'perusahaan_assigned', $selected);
                    ?>
                  </div>
                </div>
                <?php $value = (isset($perusahaan) ? $perusahaan->perusahaan_to : ''); ?>
                <?php echo render_input('perusahaan_to', 'perusahaan_to', $value); ?>
                <?php $value = (isset($perusahaan) ? $perusahaan->address : ''); ?>
                <?php echo render_textarea('address', 'perusahaan_address', $value); ?>
                <div class="row">
                  <div class="col-md-6">
                    <?php $value = (isset($perusahaan) ? $perusahaan->city : ''); ?>
                    <?php echo render_input('city', 'billing_city', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($perusahaan) ? $perusahaan->state : ''); ?>
                    <?php echo render_input('state', 'billing_state', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $countries = get_all_countries(); ?>
                    <?php $selected = (isset($perusahaan) ? $perusahaan->country : ''); ?>
                    <?php echo render_select('country', $countries, array('country_id', array('short_name'), 'iso2'), 'billing_country', $selected); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($perusahaan) ? $perusahaan->zip : ''); ?>
                    <?php echo render_input('zip', 'billing_zip', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($perusahaan) ? $perusahaan->email : ''); ?>
                    <?php echo render_input('email', 'perusahaan_email', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($perusahaan) ? $perusahaan->phone : ''); ?>
                    <?php echo render_input('phone', 'perusahaan_phone', $value); ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="btn-bottom-toolbar bottom-transaction text-right">
              <p class="no-mbot pull-left mtop5 btn-toolbar-notice"><?php echo _l('include_perusahaan_items_merge_field_help', '<b>{perusahaan_items}</b>'); ?></p>
              <button type="button" class="btn btn-info mleft10 perusahaan-form-submit save-and-send transaction-submit">
                <?php echo _l('save_and_send'); ?>
              </button>
              <button class="btn btn-info mleft5 perusahaan-form-submit transaction-submit" type="button">
                <?php echo _l('submit'); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <div class="panel_s">
          <?php $this->load->view('admin/perusahaan/_add_edit_items'); ?>
        </div>
      </div>
      <?php echo form_close(); ?>
      <?php $this->load->view('admin/invoice_items/item'); ?>
    </div>
    <div class="btn-bottom-pusher"></div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  var _rel_id = $('#rel_id'),
    _rel_type = $('#rel_type'),
    _rel_id_wrapper = $('#rel_id_wrapper'),
    data = {};

  $(function() {
    init_currency();
    // Maybe items ajax search
    init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
    validate_perusahaan_form();
    $('body').on('change', '#rel_id', function() {
      if ($(this).val() != '') {
        $.get(admin_url + 'perusahaan/get_relation_data_values/' + $(this).val() + '/' + _rel_type.val(), function(response) {
          $('input[name="perusahaan_to"]').val(response.to);
          $('textarea[name="address"]').val(response.address);
          $('input[name="email"]').val(response.email);
          $('input[name="phone"]').val(response.phone);
          $('input[name="city"]').val(response.city);
          $('input[name="state"]').val(response.state);
          $('input[name="zip"]').val(response.zip);
          $('select[name="country"]').selectpicker('val', response.country);
          var currency_selector = $('#currency');
          if (_rel_type.val() == 'customer') {
            if (typeof(currency_selector.attr('multi-currency')) == 'undefined') {
              currency_selector.attr('disabled', true);
            }

          } else {
            currency_selector.attr('disabled', false);
          }
          var perusahaan_to_wrapper = $('[app-field-wrapper="perusahaan_to"]');
          if (response.is_using_company == false && !empty(response.company)) {
            perusahaan_to_wrapper.find('#use_company_name').remove();
            perusahaan_to_wrapper.find('#use_company_help').remove();
            perusahaan_to_wrapper.append('<div id="use_company_help" class="hide">' + response.company + '</div>');
            perusahaan_to_wrapper.find('label')
              .prepend("<a href=\"#\" id=\"use_company_name\" data-toggle=\"tooltip\" data-title=\"<?php echo _l('use_company_name_instead'); ?>\" onclick='document.getElementById(\"perusahaan_to\").value = document.getElementById(\"use_company_help\").innerHTML.trim(); this.remove();'><i class=\"fa fa-building-o\"></i></a> ");
          } else {
            perusahaan_to_wrapper.find('label #use_company_name').remove();
            perusahaan_to_wrapper.find('label #use_company_help').remove();
          }
          /* Check if customer default currency is passed */
          if (response.currency) {
            currency_selector.selectpicker('val', response.currency);
          } else {
            /* Revert back to base currency */
            currency_selector.selectpicker('val', currency_selector.data('base'));
          }
          currency_selector.selectpicker('refresh');
          currency_selector.change();
        }, 'json');
      }
    });
    $('.rel_id_label').html(_rel_type.find('option:selected').text());
    _rel_type.on('change', function() {
      var clonedSelect = _rel_id.html('').clone();
      _rel_id.selectpicker('destroy').remove();
      _rel_id = clonedSelect;
      $('#rel_id_select').append(clonedSelect);
      perusahaan_rel_id_select();
      if ($(this).val() != '') {
        _rel_id_wrapper.removeClass('hide');
      } else {
        _rel_id_wrapper.addClass('hide');
      }
      $('.rel_id_label').html(_rel_type.find('option:selected').text());
    });
    perusahaan_rel_id_select();
    <?php if (!isset($perusahaan) && $rel_id != '') { ?>
      _rel_id.change();
    <?php } ?>
  });

  function perusahaan_rel_id_select() {
    var serverData = {};
    serverData.rel_id = _rel_id.val();
    data.type = _rel_type.val();
    init_ajax_search(_rel_type.val(), _rel_id, serverData);
  }

  function validate_perusahaan_form() {
    appValidateForm($('#perusahaan-form'), {
      subject: 'required',
      perusahaan_to: 'required',
      rel_type: 'required',
      rel_id: 'required',
      date: 'required',
      email: {
        email: true,
        required: true
      },
      currency: 'required',
    });
  }
</script>
</body>

</html>