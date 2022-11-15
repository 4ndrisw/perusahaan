<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Perusahaan_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $perusahaan;

    protected $contact;

    public $slug = 'perusahaan-send-to-client';

    public $rel_type = 'perusahaan';

    public function __construct($perusahaan, $contact, $cc = '')
    {
        parent::__construct();

        $this->perusahaan = $perusahaan;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->perusahaan_model->get_attachments($this->perusahaan->id, $attachment);
                $this->add_attachment([
                                'attachment' => get_upload_path_by_type('perusahaan') . $this->perusahaan->id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
            }
        }

        $this->to($this->contact->email)
        ->set_clientid($this->perusahaan->id)
        ->set_merge_fields('client_merge_fields', $this->perusahaan->clientid, $this->contact->id)
        ->set_merge_fields('perusahaan_merge_fields', $this->perusahaan->id);
    }
}
