<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Injects theme CSS
 * @return null
 */
function perusahaan_head_component()
{
        echo '<link rel="stylesheet" type="text/css" id="perusahaan-css" href="'. base_url('modules/perusahaan/assets/css/perusahaan.css').'">';
    $CI = &get_instance();
    if (($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'perusahaan') ||
        $CI->uri->segment(1) == 'perusahaan'){
    }
}


/**
 * Injects theme CSS
 * @return null
 */
function perusahaan_footer_js_component()
{
        echo '<script src="' . base_url('modules/perusahaan/assets/js/perusahaan.js') . '"></script>';
    $CI = &get_instance();
    if (($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'perusahaan') ||
        ($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'list_perusahaan') ||
        $CI->uri->segment(1) == 'perusahaan'){
    }
}


/**
 * Prepare general perusahaan pdf
 * @since  Version 1.0.2
 * @param  object $perusahaan perusahaan as object with all necessary fields
 * @param  string $tag tag for bulk pdf exporter
 * @return mixed object
 */
function perusahaan_pdf($perusahaan, $tag = '')
{
    return app_pdf('perusahaan',  module_libs_path(PERUSAHAAN_MODULE_NAME) . 'pdf/Perusahaan_pdf', $perusahaan, $tag);
}


/**
 * Get perusahaan short_url
 * @since  Version 2.7.3
 * @param  object $perusahaan
 * @return string Url
 */
function get_perusahaan_shortlink($perusahaan)
{
    $long_url = site_url("perusahaan/{$perusahaan->id}/{$perusahaan->hash}");
    if (!get_option('bitly_access_token')) {
        return $long_url;
    }

    // Check if perusahaan has short link, if yes return short link
    if (!empty($perusahaan->short_link)) {
        return $perusahaan->short_link;
    }

    // Create short link and return the newly created short link
    $short_link = app_generate_short_link([
        'long_url' => $long_url,
        'title'    => format_perusahaan_number($perusahaan->id),
    ]);

    if ($short_link) {
        $CI = &get_instance();
        $CI->db->where('id', $perusahaan->id);
        $CI->db->update(db_prefix() . 'perusahaan', [
            'short_link' => $short_link,
        ]);

        return $short_link;
    }

    return $long_url;
}

/**
 * Check if perusahaan hash is equal
 * @param  mixed $id   perusahaan id
 * @param  string $hash perusahaan hash
 * @return void
 */
function check_perusahaan_restrictions($id, $hash)
{
    $CI = &get_instance();
    $CI->load->model('perusahaan_model');
    if (!$hash || !$id) {
        show_404();
    }
    $perusahaan = $CI->perusahaan_model->get($id);
    if (!$perusahaan || ($perusahaan->hash != $hash)) {
        show_404();
    }
}

/**
 * Check if perusahaan email template for expiry reminders is enabled
 * @return boolean
 */
function is_perusahaan_email_expiry_reminder_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'perusahaan-expiry-reminder', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending perusahaan expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_perusahaan_expiry_reminders_enabled()
{
    return is_perusahaan_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_PROPOSAL_EXP_REMINDER);
}

/**
 * Return perusahaan status color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function perusahaan_status_color_class($id, $replace_default_by_muted = false)
{
    if ($id == 1) {
        $class = 'default';
    } elseif ($id == 2) {
        $class = 'danger';
    } elseif ($id == 3) {
        $class = 'success';
    } elseif ($id == 4 || $id == 5) {
        // status sent and revised
        $class = 'info';
    } elseif ($id == 6) {
        $class = 'default';
    }
    if ($class == 'default') {
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    }

    return $class;
}
/**
 * Format perusahaan status with label or not
 * @param  mixed  $status  perusahaan status id
 * @param  string  $classes additional label classes
 * @param  boolean $label   to include the label or return just translated text
 * @return string
 */
