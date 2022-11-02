<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Perusahaan_pdf extends App_pdf
{
    protected $perusahaan;

    private $perusahaan_number;

    public function __construct($perusahaan, $tag = '')
    {
        if ($perusahaan->rel_id != null && $perusahaan->rel_type == 'customer') {
            $this->load_language($perusahaan->rel_id);
        } else if ($perusahaan->rel_id != null && $perusahaan->rel_type == 'lead') {
            $CI = &get_instance();

            $this->load_language($perusahaan->rel_id);
            $CI->db->select('default_language')->where('id', $perusahaan->rel_id);
            $language = $CI->db->get('leads')->row()->default_language;

            load_pdf_language($language);
        }

        $perusahaan                = hooks()->apply_filters('perusahaan_html_pdf_data', $perusahaan);
        $GLOBALS['perusahaan_pdf'] = $perusahaan;

        parent::__construct();

        $this->tag      = $tag;
        $this->perusahaan = $perusahaan;

        $this->perusahaan_number = format_perusahaan_number($this->perusahaan->id);

        $this->SetTitle($this->perusahaan_number);
        $this->SetDisplayMode('default', 'OneColumn');

        # Don't remove these lines - important for the PDF layout
        $this->perusahaan->content = $this->fix_editor_html($this->perusahaan->content);
    }

    public function prepare()
    {
        $number_word_lang_rel_id = 'unknown';

        if ($this->perusahaan->rel_type == 'customer') {
            $number_word_lang_rel_id = $this->perusahaan->rel_id;
        }

        $this->with_number_to_word($number_word_lang_rel_id);

        $total = '';
        if ($this->perusahaan->total != 0) {
            $total = app_format_money($this->perusahaan->total, get_currency($this->perusahaan->currency));
            $total = _l('perusahaan_total') . ': ' . $total;
        }

        $this->set_view_vars([
            'number'       => $this->perusahaan_number,
            'perusahaan'     => $this->perusahaan,
            'total'        => $total,
            'perusahaan_url' => site_url('perusahaan/' . $this->perusahaan->id . '/' . $this->perusahaan->hash),
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'perusahaan';
    }

    protected function file_path()
    {
        $filePath = 'my_perusahaanpdf.php';
        $customPath = module_views_path('perusahaan','themes/' . active_clients_theme() . '/views/perusahaan/' . $filePath);
        $actualPath = module_views_path('perusahaan','themes/' . active_clients_theme() . '/views/perusahaan/perusahaanpdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
