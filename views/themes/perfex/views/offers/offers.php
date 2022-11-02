<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-perusahaan">
  <div class="panel-body">
    <h4 class="no-margin section-text"><?php echo _l('perusahaan'); ?></h4>
  </div>
</div>
<div class="panel_s">
  <div class="panel-body">
    <table class="table dt-table table-perusahaan" data-order-col="3" data-order-type="desc">
      <thead>
        <tr>
          <th class="th-perusahaan-number"><?php echo _l('perusahaan') . ' #'; ?></th>
          <th class="th-perusahaan-subject"><?php echo _l('perusahaan_subject'); ?></th>
          <th class="th-perusahaan-total"><?php echo _l('perusahaan_total'); ?></th>
          <th class="th-perusahaan-open-till"><?php echo _l('perusahaan_open_till'); ?></th>
          <th class="th-perusahaan-date"><?php echo _l('perusahaan_date'); ?></th>
          <th class="th-perusahaan-status"><?php echo _l('perusahaan_status'); ?></th>
          <?php
          $custom_fields = get_custom_fields('perusahaan',array('show_on_client_portal'=>1));
          foreach($custom_fields as $field){ ?>
            <th><?php echo $field['name']; ?></th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($daftar__perusahaan as $perusahaan){ ?>
          <tr>
            <td>
              <a href="<?php echo site_url('perusahaan/'.$perusahaan['id'].'/'.$perusahaan['hash']); ?>" class="td-perusahaan-url">
                <?php echo format_perusahaan_number($perusahaan['id']); ?>
                <?php
                if ($perusahaan['invoice_id']) {
                  echo '<br /><span class="text-success perusahaan-invoiced">' . _l('perusahaan_invoiced') . '</span>';
                }
                ?>
              </a>
              <td>
                <a href="<?php echo site_url('perusahaan/'.$perusahaan['id'].'/'.$perusahaan['hash']); ?>" class="td-perusahaan-url-subject">
                  <?php echo $perusahaan['subject']; ?>
                </a>
                <?php
                if ($perusahaan['invoice_id'] != NULL) {
                  $invoice = $this->invoices_model->get($perusahaan['invoice_id']);
                  echo '<br /><a href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '" target="_blank" class="td-perusahaan-invoice-url">' . format_invoice_number($invoice->id) . '</a>';
                } else if ($perusahaan['perusahaan_id'] != NULL) {
                  $perusahaan = $this->perusahaan_model->get($perusahaan['perusahaan_id']);
                  echo '<br /><a href="' . site_url('perusahaan/' . $perusahaan->id . '/' . $perusahaan->hash) . '" target="_blank" class="td-perusahaan-perusahaan-url">' . format_perusahaan_number($perusahaan->id) . '</a>';
                }
                ?>
              </td>
              <td data-order="<?php echo $perusahaan['total']; ?>">
                <?php
                if ($perusahaan['currency'] != 0) {
                 echo app_format_money($perusahaan['total'], get_currency($perusahaan['currency']));
               } else {
                 echo app_format_money($perusahaan['total'], get_base_currency());
               }
               ?>
             </td>
             <td data-order="<?php echo $perusahaan['open_till']; ?>"><?php echo _d($perusahaan['open_till']); ?></td>
             <td data-order="<?php echo $perusahaan['date']; ?>"><?php echo _d($perusahaan['date']); ?></td>
             <td><?php echo format_perusahaan_status($perusahaan['status']); ?></td>
             <?php foreach($custom_fields as $field){ ?>
               <td><?php echo get_custom_field_value($perusahaan['id'],$field['id'],'perusahaan'); ?></td>
             <?php } ?>
           </tr>
         <?php } ?>
       </tbody>
     </table>
   </div>
 </div>
