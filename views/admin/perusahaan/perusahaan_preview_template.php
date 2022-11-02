<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id',$perusahaan->id); ?>
<?php echo form_hidden('_attachment_sale_type','perusahaan'); ?>
<div class="panel_s">
   <div class="panel-body">
      <div class="horizontal-scrollable-tabs preview-tabs-top">
         <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
         <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
         <div class="horizontal-tabs">
            <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_perusahaan" aria-controls="tab_perusahaan" role="tab" data-toggle="tab">
                  <?php echo _l('perusahaan'); ?>
                  </a>
               </li>
               <?php if(isset($perusahaan)){ ?>
               <li role="presentation">
                  <a href="#tab_comments" onclick="get_perusahaan_comments(); return false;" aria-controls="tab_comments" role="tab" data-toggle="tab">
                  <?php
                  echo _l('perusahaan_comments');
                  $total_comments = total_rows(db_prefix() . 'perusahaan_comments', [
                      'perusahaan_id' => $perusahaan->id,
                    ]
                  );
                  ?>
                      <span class="badge total_comments <?php echo $total_comments === 0 ? 'hide' : ''; ?>"><?php echo $total_comments ?></span>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $perusahaan->id ;?> + '/' + 'perusahaan', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                  <?php echo _l('perusahaan_reminders'); ?>
                  <?php
                     $total_reminders = total_rows(db_prefix().'reminders',
                      array(
                       'isnotified'=>0,
                       'staff'=>get_staff_user_id(),
                       'rel_type'=>'perusahaan',
                       'rel_id'=>$perusahaan->id
                       )
                      );
                     if($total_reminders > 0){
                      echo '<span class="badge">'.$total_reminders.'</span>';
                     }
                     ?>
                  </a>
               </li>
               <li role="presentation" class="tab-separator">
                  <a href="#tab_tasks" onclick="init_rel_tasks_table(<?php echo $perusahaan->id; ?>,'perusahaan'); return false;" aria-controls="tab_tasks" role="tab" data-toggle="tab">
                  <?php echo _l('tasks'); ?>
                  </a>
               </li>
               <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $perusahaan->id; ?>,'perusahaan'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                     <?php echo _l('perusahaan_notes'); ?>
                     <span class="notes-total">
                        <?php if($totalNotes > 0){ ?>
                           <span class="badge"><?php echo $totalNotes; ?></span>
                        <?php } ?>
                     </span>
                     </a>
               </li>
               <li role="presentation" class="tab-separator">
                     <a href="#tab_templates" onclick="get_templates('perusahaan', <?php echo $perusahaan->id ?? '' ?>); return false" aria-controls="tab_templates" role="tab" data-toggle="tab">
                        <?php
                        echo _l('templates');
                        $total_templates = total_rows(db_prefix() . 'templates', [
                            'type' => 'perusahaan',
                          ]
                        );
                        ?>
                         <span class="badge total_templates <?php echo $total_templates === 0 ? 'hide' : ''; ?>"><?php echo $total_templates ?></span>
                     </a>
               </li>
               <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                  <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab" data-toggle="tab">
                    <?php if(!is_mobile()){ ?>
                     <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                     <?php } else { ?>
                     <?php echo _l('emails_tracking'); ?>
                     <?php } ?>
                  </a>
               </li>
               <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
                  <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                    <?php if(!is_mobile()){ ?>
                     <i class="fa fa-eye"></i>
                     <?php } else { ?>
                     <?php echo _l('view_tracking'); ?>
                     <?php } ?>
                  </a>
               </li>
               <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                  <a href="#" onclick="small_table_full_view(); return false;">
                  <i class="fa fa-expand"></i></a>
               </li>
               <?php } ?>
            </ul>
         </div>
      </div>
      <div class="row mtop10">
         <div class="col-md-3">
            <?php echo format_perusahaan_status($perusahaan->status,'pull-left mright5 mtop5'); ?>
         </div>
         <div class="col-md-9 text-right _buttons perusahaan_buttons">
            <?php if(has_permission('perusahaan','','edit')){ ?>
            <a href="<?php echo admin_url('perusahaan/perusahaan/'.$perusahaan->id); ?>" data-placement="left" data-toggle="tooltip" title="<?php echo _l('perusahaan_edit'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>
            <?php } ?>
            <div class="btn-group">
               <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li class="hidden-xs"><a href="<?php echo site_url('perusahaan/pdf/'.$perusahaan->id.'?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                  <li class="hidden-xs"><a href="<?php echo site_url('perusahaan/pdf/'.$perusahaan->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                  <li><a href="<?php echo site_url('perusahaan/pdf/'.$perusahaan->id); ?>"><?php echo _l('download'); ?></a></li>
                  <li>
                     <a href="<?php echo site_url('perusahaan/pdf/'.$perusahaan->id.'?print=true'); ?>" target="_blank">
                     <?php echo _l('print'); ?>
                     </a>
                  </li>
               </ul>
            </div>
            <a href="#" class="btn btn-default btn-with-tooltip" data-target="#perusahaan_send_to_customer" data-toggle="modal"><span data-toggle="tooltip" class="btn-with-tooltip" data-title="<?php echo _l('perusahaan_send_to_email'); ?>" data-placement="bottom"><i class="fa fa-envelope"></i></span></a>
            <div class="btn-group ">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <?php echo _l('more'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                     <a href="<?php echo site_url('perusahaan/'.$perusahaan->id .'/'.$perusahaan->hash); ?>" target="_blank"><?php echo _l('perusahaan_view'); ?></a>
                  </li>
                  <?php hooks()->do_action('after_perusahaan_view_as_client_link', $perusahaan); ?>
                  <?php if(!empty($perusahaan->open_till) && date('Y-m-d') < $perusahaan->open_till && ($perusahaan->status == 4 || $perusahaan->status == 1) && is_perusahaan_expiry_reminders_enabled()) { ?>
                  <li>
                     <a href="<?php echo admin_url('perusahaan/send_expiry_reminder/'.$perusahaan->id); ?>"><?php echo _l('send_expiry_reminder'); ?></a>
                  </li>
                  <?php } ?>
                  <li>
                     <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                  </li>
                  <?php if(has_permission('perusahaan','','create')){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'perusahaan/copy/'.$perusahaan->id; ?>"><?php echo _l('perusahaan_copy'); ?></a>
                  </li>
                  <?php } ?>
                  <?php if($perusahaan->id == NULL && $perusahaan->invoice_id == NULL){ ?>
                  <?php foreach($perusahaan_statuses as $status){
                     if(has_permission('perusahaan','','edit')){
                      if($perusahaan->status != $status){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'perusahaan/mark_action_status/'.$status.'/'.$perusahaan->id; ?>"><?php echo _l('perusahaan_mark_as',format_perusahaan_status($status,'',false)); ?></a>
                  </li>
                  <?php
                     } } } ?>
                  <?php } ?>
                  <?php if(!empty($perusahaan->signature) && has_permission('perusahaan','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url('perusahaan/clear_signature/'.$perusahaan->id); ?>" class="_delete">
                     <?php echo _l('clear_signature'); ?>
                     </a>
                  </li>
                  <?php } ?>
                  <?php if(has_permission('perusahaan','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'perusahaan/delete/'.$perusahaan->id; ?>" class="text-danger delete-text _delete"><?php echo _l('perusahaan_delete'); ?></a>
                  </li>
                  <?php } ?>
               </ul>
            </div>
            <?php if($perusahaan->id == NULL && $perusahaan->invoice_id == NULL){ ?>
            <?php if(has_permission('perusahaan','','create') || has_permission('invoices','','create')){ ?>
            <div class="btn-group">
               <button type="button" class="btn btn-success dropdown-toggle<?php if($perusahaan->rel_type == 'customer' && total_rows(db_prefix().'clients',array('active'=>0,'userid'=>$perusahaan->rel_id)) > 0){echo ' disabled';} ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <?php echo _l('perusahaan_convert'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <?php
                     $disable_convert = false;
                     $not_related = false;

                     if($perusahaan->rel_type == 'lead'){
                      if(total_rows(db_prefix().'clients',array('leadid'=>$perusahaan->rel_id)) == 0){
                       $disable_convert = true;
                       $help_text = 'perusahaan_convert_to_lead_disabled_help';
                     }
                     } else if(empty($perusahaan->rel_type)){
                     $disable_convert = true;
                     $help_text = 'perusahaan_convert_not_related_help';
                     }
                     ?>
                  <?php if(has_permission('perusahaan','','create')){ ?>
                  <li <?php if($disable_convert){ echo 'data-toggle="tooltip" title="'._l($help_text,_l('perusahaan_convert_perusahaan')).'"';} ?>><a href="#" <?php if($disable_convert){ echo 'style="cursor:not-allowed;" onclick="return false;"';} else {echo 'data-template="perusahaan" onclick="perusahaan_convert_template(this); return false;"';} ?>><?php echo _l('perusahaan_convert_perusahaan'); ?></a></li>
                  <?php } ?>
                  <?php if(has_permission('invoices','','create')){ ?>
                  <li <?php if($disable_convert){ echo 'data-toggle="tooltip" title="'._l($help_text,_l('perusahaan_convert_invoice')).'"';} ?>><a href="#" <?php if($disable_convert){ echo 'style="cursor:not-allowed;" onclick="return false;"';} else {echo 'data-template="invoice" onclick="perusahaan_convert_template(this); return false;"';} ?>><?php echo _l('perusahaan_convert_invoice'); ?></a></li>
                  <?php } ?>
               </ul>
            </div>
            <?php } ?>
            <?php } else {
               if($perusahaan->id != NULL){
                echo '<a href="'.admin_url('perusahaan/list_perusahaan/'.$perusahaan->id).'" class="btn btn-info">'.format_perusahaan_number($perusahaan->id).'</a>';
               } else {
                echo '<a href="'.admin_url('invoices/list_invoices/'.$perusahaan->invoice_id).'" class="btn btn-info">'.format_invoice_number($perusahaan->invoice_id).'</a>';
               }
               } ?>
         </div>
      </div>
      <div class="clearfix"></div>
      <hr class="hr-panel-heading" />
      <div class="row">
         <div class="col-md-12">
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane active" id="tab_perusahaan">
                  <div class="row mtop10">
                     <?php if($perusahaan->status == 3 && !empty($perusahaan->acceptance_firstname) && !empty($perusahaan->acceptance_lastname) && !empty($perusahaan->acceptance_email)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-info">
                           <?php echo _l('accepted_identity_info',array(
                              _l('perusahaan_lowercase'),
                              '<b>'.$perusahaan->acceptance_firstname . ' ' . $perusahaan->acceptance_lastname . '</b> (<a href="mailto:'.$perusahaan->acceptance_email.'">'.$perusahaan->acceptance_email.'</a>)',
                              '<b>'. _dt($perusahaan->acceptance_date).'</b>',
                              '<b>'.$perusahaan->acceptance_ip.'</b>'.(is_admin() ? '&nbsp;<a href="'.admin_url('perusahaan/clear_acceptance_info/'.$perusahaan->id).'" class="_delete text-muted" data-toggle="tooltip" data-title="'._l('clear_this_information').'"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                        </div>
                     </div>
                     <?php } ?>
                     <div class="col-md-6">
                        <h4 class="bold">
                           <?php
                              $tags = get_tags_in($perusahaan->id,'perusahaan');
                              if(count($tags) > 0){
                               echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
                              }
                              ?>
                           <a href="<?php echo admin_url('perusahaan/perusahaan/'.$perusahaan->id); ?>">
                           <span id="perusahaan-number">
                           <?php echo format_perusahaan_number($perusahaan->id); ?>
                           </span>
                           </a>
                        </h4>
                        <h5 class="bold mbot15 font-medium"><a href="<?php echo admin_url('perusahaan/perusahaan/'.$perusahaan->id); ?>"><?php echo $perusahaan->subject; ?></a></h5>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-md-6 text-right">
                        <address>
                           <span class="bold"><?php echo _l('perusahaan_to'); ?>:</span><br />
                           <?php echo format_perusahaan_info($perusahaan,'admin'); ?>
                        </address>
                     </div>
                  </div>
                  <hr class="hr-panel-heading" />
                  <?php
                     if(count($perusahaan->attachments) > 0){ ?>
                  <p class="bold"><?php echo _l('perusahaan_files'); ?></p>
                  <?php foreach($perusahaan->attachments as $attachment){
                     $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                     if(!empty($attachment['external'])){
                        $attachment_url = $attachment['external_link'];
                     }
                     ?>
                  <div class="mbot15 row" data-attachment-id="<?php echo $attachment['id']; ?>">
                     <div class="col-md-8">
                        <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                        <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                        <br />
                        <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                     </div>
                     <div class="col-md-4 text-right">
                        <?php if($attachment['visible_to_customer'] == 0){
                           $icon = 'fa-toggle-off';
                           $tooltip = _l('show_to_customer');
                           } else {
                           $icon = 'fa-toggle-on';
                           $tooltip = _l('hide_from_customer');
                           }
                           ?>
                        <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $perusahaan->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i></a>
                        <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                        <a href="#" class="text-danger" onclick="delete_perusahaan_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
                  <?php } ?>
                  <div class="clearfix"></div>

                  <div class="row">
                     <div class="col-md-12">
                        <div class="table-responsive">
                              <?php
                                 $items = get_items_table_data($perusahaan, 'perusahaan', 'html', true);
                                 echo $items->table();
                              ?>
                        </div>
                     </div>
                     <div class="col-md-5 col-md-offset-7">
                        <table class="table text-right">
                           <tbody>
                              <tr id="subtotal">
                                 <td><span class="bold"><?php echo _l('perusahaan_subtotal'); ?></span>
                                 </td>
                                 <td class="subtotal">
                                    <?php echo app_format_money($perusahaan->subtotal, $perusahaan->currency_name); ?>
                                 </td>
                              </tr>
                              <?php if(is_sale_discount_applied($perusahaan)){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('perusahaan_discount'); ?>
                                    <?php if(is_sale_discount($perusahaan,'percent')){ ?>
                                    (<?php echo app_format_number($perusahaan->discount_percent,true); ?>%)
                                    <?php } ?></span>
                                 </td>
                                 <td class="discount">
                                    <?php echo '-' . app_format_money($perusahaan->discount_total, $perusahaan->currency_name); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <?php
                                 foreach($items->taxes() as $tax){
                                     echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('.app_format_number($tax['taxrate']).'%)</td><td>'.app_format_money($tax['total_tax'], $perusahaan->currency_name).'</td></tr>';
                                 }
                                 ?>
                              <?php if((int)$perusahaan->adjustment != 0){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('perusahaan_adjustment'); ?></span>
                                 </td>
                                 <td class="adjustment">
                                    <?php echo app_format_money($perusahaan->adjustment, $perusahaan->currency_name); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <td><span class="bold"><?php echo _l('perusahaan_total'); ?></span>
                                 </td>
                                 <td class="total">
                                    <?php echo app_format_money($perusahaan->total, $perusahaan->currency_name); ?>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                     <?php if(count($perusahaan->attachments) > 0){ ?>
                     <div class="clearfix"></div>
                     <hr />
                     <div class="col-md-12">
                        <p class="bold text-muted"><?php echo _l('perusahaan_files'); ?></p>
                     </div>
                     <?php foreach($perusahaan->attachments as $attachment){
                        $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                        if(!empty($attachment['external'])){
                          $attachment_url = $attachment['external_link'];
                        }
                        ?>
                     <div class="mbot15 row col-md-12" data-attachment-id="<?php echo $attachment['id']; ?>">
                        <div class="col-md-8">
                           <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                           <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                           <br />
                           <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                        </div>
                        <div class="col-md-4 text-right">
                           <?php if($attachment['visible_to_customer'] == 0){
                              $icon = 'fa fa-toggle-off';
                              $tooltip = _l('show_to_customer');
                              } else {
                              $icon = 'fa fa-toggle-on';
                              $tooltip = _l('hide_from_customer');
                              }
                              ?>
                           <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $perusahaan->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="<?php echo $icon; ?>" aria-hidden="true"></i></a>
                           <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                           <a href="#" class="text-danger" onclick="delete_perusahaan_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                           <?php } ?>
                        </div>
                     </div>
                     <?php } ?>
                     <?php } ?>
                     
                  </div>

                      <?php if(!empty($perusahaan->signature)) { ?>
                        <div class="row mtop25">
                           <div class="col-md-6 col-md-offset-6 text-right">
                              <div class="bold">
                                 <p class="no-mbot"><?php echo _l('contract_signed_by') . ": {$perusahaan->acceptance_firstname} {$perusahaan->acceptance_lastname}"?></p>
                                 <p class="no-mbot"><?php echo _l('perusahaan_signed_date') . ': ' . _dt($perusahaan->acceptance_date) ?></p>
                                 <p class="no-mbot"><?php echo _l('perusahaan_signed_ip') . ": {$perusahaan->acceptance_ip}"?></p>
                              </div>
                              <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                                 <?php if(has_permission('perusahaan','','delete')){ ?>
                                 <a href="<?php echo admin_url('perusahaan/clear_signature/'.$perusahaan->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                                 <i class="fa fa-remove"></i>
                                 </a>
                                 <?php } ?>
                              </p>
                              <div class="pull-right">
                                 <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_upload_path_by_type('perusahaan').$perusahaan->id.'/'.$perusahaan->signature)); ?>" class="img-responsive" alt="">
                              </div>
                           </div>
                        </div>
                        <?php } ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_comments">
                  <div class="row perusahaan-comments mtop15">
                     <div class="col-md-12">
                        <div id="perusahaan-comments"></div>
                        <div class="clearfix"></div>
                        <textarea name="content" id="comment" rows="4" class="form-control mtop15 perusahaan-comment"></textarea>
                        <button type="button" class="btn btn-info mtop10 pull-right" onclick="add_perusahaan_comment();"><?php echo _l('perusahaan_add_comment'); ?></button>
                     </div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_notes">
                  <?php echo form_open(admin_url('perusahaan/add_note/'.$perusahaan->id),array('id'=>'sales-notes','class'=>'perusahaan-notes-form')); ?>
                  <?php echo render_textarea('description'); ?>
                  <div class="text-right">
                     <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('perusahaan_add_note'); ?></button>
                  </div>
                  <?php echo form_close(); ?>
                  <hr />
                  <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_templates">
                  <div class="row perusahaan-templates">
                     <div class="col-md-12">
                        <button type="button" class="btn btn-info" onclick="add_template('perusahaan',<?php echo $perusahaan->id ?? '' ?>);"><?php echo _l('add_template'); ?></button>
                        <hr>
                     </div>
                     <div class="col-md-12">
                        <div id="perusahaan-templates" class="perusahaan-templates-wrapper"></div>
                     </div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
                  <?php
                     $this->load->view('admin/includes/emails_tracking',array(
                       'tracked_emails'=>
                       get_tracked_emails($perusahaan->id, 'perusahaan'))
                       );
                     ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_tasks">
                  <?php init_relation_tasks_table(array( 'data-new-rel-id'=>$perusahaan->id,'data-new-rel-type'=>'perusahaan')); ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_reminders">
                  <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-perusahaan-<?php echo $perusahaan->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('perusahaan_set_reminder_title'); ?></a>
                  <hr />
                  <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
                  <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$perusahaan->id,'name'=>'perusahaan','members'=>$members,'reminder_title'=>_l('perusahaan_set_reminder_title'))); ?>
               </div>
               <div role="tabpanel" class="tab-pane ptop10" id="tab_views">
                  <?php
                     $views_activity = get_views_tracking('perusahaan',$perusahaan->id);
                       if(count($views_activity) === 0) {
                     echo '<h4 class="no-margin">'._l('not_viewed_yet',_l('perusahaan_lowercase')).'</h4>';
                     }
                     foreach($views_activity as $activity){ ?>
                  <p class="text-success no-margin">
                     <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
                  </p>
                  <p class="text-muted">
                     <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
                  </p>
                  <hr />
                  <?php } ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div id="modal-wrapper"></div>
<?php // $this->load->view('admin/perusahaan/send_perusahaan_to_email_template'); ?>
<script>
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
     // defined in manage perusahaan
     perusahaan_id = '<?php echo $perusahaan->id; ?>';
     //init_perusahaan_editor();
</script>
