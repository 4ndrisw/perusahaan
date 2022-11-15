<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Perusahaan
Description: Default module for defining perusahaan
Version: 1.0.1
Requires at least: 2.3.*
*/

define('PERUSAHAAN_MODULE_NAME', 'perusahaan');
define('PERUSAHAAN_ATTACHMENTS_FOLDER', FCPATH . 'uploads/perusahaan/');

//hooks()->add_filter('before_perusahaan_updated', '_format_data_perusahaan_feature');
//hooks()->add_filter('before_perusahaan_added', '_format_data_perusahaan_feature');

hooks()->add_action('after_cron_run', 'perusahaan_notification');
hooks()->add_action('admin_init', 'perusahaan_module_init_menu_items');
hooks()->add_action('admin_init', 'perusahaan_permissions');
hooks()->add_action('admin_init', 'perusahaan_settings_tab');
hooks()->add_action('clients_init', 'perusahaan_clients_area_menu_items');

//hooks()->add_action('app_admin_head', 'perusahaan_head_component');
//hooks()->add_action('app_admin_footer', 'perusahaan_footer_js_component');

hooks()->add_action('staff_member_deleted', 'perusahaan_staff_member_deleted');

hooks()->add_filter('migration_tables_to_replace_old_links', 'perusahaan_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'perusahaan_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'perusahaan_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'perusahaan_add_dashboard_widget');
hooks()->add_filter('module_perusahaan_action_links', 'module_perusahaan_action_links');


function perusahaan_add_dashboard_widget($widgets)
{
    /*
    $widgets[] = [
        'path'      => 'perusahaan/widgets/perusahaan_this_week',
        'container' => 'left-8',
    ];
    $widgets[] = [
        'path'      => 'perusahaan/widgets/project_not_perusahaand',
        'container' => 'left-8',
    ];
    */

    return $widgets;
}


function perusahaan_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'perusahaan', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function perusahaan_global_search_result_output($output, $data)
{
    if ($data['type'] == 'perusahaan') {
        $output = '<a href="' . admin_url('perusahaan/perusahaan/' . $data['result']['id']) . '">' . format_perusahaan_number($data['result']['id']) . '</a>';
    }

    return $output;
}

function perusahaan_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('perusahaan', '', 'view')) {

        // perusahaan
        $CI->db->select()
           ->from(db_prefix() . 'perusahaan')
           ->like(db_prefix() . 'perusahaan.formatted_number', $q)->limit($limit);

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'perusahaan',
                'search_heading' => _l('perusahaan'),
            ];

        if(isset($result[0]['result'][0]['id'])){
            return $result;
        }

        // perusahaan
        $CI->db->select()->from(db_prefix() . 'perusahaan')->like(db_prefix() . 'clients.company', $q)->or_like(db_prefix() . 'perusahaan.formatted_number', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'perusahaan.clientid='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'clients.company', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'perusahaan',
                'search_heading' => _l('perusahaan'),
            ];
    }

    return $result;
}

function perusahaan_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'perusahaan',
                'field' => 'description',
            ];

    return $tables;
}

function perusahaan_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('perusahaan', $capabilities, _l('perusahaan'));
}


/**
* Register activation module hook
*/
register_activation_hook(PERUSAHAAN_MODULE_NAME, 'perusahaan_module_activation_hook');

function perusahaan_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register deactivation module hook
*/
register_deactivation_hook(PERUSAHAAN_MODULE_NAME, 'perusahaan_module_deactivation_hook');

function perusahaan_module_deactivation_hook()
{

     log_activity( 'Hello, world! . perusahaan_module_deactivation_hook ' );
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(PERUSAHAAN_MODULE_NAME, [PERUSAHAAN_MODULE_NAME]);

/**
 * Init perusahaan module menu items in setup in admin_init hook
 * @return null
 */
function perusahaan_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('perusahaan'),
            'url'        => 'perusahaan',
            'permission' => 'perusahaan',
            'position'   => 57,
            ]);

    if (has_permission('perusahaan', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('perusahaan', [
                'slug'     => 'perusahaan-tracking',
                'name'     => _l('perusahaan'),
                'icon'     => 'fa fa-calendar',
                'href'     => admin_url('perusahaan'),
                'position' => 12,
        ]);
    }
}

function module_perusahaan_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=perusahaan') . '">' . _l('settings') . '</a>';

    return $actions;
}

function perusahaan_clients_area_menu_items()
{
    // Show menu item only if client is logged in
    if (is_client_logged_in()) {
        add_theme_menu_item('perusahaan', [
                    'name'     => _l('perusahaan'),
                    'href'     => site_url('perusahaan/list'),
                    'position' => 15,
        ]);
    }
}

/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function perusahaan_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('perusahaan', [
        'name'     => _l('settings_group_perusahaan'),
        //'view'     => module_views_path(PERUSAHAAN_MODULE_NAME, 'admin/settings/includes/perusahaan'),
        'view'     => 'perusahaan/perusahaan_settings',
        'position' => 51,
    ]);
}

$CI = &get_instance();
$CI->load->helper(PERUSAHAAN_MODULE_NAME . '/perusahaan');

if(($CI->uri->segment(1)=='admin' && $CI->uri->segment(2)=='perusahaan') || $CI->uri->segment(1)=='perusahaan'){
    $CI->app_css->add(PERUSAHAAN_MODULE_NAME.'-css', base_url('modules/'.PERUSAHAAN_MODULE_NAME.'/assets/css/'.PERUSAHAAN_MODULE_NAME.'.css'));
    $CI->app_scripts->add(PERUSAHAAN_MODULE_NAME.'-js', base_url('modules/'.PERUSAHAAN_MODULE_NAME.'/assets/js/'.PERUSAHAAN_MODULE_NAME.'.js'));
}
