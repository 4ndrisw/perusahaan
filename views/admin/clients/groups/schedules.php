<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(isset($client)){ ?>
	<h4 class="customer-profile-group-heading"><?php echo _l('schedules'); ?></h4>
	<?php if(has_permission('schedules','','create')){ ?>
		<a href="<?php echo admin_url('schedules/schedule?customer_id='.$client->userid); ?>" class="btn btn-info mbot15<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('create_new_schedule'); ?></a>
	<?php } ?>
	<?php if(has_permission('schedules','','view') || has_permission('schedules','','view_own') || get_option('allow_staff_view_schedules_assigned') == '1'){ ?>
		<a href="#" class="btn btn-info mbot15" data-toggle="modal" data-target="#client_zip_schedules"><?php echo _l('zip_schedules'); ?></a>
	<?php } ?>
	<div id="schedules_total"></div>
	<?php
	$this->load->view('admin/schedules/table_html', array('class'=>'schedules-single-client'));
	$this->load->view('admin/clients/modals/zip_schedules');
	?>
<?php } ?>
