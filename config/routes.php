<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['perusahaan/perusahaan/(:num)/(:any)'] = 'perusahaan/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['perusahaan/list'] = 'myperusahaan/list';
$route['perusahaan/show/(:num)/(:any)'] = 'myperusahaan/show/$1/$2';
$route['perusahaan/office/(:num)/(:any)'] = 'myperusahaan/office/$1/$2';
$route['perusahaan/pdf/(:num)'] = 'myperusahaan/pdf/$1';


$route['perusahaan/product_services/items_create'] = 'product_services/items_create/$1';