function format_perusahaan_status($status, $classes = '', $label = true)
{
    $id = $status;
    if ($status == 1) {
        $status      = _l('perusahaan_status_open');
        $label_class = 'default';
    } elseif ($status == 2) {
        $status      = _l('perusahaan_status_declined');
        $label_class = 'danger';
    } elseif ($status == 3) {
        $status      = _l('perusahaan_status_accepted');
        $label_class = 'success';
    } elseif ($status == 4) {
        $status      = _l('perusahaan_status_sent');
        $label_class = 'info';
    } elseif ($status == 5) {
        $status      = _l('perusahaan_status_revised');
        $label_class = 'info';
    } elseif ($status == 6) {
        $status      = _l('perusahaan_status_draft');
        $label_class = 'default';
    }

    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status perusahaan-status-' . $id . '">' . $status . '</span>';
    }

    return $status;
}

/**
 * Function that format perusahaan number based on the prefix option and the perusahaan id
 * @param  mixed $id perusahaan id
 * @return string
 */
function format_perusahaan_number($id)
{
    $format = get_option('perusahaan_number_prefix') . str_pad($id, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);

    return hooks()->apply_filters('perusahaan_number_format', $format, $id);
}


/**
 * Function that return perusahaan item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_perusahaan_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'perusahaan');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}


/**
 * Calculate perusahaan percent by status
 * @param  mixed $status          perusahaan status
 * @param  mixed $total_perusahaan in case the total is calculated in other place
 * @return array
 */
function get_perusahaan_percent_by_status($status, $total_perusahaan = '')
{
    $has_permission_view                 = has_permission('perusahaan', '', 'view');
    $has_permission_view_own             = has_permission('perusahaan', '', 'view_own');
    $allow_staff_view_perusahaan_assigned = get_option('allow_staff_view_perusahaan_assigned');
    $staffId                             = get_staff_user_id();

    $whereUser = '';
    if (!$has_permission_view) {
        if ($has_permission_view_own) {
            $whereUser = '(addedfrom=' . $staffId;
            if ($allow_staff_view_perusahaan_assigned == 1) {
                $whereUser .= ' OR assigned=' . $staffId;
            }
            $whereUser .= ')';
        } else {
            $whereUser .= 'assigned=' . $staffId;
        }
    }

    if (!is_numeric($total_perusahaan)) {
        $total_perusahaan = total_rows(db_prefix() . 'perusahaan', $whereUser);
    }

    $data            = [];
    $total_by_status = 0;
    $where           = 'status=' . get_instance()->db->escape_str($status);
    if (!$has_permission_view) {
        $where .= ' AND (' . $whereUser . ')';
    }

    $total_by_status = total_rows(db_prefix() . 'perusahaan', $where);
    $percent         = ($total_perusahaan > 0 ? number_format(($total_by_status * 100) / $total_perusahaan, 2) : 0);

    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_perusahaan;

    return $data;
}

/**
 * Function that will search possible perusahaan templates in applicaion/views/admin/perusahaan/templates
 * Will return any found files and user will be able to add new template
 * @return array
 */
