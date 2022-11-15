<?php defined('BASEPATH') or exit('No direct script access allowed');



if (!$CI->db->table_exists(db_prefix() . 'perusahaan')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "perusahaan` (
      `id` int(11) NOT NULL,
      `subject` varchar(191) NOT NULL,
      `content` longtext DEFAULT NULL,
      `addedfrom` int(11) NOT NULL,
      `datecreated` datetime NOT NULL,
      `total` decimal(15,2) DEFAULT NULL,
      `subtotal` decimal(15,2) NOT NULL,
      `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
      `adjustment` decimal(15,2) DEFAULT NULL,
      `discount_percent` decimal(15,2) NOT NULL,
      `discount_total` decimal(15,2) NOT NULL,
      `discount_type` varchar(30) DEFAULT NULL,
      `show_quantity_as` int(11) NOT NULL DEFAULT 1,
      `currency` int(11) NOT NULL,
      `open_till` date DEFAULT NULL,
      `date` date NOT NULL,
      `clientid` int(11) DEFAULT NULL,
      `rel_type` varchar(40) DEFAULT NULL,
      `assigned` int(11) DEFAULT NULL,
      `hash` varchar(32) NOT NULL,
      `perusahaan_to` varchar(191) DEFAULT NULL,
      `country` int(11) NOT NULL DEFAULT 0,
      `zip` varchar(50) DEFAULT NULL,
      `state` varchar(100) DEFAULT NULL,
      `city` varchar(100) DEFAULT NULL,
      `address` varchar(200) DEFAULT NULL,
      `email` varchar(150) DEFAULT NULL,
      `phone` varchar(50) DEFAULT NULL,
      `allow_comments` tinyint(1) NOT NULL DEFAULT 1,
      `status` int(11) NOT NULL,
      `perusahaan_id` int(11) DEFAULT NULL,
      `invoice_id` int(11) DEFAULT NULL,
      `date_converted` datetime DEFAULT NULL,
      `pipeline_order` int(11) DEFAULT 1,
      `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
      `acceptance_firstname` varchar(50) DEFAULT NULL,
      `acceptance_lastname` varchar(50) DEFAULT NULL,
      `acceptance_email` varchar(100) DEFAULT NULL,
      `acceptance_date` datetime DEFAULT NULL,
      `acceptance_ip` varchar(40) DEFAULT NULL,
      `signature` varchar(40) DEFAULT NULL,
      `short_link` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'perusahaan`
      ADD PRIMARY KEY (`id`),
      ADD UNIQUE KEY `subject` (`subject`),
      ADD KEY `status` (`status`),
      ADD KEY `date` (`date`),
      ADD KEY `assigned` (`assigned`),
      ADD KEY `perusahaan_to` (`perusahaan_to`);
    ');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'perusahaan`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
