<?php

use app\services\AbstractKanban;
use app\services\perusahaan\PerusahaanPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Perusahaan_model extends App_Model
{
    private $statuses;

    private $copy = false;

    public function __construct()
    {
        parent::__construct();
        $this->statuses = hooks()->apply_filters('before_set_perusahaan_statuses', [
            6,
            4,
            1,
            5,
            2,
            3,
        ]);
    }

    public function get_statuses()
    {
        return $this->statuses;
    }

    public function get_sale_agents()
    {
        return $this->db->query('SELECT DISTINCT(assigned) as sale_agent FROM ' . db_prefix() . 'perusahaan WHERE assigned != 0')->result_array();
    }

    public function get_perusahaan_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'perusahaan')->result_array();
    }


    /**
     * Performs perusahaan totals status
     * @param array $data
     * @return array
     */
    public function get_perusahaan_total($data)
    {
        $statuses            = $this->get_statuses();
        $has_permission_view = has_permission('perusahaan', '', 'view');
        $this->load->model('currencies_model');
        if (isset($data['currency'])) {
            $currencyid = $data['currency'];
        } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
            $currencyid = $this->clients_model->get_customer_default_currency($data['customer_id']);
            if ($currencyid == 0) {
                $currencyid = $this->currencies_model->get_base_currency()->id;
            }
        } elseif (isset($data['project_id']) && $data['project_id'] != '') {
            $this->load->model('projects_model');
            $currencyid = $this->projects_model->get_currency($data['project_id'])->id;
        } else {
            $currencyid = $this->currencies_model->get_base_currency()->id;
        }

        $currency = get_currency($currencyid);
        $where    = '';
        if (isset($data['customer_id']) && $data['customer_id'] != '') {
            $where = ' AND clientid=' . $data['customer_id'];
        }

        if (isset($data['project_id']) && $data['project_id'] != '') {
            $where .= ' AND project_id=' . $data['project_id'];
        }

        if (!$has_permission_view) {
            $where .= ' AND ' . get_perusahaan_where_sql_for_staff(get_staff_user_id());
        }

        $sql = 'SELECT';
        foreach ($statuses as $equipment_status) {
            $sql .= '(SELECT SUM(total) FROM ' . db_prefix() . 'perusahaan WHERE status=' . $equipment_status;
            $sql .= ' AND currency =' . $this->db->escape_str($currencyid);
            if (isset($data['years']) && count($data['years']) > 0) {
                $sql .= ' AND YEAR(date) IN (' . implode(', ', array_map(function ($year) {
                    return get_instance()->db->escape_str($year);
                }, $data['years'])) . ')';
            } else {
                $sql .= ' AND YEAR(date) = ' . date('Y');
            }
            $sql .= $where;
            $sql .= ') as "' . $equipment_status . '",';
        }

        $sql     = substr($sql, 0, -1);
        $result  = $this->db->query($sql)->result_array();
        $_result = [];
        $i       = 1;
        foreach ($result as $key => $val) {
            foreach ($val as $status => $total) {
                $_result[$i]['total']         = $total;
                $_result[$i]['symbol']        = $currency->symbol;
                $_result[$i]['currency_name'] = $currency->name;
                $_result[$i]['status']        = $status;
                $i++;
            }
        }
        $_result['currencyid'] = $currencyid;

        return $_result;
    }

    /**
     * Inserting new perusahaan function
     * @param mixed $data $_POST data
     */
    public function add($data)
    {
        $data['allow_comments'] = isset($data['allow_comments']) ? 1 : 0;

        $save_and_send = isset($data['save_and_send']);

        $tags = isset($data['tags']) ? $data['tags'] : '';

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $perusahaanRequestID = false;
        if (isset($data['perusahaan_request_id'])) {
            $perusahaanRequestID = $data['perusahaan_request_id'];
            unset($data['perusahaan_request_id']);
        }

        $data['lokasi'] = trim($data['lokasi']);
        $data['lokasi'] = nl2br($data['lokasi']);

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom']   = get_staff_user_id();
        $data['hash']        = app_generate_hash();
        /*
        if (empty($data['rel_type'])) {
            unset($data['rel_type']);
            unset($data['clientid']);
        } else {
            if (empty($data['clientid'])) {
                unset($data['rel_type']);
                unset($data['clientid']);
            }
        }
        */

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        if ($this->copy == false) {
            $data['content'] = '{perusahaan_items}';
        }

        $hook = hooks()->apply_filters('before_create_perusahaan', [
            'data'  => $data,
        ]);

        $data  = $hook['data'];
        $items = $hook['items'];
        unset($data['tags'],$data['item_select'], $data['description'], $data['long_description'],
              $data['quantity'], $data['unit'],$data['rate']
             );
        $this->db->insert(db_prefix() . 'perusahaan', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if ($perusahaanRequestID !== false && $perusahaanRequestID != '') {
                $this->load->model('perusahaan_request_model');
                $completedStatus = $this->perusahaan_request_model->get_status_by_flag('completed');
                $this->perusahaan_request_model->update_request_status([
                    'requestid' => $perusahaanRequestID,
                    'status'    => $completedStatus->id,
                ]);
            }

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            handle_tags_save($tags, $insert_id, 'perusahaan');

            foreach ($items as $key => $item) {
                if ($itemid = add_new_sales_item_post($item, $insert_id, 'perusahaan')) {
                    _maybe_insert_post_item_tax($itemid, $item, $insert_id, 'perusahaan');
                }
            }
            
            $perusahaan = $this->get($insert_id);
            if ($perusahaan->assigned != 0) {
                if ($perusahaan->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description'     => 'not_perusahaan_assigned_to_you',
                        'touserid'        => $perusahaan->assigned,
                        'fromuserid'      => get_staff_user_id(),
                        'link'            => 'perusahaan/list_perusahaan/' . $insert_id .'#' . $insert_id,
                        'additional_data' => serialize([
                            $perusahaan->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$perusahaan->assigned]);
                    }
                }
            }

            if ($data['rel_type'] == 'lead') {
                $this->load->model('leads_model');
                $this->leads_model->log_lead_activity($data['clientid'], 'not_lead_activity_created_perusahaan', false, serialize([
                    '<a href="' . admin_url('perusahaan/list_perusahaan/' . $insert_id) . '" target="_blank">' . $data['subject'] . '</a>',
                ]));
            }

            update_sales_total_tax_column($insert_id, 'perusahaan', db_prefix() . 'perusahaan');

            log_activity('New Perusahaan Created [ID: ' . $insert_id . ']');

            if ($save_and_send === true) {
                $this->send_perusahaan_to_email($insert_id);
            }

            hooks()->do_action('perusahaan_created', $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Update perusahaan
     * @param  mixed $data $_POST data
     * @param  mixed $id   perusahaan id
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;

        $data['allow_comments'] = isset($data['allow_comments']) ? 1 : 0;

        $origin = $this->get($id);

        $save_and_send = isset($data['save_and_send']);

        /*
        if (empty($data['rel_type'])) {
            $data['clientid']   = null;
            $data['rel_type'] = '';
        } else {
            if (empty($data['clientid'])) {
                $data['clientid']   = null;
                $data['rel_type'] = '';
            }
        }
        */

        //$data['date'] = _d($data['date']);
        //$data['open_till'] = _d($data['open_till']);
        
        $data['lokasi'] = trim($data['lokasi']);
        $data['lokasi'] = nl2br($data['lokasi']);

        $hook = hooks()->apply_filters('before_perusahaan_updated', [
            'data'          => $data,
            'removed_items' => isset($data['removed_items']) ? $data['removed_items'] : [],
        ], $id);

        unset($data['description']);
        unset($data['long_description']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'perusahaan', $data);
        if ($this->db->affected_rows() > 0) {

            $perusahaan = $this->get($id);            
            if ($origin->subject != $perusahaan->subject) {
                $this->log_perusahaan_activity($origin->id, 'perusahaan_activity_subject_changed', false, serialize([
                    $origin->subject,
                    $perusahaan->subject,
                ]));
            }

            $affectedRows++;
            if ($origin->assigned != $perusahaan->assigned) {
                //if ($perusahaan->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description'     => 'not_perusahaan_assigned_to_you',
                        'touserid'        => $perusahaan->assigned,
                        'fromuserid'      => get_staff_user_id(),
                        'link'            => 'perusahaan/list_perusahaan/' . $id,
                        'additional_data' => serialize([
                            $perusahaan->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$perusahaan->assigned,get_staff_user_id()]);
                    }
                //}
            }
        }

        if ($affectedRows > 0) {
            update_sales_total_tax_column($id, 'perusahaan', db_prefix() . 'perusahaan');
            log_activity('Perusahaan Updated [ID:' . $id . ']');
        }

        if ($save_and_send === true) {
            $this->send_perusahaan_to_email($id);
        }

        if ($affectedRows > 0) {
            hooks()->do_action('after_perusahaan_updated', $id);

            return true;
        }

        return false;
    }

    /**
     * Get perusahaan
     * @param  mixed $id perusahaan id OPTIONAL
     * @return mixed
     */
    public function get($id = '', $where = [], $for_editor = false)
    {
        $this->db->where($where);

        if (is_client_logged_in()) {
            $this->db->where('status !=', 0);
        }

        $this->db->select('*,' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'perusahaan.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->from(db_prefix() . 'perusahaan');
        $this->db->join(db_prefix() . 'currencies', db_prefix() . 'currencies.id = ' . db_prefix() . 'perusahaan.currency', 'left');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'perusahaan.id', $id);
            $perusahaan = $this->db->get()->row();
            if ($perusahaan) {
                $perusahaan->attachments                           = $this->get_attachments($id);
                //$perusahaan->items                                 = get_items_by_type('perusahaan', $id);
                $perusahaan->visible_attachments_to_customer_found = false;
                foreach ($perusahaan->attachments as $attachment) {
                    if ($attachment['visible_to_customer'] == 1) {
                        $perusahaan->visible_attachments_to_customer_found = true;

                        break;
                    }
                }
                /*
                 *next_feature
                if ($for_editor == false) {
                    $perusahaan = parse_perusahaan_content_merge_fields($perusahaan);
                }
                */
            }

            $perusahaan->client = $this->clients_model->get($perusahaan->clientid);

            if (!$perusahaan->client) {
                $perusahaan->client          = new stdClass();
                $perusahaan->client->company = $perusahaan->deleted_customer_name;
            }
            
            return $perusahaan;
        }

        return $this->db->get()->result_array();
    }


    /**
     * Get jenis_pesawat
     * @param  mixed $id perusahaan id OPTIONAL
     * @return mixed
     */
    public function get_jenis_pesawat($id = '', $where = [], $for_editor = false)
    {
        $this->db->where($where);

        if (is_client_logged_in()) {
            $this->db->where('status !=', 0);
        }

        $this->db->select(['id', 'description']);
        $this->db->from(db_prefix() . 'jenis_pesawat');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'perusahaan.id', $id);
            $jenis_pesawat = $this->db->get()->row();
            $jenis_pesawat->category = $this->perusahaan_model->get_category($jenis_pesawat->group_id);
            return $jenis_pesawat;
        }


        return $this->db->get()->result_array();
    }

    public function clear_signature($id)
    {
        $this->db->select('signature');
        $this->db->where('id', $id);
        $perusahaan = $this->db->get(db_prefix() . 'perusahaan')->row();

        if ($perusahaan) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'perusahaan', ['signature' => null]);

            if (!empty($perusahaan->signature)) {
                unlink(get_upload_path_by_type('perusahaan') . $id . '/' . $perusahaan->signature);
            }

            return true;
        }

        return false;
    }

    public function update_pipeline($data)
    {
        $this->mark_action_status($data['status'], $data['perusahaan_id']);
        AbstractKanban::updateOrder($data['order'], 'pipeline_order', 'perusahaan', $data['status']);
    }

    public function get_attachments($perusahaan_id, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $perusahaan_id);
        }
        $this->db->where('rel_type', 'perusahaan');
        $result = $this->db->get(db_prefix() . 'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     *  Delete perusahaan attachment
     * @param   mixed $id  attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('perusahaan') . $attachment->clientid . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Perusahaan Attachment Deleted [ID: ' . $attachment->clientid . ']');
            }
            if (is_dir(get_upload_path_by_type('perusahaan') . $attachment->clientid)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('perusahaan') . $attachment->clientid);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('perusahaan') . $attachment->clientid);
                }
            }
        }

        return $deleted;
    }

    /**
     * Add perusahaan comment
     * @param mixed  $data   $_POST comment data
     * @param boolean $client is request coming from the client side
     */
    public function add_comment($data, $client = false)
    {
        if (is_staff_logged_in()) {
            $client = false;
        }

        if (isset($data['action'])) {
            unset($data['action']);
        }
        $data['dateadded'] = date('Y-m-d H:i:s');
        if ($client == false) {
            $data['staffid'] = get_staff_user_id();
        }
        $data['content'] = nl2br($data['content']);
        $this->db->insert(db_prefix() . 'perusahaan_comments', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $perusahaan = $this->get($data['perusahaan_id']);

            // No notifications client when perusahaan is with draft status
            if ($perusahaan->status == '6' && $client == false) {
                return true;
            }

            if ($client == true) {
                // Get creator and assigned
                $this->db->select('staffid,email,phonenumber');
                $this->db->where('staffid', $perusahaan->addedfrom);
                $this->db->or_where('staffid', $perusahaan->assigned);
                $staff_perusahaan = $this->db->get(db_prefix() . 'staff')->result_array();
                $notifiedUsers  = [];
                foreach ($staff_perusahaan as $member) {
                    $notified = add_notification([
                        'description'     => 'not_perusahaan_comment_from_client',
                        'touserid'        => $member['staffid'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'perusahaan/list_perusahaan/' . $data['perusahaan_id'],
                        'additional_data' => serialize([
                            $perusahaan->subject,
                        ]),
                    ]);

                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }

                    $template     = mail_template('perusahaan_comment_to_staff', $perusahaan->id, $member['email']);
                    $merge_fields = $template->get_merge_fields();
                    $template->send();
                    // Send email/sms to admin that client commented
                    $this->app_sms->trigger(SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_STAFF, $member['phonenumber'], $merge_fields);
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                // Send email/sms to client that admin commented
                $template     = mail_template('perusahaan_comment_to_customer', $perusahaan);
                $merge_fields = $template->get_merge_fields();
                $template->send();
                $this->app_sms->trigger(SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_CUSTOMER, $perusahaan->phone, $merge_fields);
            }

            return true;
        }

        return false;
    }

    public function edit_comment($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'perusahaan_comments', [
            'content' => nl2br($data['content']),
        ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get perusahaan comments
     * @param  mixed $id perusahaan id
     * @return array
     */
    public function get_comments($id)
    {
        $this->db->where('perusahaan_id', $id);
        $this->db->order_by('dateadded', 'ASC');

        return $this->db->get(db_prefix() . 'perusahaan_comments')->result_array();
    }

    /**
     * Get perusahaan single comment
     * @param  mixed $id  comment id
     * @return object
     */
    public function get_comment($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'perusahaan_comments')->row();
    }

    /**
     * Remove perusahaan comment
     * @param  mixed $id comment id
     * @return boolean
     */
    public function remove_comment($id)
    {
        $comment = $this->get_comment($id);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'perusahaan_comments');
        if ($this->db->affected_rows() > 0) {
            log_activity('Perusahaan Comment Removed [PerusahaanID:' . $comment->perusahaan_id . ', Comment Content: ' . $comment->content . ']');

            return true;
        }

        return false;
    }

    /**
     * Copy perusahaan
     * @param  mixed $id perusahaan id
     * @return mixed
     */
    public function copy($id)
    {
        $this->copy      = true;
        $perusahaan        = $this->get($id, [], true);
        $not_copy_fields = [
            'addedfrom',
            'id',
            'datecreated',
            'hash',
            'status',
            'invoice_id',
            'perusahaan_id',
            'is_expiry_notified',
            'date_converted',
            'signature',
            'acceptance_firstname',
            'acceptance_lastname',
            'acceptance_email',
            'acceptance_date',
            'acceptance_ip',
        ];
        $fields      = $this->db->list_fields(db_prefix() . 'perusahaan');
        $insert_data = [];
        foreach ($fields as $field) {
            if (!in_array($field, $not_copy_fields)) {
                $insert_data[$field] = $perusahaan->$field;
            }
        }

        $insert_data['addedfrom']   = get_staff_user_id();
        $insert_data['datecreated'] = date('Y-m-d H:i:s');
        $insert_data['date']        = _d(date('Y-m-d'));
        $insert_data['status']      = 6;
        $insert_data['hash']        = app_generate_hash();

        // in case open till is expired set new 7 days starting from current date
        if ($insert_data['open_till'] && get_option('perusahaan_due_after') != 0) {
            $insert_data['open_till'] = _d(date('Y-m-d', strtotime('+' . get_option('perusahaan_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $id = $this->add($insert_data);
            log_activity('Copied Perusahaan ' . format_perusahaan_number($perusahaan->id));
            return $id;

        return false;
    }

    /**
     * Take perusahaan action (change status) manually
     * @param  mixed $status status id
     * @param  mixed  $id     perusahaan id
     * @param  boolean $client is request coming from client side or not
     * @return boolean
     */
    public function mark_action_status($status, $id, $client = false)
    {
        $original_perusahaan = $this->get($id);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'perusahaan', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            // Client take action
            if ($client == true) {
                $revert = false;
                // Declined
                if ($status == 2) {
                    $message = 'not_perusahaan_perusahaan_declined';
                } elseif ($status == 3) {
                    $message = 'not_perusahaan_perusahaan_accepted';
                // Accepted
                } else {
                    $revert = true;
                }
                // This is protection that only 3 and 4 statuses can be taken as action from the client side
                if ($revert == true) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'perusahaan', [
                        'status' => $original_perusahaan->status,
                    ]);

                    return false;
                }

                // Get creator and assigned;
                $this->db->where('staffid', $original_perusahaan->addedfrom);
                $this->db->or_where('staffid', $original_perusahaan->assigned);
                $staff_perusahaan = $this->db->get(db_prefix() . 'staff')->result_array();
                $notifiedUsers  = [];
                foreach ($staff_perusahaan as $member) {
                    $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => $message,
                            'link'            => 'perusahaan/list_perusahaan/' . $id,
                            'additional_data' => serialize([
                                format_perusahaan_number($id),
                            ]),
                        ]);
                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }
                }

                pusher_trigger_notification($notifiedUsers);

                // Send thank you to the customer email template
                if ($status == 3) {
                    foreach ($staff_perusahaan as $member) {
                        send_mail_template('perusahaan_accepted_to_staff', $original_perusahaan, $member['email']);
                    }

                    send_mail_template('perusahaan_accepted_to_customer', $original_perusahaan);

                    hooks()->do_action('perusahaan_accepted', $id);
                } else {

                    // Client declined send template to admin
                    foreach ($staff_perusahaan as $member) {
                        send_mail_template('perusahaan_declined_to_staff', $original_perusahaan, $member['email']);
                    }

                    hooks()->do_action('perusahaan_declined', $id);
                }
            } else {
                // in case admin mark as open the the open till date is smaller then current date set open till date 7 days more
                if ((date('Y-m-d', strtotime($original_perusahaan->open_till)) < date('Y-m-d')) && $status == 1) {
                    $open_till = date('Y-m-d', strtotime('+7 DAY', strtotime(date('Y-m-d'))));
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'perusahaan', [
                        'open_till' => $open_till,
                    ]);
                }
            }

            log_activity('Perusahaan Status Changes [PerusahaanID:' . $id . ', Status:' . format_perusahaan_status($status, '', false) . ',Client Action: ' . (int) $client . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete perusahaan
     * @param  mixed $id perusahaan id
     * @return boolean
     */
    public function delete($id)
    {
        $this->clear_signature($id);
        $perusahaan = $this->get($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'perusahaan');
        if ($this->db->affected_rows() > 0) {
            if (!is_null($perusahaan->short_link)) {
                app_archive_short_link($perusahaan->short_link);
            }

            delete_tracked_emails($id, 'perusahaan');

            $this->db->where('perusahaan_id', $id);
            $this->db->delete(db_prefix() . 'perusahaan_comments');
            // Get related tasks
            $this->db->where('rel_type', 'perusahaan');
            $this->db->where('clientid', $id);

            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }

            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('clientid', $id);
            $this->db->where('rel_type', 'perusahaan');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('relid IN (SELECT id from ' . db_prefix() . 'itemable WHERE rel_type="perusahaan" AND clientid="' . $this->db->escape_str($id) . '")');
            $this->db->where('fieldto', 'items');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('clientid', $id);
            $this->db->where('rel_type', 'perusahaan');
            $this->db->delete(db_prefix() . 'itemable');


            $this->db->where('clientid', $id);
            $this->db->where('rel_type', 'perusahaan');
            $this->db->delete(db_prefix() . 'item_tax');

            $this->db->where('clientid', $id);
            $this->db->where('rel_type', 'perusahaan');
            $this->db->delete(db_prefix() . 'taggables');

            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'perusahaan');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('rel_type', 'perusahaan');
            $this->db->where('clientid', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_type', 'perusahaan');
            $this->db->where('clientid', $id);
            $this->db->delete(db_prefix() . 'views_tracking');

            log_activity('Perusahaan Deleted [PerusahaanID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get relation perusahaan data. Ex lead or customer will return the necesary db fields
     * @param  mixed $clientid
     * @param  string $rel_type customer/lead
     * @return object
     */
    public function get_relation_data_values($clientid)
    {
        $data = new StdClass();

        $this->db->where('userid', $clientid);
        $_data = $this->db->get(db_prefix() . 'clients')->row();

        $primary_contact_id = get_primary_contact_user_id($clientid);

        if ($primary_contact_id) {
            $contact     = $this->clients_model->get_contact($primary_contact_id);
            $data->email = $contact->email;
        }

        $data->phone            = $_data->phonenumber;
        $data->is_using_company = false;
        if (isset($contact)) {
            $data->to = $contact->firstname . ' ' . $contact->lastname;
        } else {
            if (!empty($_data->company)) {
                $data->to               = $_data->company;
                $data->is_using_company = true;
            }
        }
        $data->company = $_data->company;
        $data->lokasi = clear_textarea_breaks($_data->lokasi);
        $data->zip     = $_data->zip;
        $data->country = $_data->country;
        $data->state   = $_data->state;
        $data->city    = $_data->city;

        $default_currency = $this->clients_model->get_customer_default_currency($clientid);
        if ($default_currency != 0) {
            $data->currency = $default_currency;
        }

        return $data;
    }

    /**
     * Sent perusahaan to email
     * @param  mixed  $id        perusahaan_id
     * @param  string  $template  email template to sent
     * @param  boolean $attachpdf attach perusahaan pdf or not
     * @return boolean
     */
    public function send_expiry_reminder($id)
    {
        $perusahaan = $this->get($id);

        // For all cases update this to prevent sending multiple reminders eq on fail
        $this->db->where('id', $perusahaan->id);
        $this->db->update(db_prefix() . 'perusahaan', [
            'is_expiry_notified' => 1,
        ]);

        $template     = mail_template('perusahaan_expiration_reminder', $perusahaan);
        $merge_fields = $template->get_merge_fields();

        $template->send();

        if (can_send_sms_based_on_creation_date($perusahaan->datecreated)) {
            $sms_sent = $this->app_sms->trigger(SMS_TRIGGER_PROPOSAL_EXP_REMINDER, $perusahaan->phone, $merge_fields);
        }

        return true;
    }

    public function send_perusahaan_to_email($id, $attachpdf = true, $cc = '')
    {
        // Perusahaan status is draft update to sent
        if (total_rows(db_prefix() . 'perusahaan', ['id' => $id, 'status' => 6]) > 0) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'perusahaan', ['status' => 4]);
        }

        $perusahaan = $this->get($id);

        $sent = send_mail_template('perusahaan_send_to_customer', $perusahaan, $attachpdf, $cc);

        if ($sent) {

            // Set to status sent
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'perusahaan', [
                'status' => 4,
            ]);

            hooks()->do_action('perusahaan_sent', $id);

            return true;
        }

        return false;
    }

    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)
    {
        _deprecated_function('Perusahaan_model::do_kanban_query', '2.9.2', 'PerusahaanPipeline class');

        $kanBan = (new PerusahaanPipeline($status))
            ->search($search)
            ->page($page)
            ->sortBy($sort['sort'] ?? null, $sort['sort_by'] ?? null);

        if ($count) {
            return $kanBan->countAll();
        }

        return $kanBan->get();
    }

    /**
     * Get the perusahaan for the client given
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_client_perusahaan($client = null)
    {
        /*
        if ($staffId && ! staff_can('view', 'perusahaan', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }
        */

        $this->db->select( db_prefix() . 'clients.userid,' . db_prefix() . 'perusahaan.hash,' . db_prefix() . 'perusahaan.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'perusahaan.clientid', 'left');
        
        $this->db->where(db_prefix() . 'perusahaan.clientid =', $client->userid);

        return $this->db->get(db_prefix() . 'perusahaan')->result_array();
    }

    /**
     * All perusahaan activity
     * @param mixed $id perusahaanid
     * @return array
     */
    public function get_perusahaan_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'perusahaan');
        $this->db->order_by('date', 'desc');

        return $this->db->get(db_prefix() . 'perusahaan_activity')->result_array();
    }

    /**
     * Log perusahaan activity to database
     * @param mixed $id perusahaanid
     * @param string $description activity description
     */
    public function log_perusahaan_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid   = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        }

        $this->db->insert(db_prefix() . 'perusahaan_activity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => 'perusahaan',
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
    }
}