function get_perusahaan_templates()
{
    $perusahaan_templates = [];
    if (is_dir(VIEWPATH . 'admin/perusahaan/templates')) {
        foreach (list_files(VIEWPATH . 'admin/perusahaan/templates') as $template) {
            $perusahaan_templates[] = $template;
        }
    }

    return $perusahaan_templates;
}
/**
 * Check if staff member can view perusahaan
 * @param  mixed $id perusahaan id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_perusahaan($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('perusahaan', $staff_id, 'view')) {
        return true;
    }

    $CI->db->select('id, addedfrom, assigned');
    $CI->db->from(db_prefix() . 'perusahaan');
    $CI->db->where('id', $id);
    $perusahaan = $CI->db->get()->row();

    if ((has_permission('perusahaan', $staff_id, 'view_own') && $perusahaan->addedfrom == $staff_id)
        || ($perusahaan->assigned == $staff_id && get_option('allow_staff_view_perusahaan_assigned') == 1)
    ) {
        return true;
    }

    return false;
}
function parse_perusahaan_content_merge_fields($perusahaan)
{
    $id = is_array($perusahaan) ? $perusahaan['id'] : $perusahaan->id;
    $CI = &get_instance();

    $CI->load->library('merge_fields/perusahaan_merge_fields');
    $CI->load->library('merge_fields/other_merge_fields');

    $merge_fields = [];
    $merge_fields = array_merge($merge_fields, $CI->perusahaan_merge_fields->format($id));
    $merge_fields = array_merge($merge_fields, $CI->other_merge_fields->format());
    foreach ($merge_fields as $key => $val) {
        $content = is_array($perusahaan) ? $perusahaan['content'] : $perusahaan->content;

        if (stripos($content, $key) !== false) {
            if (is_array($perusahaan)) {
                $perusahaan['content'] = str_ireplace($key, $val, $content);
            } else {
                $perusahaan->content = str_ireplace($key, $val, $content);
            }
        } else {
            if (is_array($perusahaan)) {
                $perusahaan['content'] = str_ireplace($key, '', $content);
            } else {
                $perusahaan->content = str_ireplace($key, '', $content);
            }
        }
    }

    return $perusahaan;
}

/**
 * Check if staff member have assigned perusahaan / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_perusahaan($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-perusahaan-' . $staff_id);
    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'perusahaan', ['assigned' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-perusahaan-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}

function get_perusahaan_sql_where_staff($staff_id)
{
    $has_permission_view_own            = has_permission('perusahaan', '', 'view_own');
    $allow_staff_view_invoices_assigned = get_option('allow_staff_view_perusahaan_assigned');
    $CI                                 = &get_instance();

    $whereUser = '';
    if ($has_permission_view_own) {
        $whereUser = '((' . db_prefix() . 'perusahaan.addedfrom=' . $CI->db->escape_str($staff_id) . ' AND ' . db_prefix() . 'perusahaan.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "perusahaan" AND capability="view_own"))';
        if ($allow_staff_view_invoices_assigned == 1) {
            $whereUser .= ' OR assigned=' . $CI->db->escape_str($staff_id);
        }
        $whereUser .= ')';
    } else {
        $whereUser .= 'assigned=' . $CI->db->escape_str($staff_id);
    }

    return $whereUser;
}



if (!function_exists('format_perusahaan_info')) {
    /**
     * Format perusahaan info format
     * @param  object $perusahaan perusahaan from database
     * @param  string $for      where this info will be used? Admin area, HTML preview?
     * @return string
     */
    function format_perusahaan_info($perusahaan, $for = '')
    {
        $format = get_option('perusahaan_info_format');

        $countryCode = '';
        $countryName = '';

        if ($country = get_country($perusahaan->country)) {
            $countryCode = $country->iso2;
            $countryName = $country->short_name;
        }

        $perusahaanTo = '<b>' . $perusahaan->perusahaan_to . '</b>';
        $phone      = $perusahaan->phone;
        $email      = $perusahaan->email;

        if ($for == 'admin') {
            $hrefAttrs = '';
            if ($perusahaan->rel_type == 'lead') {
                $hrefAttrs = ' href="#" onclick="init_lead(' . $perusahaan->rel_id . '); return false;" data-toggle="tooltip" data-title="' . _l('lead') . '"';
            } else {
                $hrefAttrs = ' href="' . admin_url('clients/client/' . $perusahaan->rel_id) . '" data-toggle="tooltip" data-title="' . _l('client') . '"';
            }
            $perusahaanTo = '<a' . $hrefAttrs . '>' . $perusahaanTo . '</a>';
        }

        if ($for == 'html' || $for == 'admin') {
            $phone = '<a href="tel:' . $perusahaan->phone . '">' . $perusahaan->phone . '</a>';
            $email = '<a href="mailto:' . $perusahaan->email . '">' . $perusahaan->email . '</a>';
        }

        $format = _info_format_replace('perusahaan_to', $perusahaanTo, $format);
        $format = _info_format_replace('address', $perusahaan->address, $format);
        $format = _info_format_replace('city', $perusahaan->city, $format);
        $format = _info_format_replace('state', $perusahaan->state, $format);

        $format = _info_format_replace('country_code', $countryCode, $format);
        $format = _info_format_replace('country_name', $countryName, $format);

        $format = _info_format_replace('zip_code', $perusahaan->zip, $format);
        $format = _info_format_replace('phone', $phone, $format);
        $format = _info_format_replace('email', $email, $format);

        $whereCF = [];
        if (is_custom_fields_for_customers_portal()) {
            $whereCF['show_on_client_portal'] = 1;
        }
        $customFieldsProposals = get_custom_fields('perusahaan', $whereCF);

        foreach ($customFieldsProposals as $field) {
            $value  = get_custom_field_value($perusahaan->id, $field['id'], 'perusahaan');
            $format = _info_format_custom_field($field['id'], $field['name'], $value, $format);
        }

        // If no custom fields found replace all custom fields merge fields to empty
        $format = _info_format_custom_fields_check($customFieldsProposals, $format);
        $format = _maybe_remove_first_and_last_br_tag($format);

        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return hooks()->apply_filters('perusahaan_info_text', $format, ['perusahaan' => $perusahaan, 'for' => $for]);
    }
}


