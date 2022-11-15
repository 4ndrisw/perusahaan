<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Jenis_pesawat extends AdminController
{
    private $not_importable_fields = ['id'];

    public function __construct()
    {
        parent::__construct();
        include_once(APP_MODULES_PATH . PERUSAHAAN_MODULE_NAME . '/models/jenis_pesawat_model.php');
        $this->load->model('jenis_pesawat_model');
    }

    /* List all available items */
    public function index()
    {
        if (!has_permission('items', '', 'view')) {
            access_denied('Invoice Items');
        }

        $data['kelompok_alat'] = $this->jenis_pesawat_model->get_groups();

        $data['title'] = _l('jenis_pesawat');
        $this->load->view('admin/jenis_pesawat/manage', $data);
    }

    public function table()
    {
        if (!has_permission('items', '', 'view')) {
            ajax_access_denied();
        }
        //$this->app->get_table_data(module_views_path('perusahaan', 'admin/tables/table'));
        $this->app->get_table_data(module_views_path(PERUSAHAAN_MODULE_NAME, 'admin/tables/jenis_pesawat'));
    }

    /* Edit or update items / ajax request /*/
    public function manage()
    {
        if (has_permission('items', '', 'view')) {
            if ($this->input->post()) {
                $data = $this->input->post();
                if ($data['itemid'] == '') {
                    if (!has_permission('items', '', 'create')) {
                        header('HTTP/1.0 400 Bad error');
                        echo _l('access_denied');
                        die;
                    }
                    $id      = $this->jenis_pesawat_model->add($data);
                    $success = false;
                    $message = '';
                    if ($id) {
                        $success = true;
                        $message = _l('added_successfully', _l('sales_item'));
                    }
                    echo json_encode([
                        'success' => $success,
                        'message' => $message,
                        'item'    => $this->jenis_pesawat_model->get($id),
                    ]);
                } else {
                    if (!has_permission('items', '', 'edit')) {
                        header('HTTP/1.0 400 Bad error');
                        echo _l('access_denied');
                        die;
                    }
                    $success = $this->jenis_pesawat_model->edit($data);
                    $message = '';
                    if ($success) {
                        $message = _l('updated_successfully', _l('sales_item'));
                    }
                    echo json_encode([
                        'success' => $success,
                        'message' => $message,
                    ]);
                }
            }
        }
    }

    public function import()
    {
        if (!has_permission('items', '', 'create')) {
            access_denied('Items Import');
        }

        $this->load->library('import/import_items', [], 'import');

        $this->import->setDatabaseFields($this->db->list_fields(db_prefix() . 'items'))
            ->setCustomFields(get_custom_fields('items'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if (
            $this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != ''
        ) {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['title'] = _l('import');
        $this->load->view('admin/jenis_pesawat/import', $data);
    }

    public function add_group()
    {
        if ($this->input->post() && has_permission('items', '', 'create')) {
            $this->jenis_pesawat_model->add_group($this->input->post());
            set_alert('success', _l('added_successfully', _l('kelompok_alat')));
        }
    }

    public function update_group($id)
    {
        if ($this->input->post() && has_permission('items', '', 'edit')) {
            $this->jenis_pesawat_model->edit_group($this->input->post(), $id);
            set_alert('success', _l('updated_successfully', _l('kelompok_alat')));
        }
    }

    public function delete_group($id)
    {
        if (has_permission('items', '', 'delete')) {
            if ($this->jenis_pesawat_model->delete_group($id)) {
                set_alert('success', _l('deleted', _l('kelompok_alat')));
            }
        }
        redirect(admin_url('perusahaan/jenis_pesawat?groups_modal=true'));
    }

    /* Delete item*/
    public function delete($id)
    {
        if (!has_permission('items', '', 'delete')) {
            access_denied('Invoice Items');
        }

        if (!$id) {
            redirect(admin_url('perusahaan/jenis_pesawat'));
        }

        $response = $this->jenis_pesawat_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('jenis_pesawat_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('jenis_pesawat')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('jenis_pesawat_lowercase')));
        }
        redirect(admin_url('perusahaan/jenis_pesawat'));
    }

    public function bulk_action()
    {
        hooks()->do_action('before_do_bulk_action_for_items');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids                   = $this->input->post('ids');
            $has_permission_delete = has_permission('items', '', 'delete');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($has_permission_delete) {
                            if ($this->jenis_pesawat_model->delete($id)) {
                                $total_deleted++;
                            }
                        }
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_items_deleted', $total_deleted));
        }
    }

    public function search()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            echo json_encode($this->jenis_pesawat_model->search($this->input->post('q')));
        }
    }

    /* Get item by id / ajax */
    public function get_item_by_id($id)
    {
        if ($this->input->is_ajax_request()) {
            $item                     = $this->jenis_pesawat_model->get($id);
            $item->long_description   = nl2br($item->long_description);
            $item->custom_fields_html = render_custom_fields('items', $id, [], ['items_pr' => true]);
            $item->custom_fields      = [];

            $cf = get_custom_fields('items');

            foreach ($cf as $custom_field) {
                $val = get_custom_field_value($id, $custom_field['id'], 'items_pr');
                if ($custom_field['type'] == 'textarea') {
                    $val = clear_textarea_breaks($val);
                }
                $custom_field['value'] = $val;
                $item->custom_fields[] = $custom_field;
            }

            echo json_encode($item);
        }
    }

    /* Copy Item */
    public function copy($id)
    {
        if (!has_permission('items', '', 'create')) {
            access_denied('Create Item');
        }

        $data = (array) $this->jenis_pesawat_model->get($id);

        $id = $this->jenis_pesawat_model->copy($data);

        if ($id) {
            set_alert('success', _l('item_copy_success'));
            return redirect(admin_url('perusahaan/jenis_pesawat?id=' . $id));
        }

        set_alert('warning', _l('item_copy_fail'));
        return redirect(admin_url('perusahaan/jenis_pesawat'));
    }
}
