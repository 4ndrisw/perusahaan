<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s no-margin">
                    <div class="panel-body _buttons">
                        <div class="row">
                            <div class="col-md-8">
                                <?php if(has_permission('perusahaan','','create')){ ?>
                                <a href="<?php echo admin_url('perusahaan/perusahaan'); ?>" class="btn btn-info pull-left new"><?php echo _l('new_perusahaan'); ?></a>
                                <?php } ?>
                                <a href="<?php echo admin_url('perusahaan/pipeline/'.$switch_pipeline); ?>" class="btn btn-default mleft5 pull-left"><?php echo _l('switch_to_list_view'); ?></a>
                            </div>
                            <div class="col-md-4" data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
                                <?php echo render_input('search','','','search',array('data-name'=>'search','onkeyup'=>'perusahaan_pipeline();'),array(),'no-margin') ?>
                                <?php echo form_hidden('sort_type'); ?>
                                <?php echo form_hidden('sort',(get_option('default_perusahaan_pipeline_sort') != '' ? get_option('default_perusahaan_pipeline_sort_type') : '')); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel_s animated mtop5 fadeIn">
                    <?php echo form_hidden('perusahaan_id',$perusahaan_id); ?>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="kanban-leads-sort">
                                    <span class="bold"><?php echo _l('perusahaan_pipeline_sort'); ?>: </span>
                                    <a href="#" onclick="perusahaan_pipeline_sort('datecreated'); return false" class="datecreated">
                                        <?php if(get_option('default_perusahaan_pipeline_sort') == 'datecreated'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_perusahaan_pipeline_sort_type')).'"></i> ';} ?><?php echo _l('perusahaan_sort_datecreated'); ?>
                                        </a>
                                    |
                                    <a href="#" onclick="perusahaan_pipeline_sort('date'); return false" class="date">
                                       <?php if(get_option('default_perusahaan_pipeline_sort') == 'date'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_perusahaan_pipeline_sort_type')).'"></i> ';} ?><?php echo _l('perusahaan_sort_perusahaan_date'); ?>
                                        </a>
                                    |
                                    <a href="#" onclick="perusahaan_pipeline_sort('pipeline_order');return false;" class="pipeline_order">
                                        <?php if(get_option('default_perusahaan_pipeline_sort') == 'pipeline_order'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_perusahaan_pipeline_sort_type')).'"></i> ';} ?><?php echo _l('perusahaan_sort_pipeline'); ?>
                                        </a>
                                    |
                                    <a href="#" onclick="perusahaan_pipeline_sort('open_till');return false;" class="open_till">
                                       <?php if(get_option('default_perusahaan_pipeline_sort') == 'open_till'){echo '<i class="kanban-sort-icon fa fa-sort-amount-'.strtolower(get_option('default_perusahaan_pipeline_sort_type')).'"></i> ';} ?><?php echo _l('perusahaan_sort_open_till'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div id="perusahaan-pipeline">
                                <div class="container-fluid">
                                    <div id="kan-ban"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="perusahaan">
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>
   $(function(){
      perusahaan_pipeline();
  });
</script>
</body>
</html>
