
<div role="tabpanel" class="tab-pane" id="perusahaan">
         
            <?php if (has_permission('items', '', 'create') || has_permission('items', '', 'edit')) { ?>
            <a href="#" class="btn btn-info mbot30" data-toggle="modal" data-target="#sales_item_modals"><?php echo _l('new_invoice_item'); ?></a>
            <?php } ?>

      <div class="row">
         <div class="_filters _hidden_inputs">
            <?php
            
               foreach($statuses as $_status){
                $val = '';
                if($_status == $this->input->get('status')){
                  $val = $_status;
                }
                echo form_hidden('perusahaan_'.$_status,$val);
               }
               foreach($years as $year){
                echo form_hidden('year_'.$year['year'],$year['year']);
               }
               echo form_hidden('leads_related');
               echo form_hidden('customers_related');
               echo form_hidden('expired');
               ?>
         </div>
         <div class="col-md-12">
            <div class="panel_s mbot10">


               <div class="panel-body _buttons">

                  <?php if(has_permission('perusahaan','','create')){
                  $this->load->view('admin/perusahaan/perusahaan_top_stats');
                  } ?>


                  <?php if(has_permission('perusahaan','','create')){ ?>
                  <a href="<?php echo admin_url('perusahaan/add_perusahaan'); ?>" class="btn btn-info pull-left display-block">
                  <?php echo _l('new_perusahaan'); ?>
                  </a>
                  <?php } ?>
                  <a href="<?php echo admin_url('perusahaan/pipeline/'.$switch_pipeline); ?>" class="btn btn-default mleft5 pull-left hidden-xs"><?php echo _l('switch_to_pipeline'); ?></a>
                  <div class="display-block text-right">
                     
                      <div class="display-block pull-left mleft5">
                          <a href="#" class="btn btn-default equipments-total" onclick="slideToggle('#stats-top'); init_perusahaan_total(true); return false;" data-toggle="tooltip" title="<?php echo _l('view_stats_tooltip'); ?>"><i class="fa fa-bar-chart"></i></a>
                      </div>

                     <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu width300">
                           <li>
                              <a href="#" data-cview="all" onclick="dt_custom_view('','.table-perusahaan',''); return false;">
                              <?php echo _l('perusahaan_list_all'); ?>
                              </a>
                           </li>
                           <li class="divider"></li>
                           <?php foreach($statuses as $status){ ?>
                           <li class="<?php if($this->input->get('status') == $status){echo 'active';} ?>">
                              <a href="#" data-cview="perusahaan_<?php echo $status; ?>" onclick="dt_custom_view('perusahaan_<?php echo $status; ?>','.table-perusahaan','perusahaan_<?php echo $status; ?>'); return false;">
                              <?php echo format_perusahaan_status($status,'',false); ?>
                              </a>
                           </li>
                           <?php } ?>
                           <?php if(count($years) > 0){ ?>
                           <li class="divider"></li>
                           <?php foreach($years as $year){ ?>
                           <li class="active">
                              <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-perusahaan','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                              </a>
                           </li>
                           <?php } ?>
                           <?php } ?>
                           
                           <li>
                              <a href="#" data-cview="expired" onclick="dt_custom_view('expired','.table-perusahaan','expired'); return false;">
                              <?php echo _l('perusahaan_expired'); ?>
                              </a>
                           </li>
                           <li>
                              <a href="#" data-cview="leads_related" onclick="dt_custom_view('leads_related','.table-perusahaan','leads_related'); return false;">
                              <?php echo _l('perusahaan_leads_related'); ?>
                              </a>
                           </li>
                           <li>
                              <a href="#" data-cview="customers_related" onclick="dt_custom_view('customers_related','.table-perusahaan','customers_related'); return false;">
                              <?php echo _l('perusahaan_customers_related'); ?>
                              </a>
                           </li>
                        </ul>
                     </div>
                     <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-perusahaan','#perusahaan'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                        <!-- if invoiceid found in url -->
                        <?php echo form_hidden('perusahaan_id',$perusahaan_id); ?>
                        <?php
                           $table_data = array(
                              _l('perusahaan') . ' #',
                              _l('perusahaan_subject'),
                              _l('perusahaan_dt_tbl_to'),
                              _l('nomor_seri'),
                              _l('nomor_unit'),
                              _l('perusahaan_open_till'),
                              _l('perusahaan_date_created'),
                              _l('perusahaan_status'),
                            );

                             $custom_fields = get_custom_fields('perusahaan',array('show_on_table'=>1));
                             foreach($custom_fields as $field){
                                array_push($table_data,$field['name']);
                             }

                             $table_data = hooks()->apply_filters('perusahaan_table_columns', $table_data);
                             render_datatable($table_data,'perusahaan',[],[
                                 'data-last-order-identifier' => 'perusahaan',
                                 'data-default-order'         => get_table_last_order('perusahaan'),
                             ]);
                           ?>
                     </div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="perusahaan" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>

         </div>
         <?php //$CI->load->view(MODULE_PERUSAHAAN . '/admin/perusahaan/items/items'); ?>
         <div class="checkbox checkbox-primary no-mtop checkbox-inline task-add-edit-public" style=" display:none;">
                     <input type="checkbox" id="is_perusahaan" name="is_perusahaan" checked>
                     <label for="is_perusahaan"><?= _l('is_perusahaan') ?></label>
          </div>

<script>var hidden_columns = [4,5,6,7];</script>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>
   var perusahaan_id;
   $(function(){
     var Perusahaan_ServerParams = {};
     $.each($('._hidden_inputs._filters input'),function(){
       Perusahaan_ServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
     });
     initDataTable('.table-perusahaan', admin_url+'perusahaan/table', ['undefined'], ['undefined'], Perusahaan_ServerParams, [7, 'desc']);
     init_perusahaan();
   });
</script>          