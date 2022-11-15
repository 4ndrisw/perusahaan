<?php

defined('BASEPATH') or exit('No direct script access allowed');


require_once('install/perusahaan.php');
require_once('install/perusahaan_activity.php');
require_once('install/perusahaan_comments.php');

$CI->db->query("
INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('perusahaan', 'perusahaan-send-to-client', 'english', 'Send perusahaan to Customer', 'perusahaan # {perusahaan_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached perusahaan <strong># {perusahaan_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>perusahaan status:</strong> {perusahaan_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-already-send', 'english', 'perusahaan Already Sent to Customer', 'perusahaan # {perusahaan_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your perusahaan request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-declined-to-staff', 'english', 'perusahaan Declined (Sent to Staff)', 'Customer Declined perusahaan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined perusahaan with number <strong># {perusahaan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-accepted-to-staff', 'english', 'perusahaan Accepted (Sent to Staff)', 'Customer Accepted perusahaan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted perusahaan with number <strong># {perusahaan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-thank-you-to-customer', 'english', 'Thank You Email (Sent to Customer After Accept)', 'Thank for you accepting perusahaan', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank for for accepting the perusahaan.</span><br /> <br /><span style=\"font-size: 12pt;\">We look forward to doing business with you.</span><br /> <br /><span style=\"font-size: 12pt;\">We will contact you as soon as possible.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-expiry-reminder', 'english', 'perusahaan Expiration Reminder', 'perusahaan Expiration Reminder', '<p><span style=\"font-size: 12pt;\">Hello {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The perusahaan with <strong># {perusahaan_number}</strong> will expire on <strong>{perusahaan_expirydate}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span></p>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-send-to-client', 'english', 'Send perusahaan to Customer', 'perusahaan # {perusahaan_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached perusahaan <strong># {perusahaan_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>perusahaan status:</strong> {perusahaan_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-already-send', 'english', 'perusahaan Already Sent to Customer', 'perusahaan # {perusahaan_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your perusahaan request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-declined-to-staff', 'english', 'perusahaan Declined (Sent to Staff)', 'Customer Declined perusahaan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined perusahaan with number <strong># {perusahaan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-accepted-to-staff', 'english', 'perusahaan Accepted (Sent to Staff)', 'Customer Accepted perusahaan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted perusahaan with number <strong># {perusahaan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'staff-added-as-project-member', 'english', 'Staff Added as Project Member', 'New project assigned to you', '<p>Hi <br /><br />New perusahaan has been assigned to you.<br /><br />You can view the perusahaan on the following link <a href=\"{perusahaan_link}\">perusahaan__number</a><br /><br />{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('perusahaan', 'perusahaan-accepted-to-staff', 'english', 'perusahaan Accepted (Sent to Staff)', 'Customer Accepted perusahaan', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted perusahaan with number <strong># {perusahaan_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the perusahaan on the following link: <a href=\"{perusahaan_link}\">{perusahaan_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0);
");
/*
 *
 */

// Add options for perusahaan
add_option('delete_only_on_last_perusahaan', 1);
add_option('perusahaan_prefix', 'SCH-');
add_option('next_perusahaan_number', 1);
add_option('default_perusahaan_assigned', 9);
add_option('perusahaan_number_decrement_on_delete', 0);
add_option('perusahaan_number_format', 4);
add_option('perusahaan_year', date('Y'));
add_option('exclude_perusahaan_from_client_area_with_draft_status', 1);
add_option('predefined_client_note_perusahaan', '- Staf diatas untuk melakukan riksa uji pada perusahaan tersebut.
- Staf diatas untuk membuat dokumentasi riksa uji sesuai kebutuhan.');
add_option('predefined_terms_perusahaan', '- Pelaksanaan riksa uji harus mengikuti prosedur yang ditetapkan perusahaan pemilik alat.
- Dilarang membuat dokumentasi tanpa seizin perusahaan pemilik alat.
- Dokumen ini diterbitkan dari sistem CRM, tidak memerlukan tanda tangan dari PT. Cipta Mas Jaya');
add_option('perusahaan_due_after', 1);
add_option('allow_staff_view_perusahaan_assigned', 1);
add_option('show_assigned_on_perusahaan', 1);
add_option('require_client_logged_in_to_view_perusahaan', 0);

add_option('show_project_on_perusahaan', 1);
add_option('perusahaan_pipeline_limit', 1);
add_option('default_perusahaan_pipeline_sort', 1);
add_option('perusahaan_accept_identity_confirmation', 1);
add_option('perusahaan_qrcode_size', '160');
add_option('perusahaan_send_telegram_message', 0);


/*

DROP TABLE `tblperusahaan`;
DROP TABLE `tblperusahaan_activity`, `tblperusahaan_items`, `tblperusahaan_members`;
delete FROM `tbloptions` WHERE `name` LIKE '%perusahaan%';
DELETE FROM `tblemailtemplates` WHERE `type` LIKE 'perusahaan';



*/