/**
 * Prepares email template preview $data for the view
 * @param  string $template    template class name
 * @param  mixed $customer_id_or_email customer ID to fetch the primary contact email or email
 * @return array
 */
function perusahaan_prepare_mail_preview_data($template, $customer_id_or_email, $mailClassParams = [])
{
    $CI = &get_instance();

    if (is_numeric($customer_id_or_email)) {
        $contact = $CI->clients_model->get_contact(get_primary_contact_user_id($customer_id_or_email));
        $email   = $contact ? $contact->email : '';
    } else {
        $email = $customer_id_or_email;
    }

    $CI->load->model('emails_model');

    $data['template'] = $CI->perusahaan_mail_template->prepare($email, $template);
    $slug             = $CI->perusahaan_mail_template->get_default_property_value('slug', $template, $mailClassParams);

    $data['template_name'] = $slug;

    $template_result = $CI->emails_model->get(['slug' => $slug, 'language' => 'english'], 'row');

    $data['template_system_name'] = $template_result->name;
    $data['template_id']          = $template_result->emailtemplateid;

    $data['template_disabled'] = $template_result->active == 0;

    return $data;
}


function perusahaan_get_mail_template_path($class, &$params)
{
    //log_activity('params get_mail_template_path 1 : ' .time() .' ' . json_encode($params));
    $CI  = &get_instance();

    $dir = module_libs_path(PERUSAHAAN_MODULE_NAME, 'mails/');

    // Check if second parameter is module and is activated so we can get the class from the module path
    // Also check if the first value is not equal to '/' e.q. when import is performed we set
    // for some values which are blank to "/"
    if (isset($params[0]) && is_string($params[0]) && $params[0] !== '/' && is_dir(module_dir_path($params[0]))) {
        $module = $CI->app_modules->get($params[0]);

        if ($module['activated'] === 1) {
            $dir = module_libs_path($params[0]) . 'mails/';
        }

        unset($params[0]);
        $params = array_values($params);
        //log_activity('params get_mail_template_path 2 : ' .time() .' ' . json_encode($params));
        //log_activity('params get_mail_template_path 3 : ' .time() .' ' . json_encode($dir));
    }

    return $dir . ucfirst($class) . '.php';
}


/**
 * Return RGBa perusahaan status color for PDF documents
 * @param  mixed $status_id current perusahaan status
 * @return string
 */
function perusahaan_status_color_pdf($status_id)
{
    if ($status_id == 1) {
        $statusColor = '119, 119, 119';
    } elseif ($status_id == 2) {
        // Sent
        $statusColor = '3, 169, 244';
    } elseif ($status_id == 3) {
        //Declines
        $statusColor = '252, 45, 66';
    } elseif ($status_id == 4) {
        //Accepted
        $statusColor = '0, 191, 54';
    } else {
        // Expired
        $statusColor = '255, 111, 0';
    }

    return hooks()->apply_filters('perusahaan_status_pdf_color', $statusColor, $status_id);
}
