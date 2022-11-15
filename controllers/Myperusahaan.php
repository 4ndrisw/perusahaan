<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Myperusahaan extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('perusahaan_model');
        $this->load->model('currencies_model');
        //include_once(module_libs_path(PERUSAHAAN_MODULE_NAME) . 'mails/Perusahaan_mail_template.php');
        //$this->load->library('module_name/library_name');
        //$this->load->library('perusahaan_mail_template');
        //include_once(module_libs_path(PERUSAHAAN_MODULE_NAME) . 'mails/Perusahaan_send_to_customer.php');
        //$this->load->library('module_name/library_name');
        //$this->load->library('perusahaan_send_to_customer');


    }

    /* Get all perusahaan in case user go on index page */
    public function list($id = '')
    {
        
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('perusahaan', 'admin/tables/table'));
        }
        $contact_id = get_contact_user_id();
        $user_id = get_user_id_by_contact_id($contact_id);
        $client = $this->clients_model->get($user_id);
        
        $data['daftar_perusahaan'] = $this->perusahaan_model->get_client_perusahaan($client);
        $data['client'] = $client;
        $data['perusahaan_statuses'] = $this->perusahaan_model->get_statuses();
        $data['perusahaanid']            = $id;
        $data['title']                 = _l('perusahaan_tracking');
        
        $data['bodyclass'] = 'perusahaan';
        $this->data($data);
        $this->view('themes/'. active_clients_theme() .'/views/perusahaan/perusahaan');
        $this->layout();

    }

    public function show($id, $hash)
    {
        check_perusahaan_restrictions($id, $hash);
        $perusahaan = $this->perusahaan_model->get($id);

        if ($perusahaan->rel_type == 'customer' && !is_client_logged_in()) {
            load_client_language($perusahaan->clientid);
        } else if ($perusahaan->rel_type == 'lead') {
            load_lead_language($perusahaan->clientid);
        }

        $identity_confirmation_enabled = get_option('perusahaan_accept_identity_confirmation');
        if ($this->input->post()) {
            $action = $this->input->post('action');
            switch ($action) {
                case 'perusahaan_comment':
                    // comment is blank
                    if (!$this->input->post('content')) {
                        redirect($this->uri->uri_string());
                    }
                    $data               = $this->input->post();
                    $data['perusahaan_id'] = $id;
                    $this->perusahaan_model->add_comment($data, true);
                    redirect($this->uri->uri_string() . '?tab=discussion');

                    break;
                case 'accept_perusahaan':
                    $success = $this->perusahaan_model->mark_action_status(3, $id, true);
                    if ($success) {
                        process_digital_signature_image($this->input->post('signature', false), PROPOSAL_ATTACHMENTS_FOLDER . $id);

                        $this->db->where('id', $id);
                        $this->db->update(db_prefix() . 'perusahaan', get_acceptance_info_array());
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
                case 'decline_perusahaan':
                    $success = $this->perusahaan_model->mark_action_status(2, $id, true);
                    if ($success) {
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
            }
        }

        $number_word_lang_clientid = 'unknown';
        if ($perusahaan->rel_type == 'customer') {
            $number_word_lang_clientid = $perusahaan->clientid;
        }
        $this->load->library('app_number_to_word', [
            'clientid' => $number_word_lang_clientid,
        ], 'numberword');

        $this->disableNavigation();
        $this->disableSubMenu();

        $data['title']     = $perusahaan->subject;
        $data['can_be_accepted']               = false;
        $data['perusahaan']  = hooks()->apply_filters('perusahaan_html_pdf_data', $perusahaan);
        $data['bodyclass'] = 'perusahaan perusahaan-view';

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }

        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');

        $data['comments'] = $this->perusahaan_model->get_comments($id);
        add_views_tracking('perusahaan', $id);
        hooks()->do_action('perusahaan_html_viewed', $id);
        hooks()->add_action('app_admin_head', 'perusahaan_head_component');

        $this->app_css->remove('reset-css', 'customers-area-default');

        $data                      = hooks()->apply_filters('perusahaan_customers_area_view_data', $data);
        no_index_customers_area();
        $this->data($data);

        $this->view('themes/' . active_clients_theme() . '/views/perusahaan/perusahaan_html');

        $this->layout();
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
        $perusahaan_number = format_perusahaan_number($id);
        /*
        echo '<pre>';
        var_dump($perusahaan);
        echo '</pre>';
        die();
        */

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

        $pdf->Output($perusahaan_number . '.pdf', $type);
    }
}
