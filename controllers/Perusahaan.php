<?php
defined('BASEPATH') or exit('No direct script access allowed');

use modules\perusahaan\services\perusahaan\PerusahaanPipeline;


class Perusahaan extends AdminController
{
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('perusahaan_model');
        $this->load->model('currencies_model');
        include_once(module_libs_path('perusahaan') . 'mails/Perusahaan_mail_template.php');
        //$this->load->library('module_name/library_name'); 
        $this->load->library('perusahaan_mail_template'); 
        //include_once(module_libs_path(PERUSAHAAN_MODULE_NAME) . 'mails/Perusahaan_send_to_customer.php');
        //$this->load->library('module_name/library_name'); 
        //$this->load->library('perusahaan_send_to_customer'); 


    }

    public function index($perusahaan_id = '')
    {
        $this->list_perusahaan($perusahaan_id);
    }

    public function list_perusahaan($perusahaan_id = '')
    {
        close_setup_menu();

        if (!has_permission('perusahaan', '', 'view') && !has_permission('perusahaan', '', 'view_own') && get_option('allow_staff_view_perusahaan_assigned') == 0) {
            access_denied('perusahaan');
        }
        
        log_activity($perusahaan_id);

        $isPipeline = $this->session->userdata('perusahaan_pipeline') == 'true';

        if ($isPipeline && !$this->input->get('status')) {
            $data['title']           = _l('perusahaan_pipeline');
            $data['bodyclass']       = 'perusahaan-pipeline';
            $data['switch_pipeline'] = false;
            // Direct access
            if (is_numeric($perusahaan_id)) {
                $data['perusahaan_id'] = $perusahaan_id;
            } else {
                $data['perusahaan_id'] = $this->session->flashdata('perusahaan_id');
            }

            $this->load->view('admin/perusahaan/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['perusahaan_id']           = $perusahaan_id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('perusahaan');
            $data['statuses']              = $this->perusahaan_model->get_statuses();
            //$data['perusahaan_sale_agents'] = $this->perusahaan_model->get_sale_agents();
            $data['years']                 = $this->perusahaan_model->get_perusahaan_years();
            
            log_activity(json_encode($data));
            if($perusahaan_id){
                $this->load->view('admin/perusahaan/manage_small_table', $data);
            }else{
                $this->load->view('admin/perusahaan/manage_table', $data);
            }
        }
    }

    public function table()
    {
        if (
            !has_permission('perusahaan', '', 'view')
            && !has_permission('perusahaan', '', 'view_own')
            && get_option('allow_staff_view_perusahaan_assigned') == 0
        ) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('perusahaan', 'tables/perusahaan'));
        
    }
    
    public function small_table()
    {
        if (
            !has_permission('perusahaan', '', 'view')
            && !has_permission('perusahaan', '', 'view_own')
            && get_option('allow_staff_view_perusahaan_assigned') == 0
        ) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('perusahaan', 'tables/perusahaan_small_table'));
        
    }

    public function perusahaan_relations($rel_id, $rel_type)
    {
        $this->app->get_table_data(module_views_path('perusahaan', 'tables/perusahaan_relations', [
            'rel_id'   => $rel_id,
            'rel_type' => $rel_type,
        ]));
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->perusahaan_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('perusahaan', '', 'delete')) {
            $this->perusahaan_model->clear_signature($id);
        }

        redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id .'#' . $id));
    }

    public function sync_data()
    {
        if (has_permission('perusahaan', '', 'create') || has_permission('perusahaan', '', 'edit')) {
            $has_permission_view = has_permission('perusahaan', '', 'view');

            $this->db->where('rel_id', $this->input->post('rel_id'));
            $this->db->where('rel_type', $this->input->post('rel_type'));

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $address = trim($this->input->post('address'));
            $address = nl2br($address);
            $this->db->update(db_prefix() . 'perusahaan', [
                'phone'   => $this->input->post('phone'),
                'zip'     => $this->input->post('zip'),
                'country' => $this->input->post('country'),
                'state'   => $this->input->post('state'),
                'address' => $address,
                'city'    => $this->input->post('city'),
            ]);

            if ($this->db->affected_rows() > 0) {
                echo json_encode([
                    'message' => _l('all_data_synced_successfully'),
                ]);
            } else {
                echo json_encode([
                    'message' => _l('sync_perusahaan_up_to_date'),
                ]);
            }
        }
    }

    public function perusahaan($id = '')
    {
        if ($this->input->post()) {
            $perusahaan_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('perusahaan', '', 'create')) {
                    access_denied('perusahaan');
                }
                $id = $this->perusahaan_model->add($perusahaan_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('perusahaan')));
                    if ($this->set_perusahaan_pipeline_autoload($id)) {
                        redirect(admin_url('perusahaan'));
                    } else {
                        redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
                    }
                }
            } else {
                if (!has_permission('perusahaan', '', 'edit')) {
                    access_denied('perusahaan');
                }
                $success = $this->perusahaan_model->update($perusahaan_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('perusahaan')));
                }
                if ($this->set_perusahaan_pipeline_autoload($id)) {
                    redirect(admin_url('perusahaan'));
                } else {
                    redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('perusahaan_lowercase'));
        } else {
            $data['perusahaan'] = $this->perusahaan_model->get($id);

            if (!$data['perusahaan'] || !user_can_view_perusahaan($id)) {
                blank_page(_l('perusahaan_not_found'));
            }

            $data['perusahaan']    = $data['perusahaan'];
            $data['is_perusahaan'] = true;
            $title               = _l('edit', _l('perusahaan_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['statuses']      = $this->perusahaan_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/perusahaan/perusahaan', $data);
    }


    public function add_perusahaan($id = '')
    {
        if ($this->input->post()) {
            $perusahaan_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('perusahaan', '', 'create')) {
                    access_denied('perusahaan');
                }
                $id = $this->perusahaan_model->add($perusahaan_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('perusahaan')));
                    if ($this->set_perusahaan_pipeline_autoload($id)) {
                        redirect(admin_url('perusahaan'));
                    } else {
                        redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
                    }
                }
            } else {
                if (!has_permission('perusahaan', '', 'edit')) {
                    access_denied('perusahaan');
                }
                $success = $this->perusahaan_model->update($perusahaan_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('perusahaan')));
                }
                if ($this->set_perusahaan_pipeline_autoload($id)) {
                    redirect(admin_url('perusahaan'));
                } else {
                    redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('perusahaan_lowercase'));
        } else {
            $data['perusahaan'] = $this->perusahaan_model->get($id);

            if (!$data['perusahaan'] || !user_can_view_perusahaan($id)) {
                blank_page(_l('perusahaan_not_found'));
            }

            $data['perusahaan']    = $data['perusahaan'];
            $data['is_perusahaan'] = true;
            $title               = _l('edit', _l('perusahaan_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['statuses']      = $this->perusahaan_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/perusahaan/perusahaan', $data);
    }

    public function edit_perusahaan($id)
    {
        if ($this->input->post()) {
            $perusahaan_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('perusahaan', '', 'create')) {
                    access_denied('perusahaan');
                }
                $id = $this->perusahaan_model->add($perusahaan_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('perusahaan')));
                    if ($this->set_perusahaan_pipeline_autoload($id)) {
                        redirect(admin_url('perusahaan'));
                    } else {
                        redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
                    }
                }
            } else {
                if (!has_permission('perusahaan', '', 'edit')) {
                    access_denied('perusahaan');
                }
                $success = $this->perusahaan_model->update($perusahaan_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('perusahaan')));
                }
                if ($this->set_perusahaan_pipeline_autoload($id)) {
                    redirect(admin_url('perusahaan'));
                } else {
                    redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('perusahaan_lowercase'));
        } else {
            $data['perusahaan'] = $this->perusahaan_model->get($id);

            if (!$data['perusahaan'] || !user_can_view_perusahaan($id)) {
                blank_page(_l('perusahaan_not_found'));
            }

            $data['perusahaan']    = $data['perusahaan'];
            $data['is_perusahaan'] = true;
            $title               = _l('edit', _l('perusahaan_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['statuses']      = $this->perusahaan_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/perusahaan/perusahaan', $data);
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/perusahaan/templates/' . $name, [], true);
    }

    public function send_expiry_reminder($id)
    {
        $canView = user_can_view_perusahaan($id);
        if (!$canView) {
            access_denied('perusahaan');
        } else {
            if (!has_permission('perusahaan', '', 'view') && !has_permission('perusahaan', '', 'view_own') && $canView == false) {
                access_denied('perusahaan');
            }
        }

        $success = $this->perusahaan_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_perusahaan_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
        }
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'perusahaan', get_acceptance_info_array(true));
        }

        redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
    }

    public function pdf($id)
    {
        if (!$id) {
            redirect(admin_url('perusahaan'));
        }

        $canView = user_can_view_perusahaan($id);
        if (!$canView) {
            access_denied('perusahaan');
        } else {
            if (!has_permission('perusahaan', '', 'view') && !has_permission('perusahaan', '', 'view_own') && $canView == false) {
                access_denied('perusahaan');
            }
        }

        $perusahaan = $this->perusahaan_model->get($id);

        try {
            $pdf = perusahaan_pdf($perusahaan);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $perusahaan_number = format_perusahaan_number($id);
        $pdf->Output($perusahaan_number . '.pdf', $type);
    }

    public function get_perusahaan_data_ajax($id, $to_return = false)
    {
        if (!has_permission('perusahaan', '', 'view') && !has_permission('perusahaan', '', 'view_own') && get_option('allow_staff_view_perusahaan_assigned') == 0) {
            echo _l('access_denied');
            die;
        }

        $perusahaan = $this->perusahaan_model->get($id, [], true);

        if (!$perusahaan || !user_can_view_perusahaan($id)) {
            echo _l('perusahaan_not_found');
            die;
        }

        
        //$this->perusahaan_mail_template->set_rel_id($perusahaan->id);
        include_once(module_libs_path(PERUSAHAAN_MODULE_NAME) . 'mails/Perusahaan_send_to_customer.php');

        //$data = perusahaan_prepare_mail_preview_data('perusahaan_send_to_customer', $perusahaan->email);

        $merge_fields = [];

        $merge_fields[] = [
            [
                'name' => 'Items Table',
                'key'  => '{perusahaan_items}',
            ],
        ];

        $merge_fields = array_merge($merge_fields, $this->app_merge_fields->get_flat('perusahaan', 'other', '{email_signature}'));

        $data['perusahaan_statuses']     = $this->perusahaan_model->get_statuses();
        $data['members']               = $this->staff_model->get('', ['active' => 1]);
        $data['perusahaan_merge_fields'] = $merge_fields;
        $data['perusahaan']              = $perusahaan;
        $data['totalNotes']            = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'perusahaan']);

        if ($to_return == false) {
            $this->load->view('admin/perusahaan/perusahaan_preview_template', $data);
        } else {
            return $this->load->view('admin/perusahaan/perusahaan_preview_template', $data, true);
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_perusahaan($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'perusahaan', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_perusahaan($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'perusahaan');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function convert_to_perusahaan($id)
    {
        if (!has_permission('perusahaan', '', 'create')) {
            access_denied('perusahaan');
        }
        if ($this->input->post()) {
            $this->load->model('perusahaan_model');
            $perusahaan_id = $this->perusahaan_model->add($this->input->post());
            if ($perusahaan_id) {
                set_alert('success', _l('perusahaan_converted_to_perusahaan_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'perusahaan', [
                    'perusahaan_id' => $perusahaan_id,
                    'status'      => 3,
                ]);
                log_activity('Perusahaan Converted to Estimate [EstimateID: ' . $perusahaan_id . ', PerusahaanID: ' . $id . ']');

                hooks()->do_action('perusahaan_converted_to_perusahaan', ['perusahaan_id' => $id, 'perusahaan_id' => $perusahaan_id]);

                redirect(admin_url('perusahaan/perusahaan/' . $perusahaan_id));
            } else {
                set_alert('danger', _l('perusahaan_converted_to_perusahaan_fail'));
            }
            if ($this->set_perusahaan_pipeline_autoload($id)) {
                redirect(admin_url('perusahaan'));
            } else {
                redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
            }
        }
    }

    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if ($this->input->post()) {
            $this->load->model('invoices_model');
            $invoice_id = $this->invoices_model->add($this->input->post());
            if ($invoice_id) {
                set_alert('success', _l('perusahaan_converted_to_invoice_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'perusahaan', [
                    'invoice_id' => $invoice_id,
                    'status'     => 3,
                ]);
                log_activity('Perusahaan Converted to Invoice [InvoiceID: ' . $invoice_id . ', PerusahaanID: ' . $id . ']');
                hooks()->do_action('perusahaan_converted_to_invoice', ['perusahaan_id' => $id, 'invoice_id' => $invoice_id]);
                redirect(admin_url('invoices/invoice/' . $invoice_id));
            } else {
                set_alert('danger', _l('perusahaan_converted_to_invoice_fail'));
            }
            if ($this->set_perusahaan_pipeline_autoload($id)) {
                redirect(admin_url('perusahaan'));
            } else {
                redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
            }
        }
    }

    public function get_invoice_convert_data($id)
    {
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $this->load->model('taxes_model');
        $data['taxes']         = $this->taxes_model->get();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']          = $this->staff_model->get('', ['active' => 1]);
        $data['perusahaan']       = $this->perusahaan_model->get($id);
        $data['billable_tasks'] = [];
        $data['add_items']      = $this->_parse_items($data['perusahaan']);

        if ($data['perusahaan']->rel_type == 'lead') {
            $this->db->where('leadid', $data['perusahaan']->rel_id);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['perusahaan']->rel_id;
        }
        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'perusahaan',
            'rel_id'     => $id,
        ];
        $this->load->view('admin/perusahaan/invoice_convert_template', $data);
    }

    public function get_perusahaan_convert_data($id)
    {
        $this->load->model('taxes_model');
        $data['taxes']         = $this->taxes_model->get();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['perusahaan']  = $this->perusahaan_model->get($id);
        $data['add_items'] = $this->_parse_items($data['perusahaan']);

        $this->load->model('perusahaan_model');
        $data['perusahaan_statuses'] = $this->perusahaan_model->get_statuses();
        if ($data['perusahaan']->rel_type == 'lead') {
            $this->db->where('leadid', $data['perusahaan']->rel_id);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['perusahaan']->rel_id;
        }

        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'perusahaan',
            'rel_id'     => $id,
        ];

        $this->load->view('admin/perusahaan/perusahaan_convert_template', $data);
    }

    private function _parse_items($perusahaan)
    {
        $items = [];
        foreach ($perusahaan->items as $item) {
            $taxnames = [];
            $taxes    = get_perusahaan_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                array_push($taxnames, $tax['taxname']);
            }
            $item['taxname']        = $taxnames;
            $item['parent_item_id'] = $item['id'];
            $item['id']             = 0;
            $items[]                = $item;
        }

        return $items;
    }

    /* Send perusahaan to email */
    public function send_to_email($id)
    {
        $canView = user_can_view_perusahaan($id);
        if (!$canView) {
            access_denied('perusahaan');
        } else {
            if (!has_permission('perusahaan', '', 'view') && !has_permission('perusahaan', '', 'view_own') && $canView == false) {
                access_denied('perusahaan');
            }
        }

        if ($this->input->post()) {
            try {
                $success = $this->perusahaan_model->send_perusahaan_to_email(
                    $id,
                    $this->input->post('attach_pdf'),
                    $this->input->post('cc')
                );
            } catch (Exception $e) {
                $message = $e->getMessage();
                echo $message;
                if (strpos($message, 'Unable to get the size of the image') !== false) {
                    show_pdf_unable_to_get_image_size_error();
                }
                die;
            }

            if ($success) {
                set_alert('success', _l('perusahaan_sent_to_email_success'));
            } else {
                set_alert('danger', _l('perusahaan_sent_to_email_fail'));
            }

            if ($this->set_perusahaan_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('perusahaan', '', 'create')) {
            access_denied('perusahaan');
        }
        $new_id = $this->perusahaan_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('perusahaan_copy_success'));
            $this->set_perusahaan_pipeline_autoload($new_id);
            redirect(admin_url('perusahaan/perusahaan/' . $new_id));
        } else {
            set_alert('success', _l('perusahaan_copy_fail'));
        }
        if ($this->set_perusahaan_pipeline_autoload($id)) {
            redirect(admin_url('perusahaan'));
        } else {
            redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('perusahaan', '', 'edit')) {
            access_denied('perusahaan');
        }
        $success = $this->perusahaan_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('perusahaan_status_changed_success'));
        } else {
            set_alert('danger', _l('perusahaan_status_changed_fail'));
        }
        if ($this->set_perusahaan_pipeline_autoload($id)) {
            redirect(admin_url('perusahaan'));
        } else {
            redirect(admin_url('perusahaan/list_perusahaan/' . $id .'#' . $id));
        }
    }

    public function delete($id)
    {
        if (!has_permission('perusahaan', '', 'delete')) {
            access_denied('perusahaan');
        }
        $response = $this->perusahaan_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('perusahaan')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('perusahaan_lowercase')));
        }
        redirect(admin_url('perusahaan'));
    }

    public function get_relation_data_values($rel_id, $rel_type)
    {
        echo json_encode($this->perusahaan_model->get_relation_data_values($rel_id, $rel_type));
    }

    public function add_perusahaan_comment()
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->perusahaan_model->add_comment($this->input->post()),
            ]);
        }
    }

    public function edit_comment($id)
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->perusahaan_model->edit_comment($this->input->post(), $id),
                'message' => _l('comment_updated_successfully'),
            ]);
        }
    }

    public function get_perusahaan_comments($id)
    {
        $data['comments'] = $this->perusahaan_model->get_comments($id);
        $this->load->view('admin/perusahaan/comments_template', $data);
    }

    public function remove_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'perusahaan_comments')->row();
        if ($comment) {
            if ($comment->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode([
                    'success' => false,
                ]);
                die;
            }
            echo json_encode([
                'success' => $this->perusahaan_model->remove_comment($id),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function save_perusahaan_data()
    {
        if (!has_permission('perusahaan', '', 'edit') && !has_permission('perusahaan', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('perusahaan_id'));
        $this->db->update(db_prefix() . 'perusahaan', [
            'content' => html_purify($this->input->post('content', false)),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = _l('updated_successfully', _l('perusahaan'));

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    // Pipeline
    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'perusahaan_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('perusahaan'));
        }
    }

    public function pipeline_open($id)
    {
        if (has_permission('perusahaan', '', 'view') || has_permission('perusahaan', '', 'view_own') || get_option('allow_staff_view_perusahaan_assigned') == 1) {
            $data['perusahaan']      = $this->get_perusahaan_data_ajax($id, true);
            $data['perusahaan_data'] = $this->perusahaan_model->get($id);
            $this->load->view('admin/perusahaan/pipeline/perusahaan', $data);
        }
    }

    public function update_pipeline()
    {
        if (has_permission('perusahaan', '', 'edit')) {
            $this->perusahaan_model->update_pipeline($this->input->post());
        }
    }

    public function get_pipeline()
    {
        if (has_permission('perusahaan', '', 'view') || has_permission('perusahaan', '', 'view_own') || get_option('allow_staff_view_perusahaan_assigned') == 1) {
            $data['statuses'] = $this->perusahaan_model->get_statuses();
            $this->load->view('admin/perusahaan/pipeline/pipeline', $data);
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $daftar__perusahaan = (new PerusahaanPipeline($status))
        ->search($this->input->get('search'))
        ->sortBy(
            $this->input->get('sort_by'),
            $this->input->get('sort')
        )
        ->page($page)->get();

        foreach ($daftar__perusahaan as $perusahaan) {
            $this->load->view('admin/perusahaan/pipeline/_kanban_card', [
                'perusahaan' => $perusahaan,
                'status'   => $status,
            ]);
        }
    }

    public function set_perusahaan_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('perusahaan_pipeline') && $this->session->userdata('perusahaan_pipeline') == 'true') {
            $this->session->set_flashdata('perusahaan_id', $id);

            return true;
        }

        return false;
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('perusahaan_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('perusahaan_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }
}
