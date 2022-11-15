<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ($perusahaan['status'] == $status) { ?>
<li data-perusahaan-id="<?php echo $perusahaan['id']; ?>" class="<?php if($perusahaan['invoice_id'] != NULL || $perusahaan['perusahaan_id'] != NULL){echo 'not-sortable';} ?>">
   <div class="panel-body">
      <div class="row">
         <div class="col-md-12">
            <h4 class="bold pipeline-heading">
               <a href="<?php echo admin_url('perusahaan/list_perusahaan/'.$perusahaan['id']); ?>" data-toggle="tooltip" data-title="<?php echo $perusahaan['subject']; ?>" onclick="perusahaan_pipeline_open(<?php echo $perusahaan['id']; ?>); return false;"><?php echo format_perusahaan_number($perusahaan['id']); ?></a>
               <?php if(has_permission('perusahaan','','edit')){ ?>
               <a href="<?php echo admin_url('perusahaan/perusahaan/'.$perusahaan['id']); ?>" target="_blank" class="pull-right"><small><i class="fa fa-pencil-square-o" aria-hidden="true"></i></small></a>
               <?php } ?>
            </h4>
            <span class="mbot10 inline-block full-width">
            <?php
               if($perusahaan['rel_type'] == 'lead'){
                 echo '<a href="'.admin_url('leads/index/'.$perusahaan['clientid']).'" onclick="init_lead('.$perusahaan['clientid'].'); return false;" data-toggle="tooltip" data-title="'._l('lead').'">' .$perusahaan['perusahaan_to'].'</a><br />';
               } else if($perusahaan['rel_type'] == 'customer'){
                 echo '<a href="'.admin_url('clients/client/'.$perusahaan['clientid']).'" data-toggle="tooltip" data-title="'._l('client').'">' .$perusahaan['perusahaan_to'].'</a><br />';
               }
               ?>
            </span>
         </div>
         <div class="col-md-12">
            <div class="row">
               <div class="col-md-8">
                  <?php if($perusahaan['total'] != 0){
                     ?>
                  <span class="bold"><?php echo _l('perusahaan_total'); ?>:
                     <?php echo app_format_money($perusahaan['total'], get_currency($perusahaan['currency'])); ?>
                  </span>
                  <br />
                  <?php } ?>
                  <?php echo _l('perusahaan_date'); ?>: <?php echo _d($perusahaan['date']); ?>
                  <?php if(is_date($perusahaan['open_till'])){ ?>
                  <br />
                  <?php echo _l('perusahaan_open_till'); ?>: <?php echo _d($perusahaan['open_till']); ?>
                  <?php } ?>
                  <br />
               </div>
               <div class="col-md-4 text-right">
                  <small><i class="fa fa-comments" aria-hidden="true"></i> <?php echo _l('perusahaan_comments'); ?>: <?php echo total_rows(db_prefix().'perusahaan_comments', array(
                     'perusahaan_id' => $perusahaan['id']
                     )); ?></small>
               </div>
               <?php $tags = get_tags_in($perusahaan['id'],'perusahaan');
                  if(count($tags) > 0){ ?>
               <div class="col-md-12">
                  <div class="mtop5 kanban-tags">
                     <?php echo render_tags($tags); ?>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</li>
<?php } ?>
