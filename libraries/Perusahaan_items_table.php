<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(APPPATH . 'libraries/App_items_table_template.php');

class Perusahaan_items_table extends App_items_table_template
{
    public function __construct($transaction, $type, $for = 'html', $admin_preview = false)
    {
        // Required
        $this->type          = strtolower($type);
        $this->admin_preview = $admin_preview;
        $this->for           = $for;

        $this->set_transaction($transaction);
        $this->set_items($transaction->items);
        $this->adjustment = 7;

        parent::__construct();
    }

    /**
     * Builds the actual table items rows preview
     * @return string
     */
    public function items()
    {
        $html = '';


        $descriptionItemWidth = $this->get_description_item_width();

        $regularItemWidth  = $this->get_regular_items_width($this->adjustment);


        if ($this->for == 'html') {
            $descriptionItemWidth = $descriptionItemWidth - 5;
            $regularItemWidth     = $regularItemWidth - 5;
        }

        $i = 1;
        foreach ($this->items as $item) {
            $itemHTML = '';

            // Open table row
            $itemHTML .= '<tr' . $this->tr_attributes($item) . '>';

            // Table data number
            $itemHTML .= '<td' . $this->td_attributes() . ' align="center" width="5%">' . $i . '</td>';

            $itemHTML .= '<td class="description" align="left;" width="' . $descriptionItemWidth . '%">';

            /**
             * Item description
             */
            if (!empty($item['description'])) {
                $itemHTML .= '<span class="description" style="font-size:' . 12 . 'px;"><strong>'
                . $this->period_merge_field($item['description'])
                . '</strong></span>';

                if (!empty($item['long_description'])) {
                    $itemHTML .= '<br />';
                }
            }

            /**
             * Item long description
             */
            if (!empty($item['long_description'])) {
                $itemHTML .= '<span class="long-description">' . $this->period_merge_field($item['long_description']) . '</span>';
            }

            $itemHTML .= '</td>';

            /**
             * Item quantity
             */
            $itemHTML .= '<td align="right" width="' . $regularItemWidth . '%">' . floatVal($item['qty']);

            /**
             * Maybe item has added unit?
             */
            $itemHTML .= ' ' . 'Unit';
            /*
            if ($item['unit']) {
                $itemHTML .= ' ' . $item['unit'];
            }
            */

            $itemHTML .= '</td>';


            // Close table row
            $itemHTML .= '</tr>';

            $html .= $itemHTML;

            $i++;
        }

        return $html;
    }

    /**
     * Html headings preview
     * @return string
     */
    public function html_headings()
    {
        $html = '<tr>';
        $html .= '<th align="center">' . $this->number_heading() . '</th>';
        $html .= '<th class="description" width="' . $this->get_description_item_width() . '%" align="left">' . $this->item_heading() . '</th>';
        $html .= '<th align="right">' . $this->qty_heading() . '</th>';        
        $html .= '</tr>';

        return $html;
    }

    /**
     * PDF headings preview
     * @return string
     */
    public function pdf_headings()
    {
        $descriptionItemWidth = $this->get_description_item_width();
        $regularItemWidth     = $this->get_regular_items_width($this->adjustment);

        $tblhtml = '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

        $tblhtml .= '<th width="5%;" align="center">' . $this->number_heading() . '</th>';
        $tblhtml .= '<th width="' . $descriptionItemWidth . '%" align="left">' . $this->item_heading() . '</th>';

        $tblhtml .= '<th width="' . $regularItemWidth . '%" align="right">' . $this->qty_heading() . '</th>';
      
        $tblhtml .= '</tr>';

        return $tblhtml;
    }

    /**
     * Check for period merge field for recurring invoices
     *
     * @return string
     */
    protected function period_merge_field($text)
    {
        if ($this->type != 'invoice') {
            return $text;
        }

        // Is subscription invoice
        if (!property_exists($this->transaction, 'recurring_type')) {
            return $text;
        }

        $compareRecurring = $this->transaction->recurring_type;
        $compareDate      = !$this->transaction->last_recurring_date ? $this->transaction->date : $this->transaction->last_recurring_date;
        $transactionDate  = $this->transaction->date;

        // Is not Y-m-d format
        if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $compareDate)) {
            $compareDate = to_sql_date($compareDate);
        }

        if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $transactionDate)) {
            $transactionDate = to_sql_date($transactionDate);
        }

        if ($this->transaction->custom_recurring == 0) {
            $compareRecurring = 'month';
        }

        $next_date = date('Y-m-d', strtotime(
            '+' . $this->transaction->recurring . ' ' . strtoupper($compareRecurring),
            strtotime($compareDate)
        ));

        return str_ireplace('{period}', _d($transactionDate) . ' - ' . _d(date('Y-m-d', strtotime('-1 day', strtotime($next_date)))), $text);
    }

    protected function get_description_item_width()
    {
        $item_width = hooks()->apply_filters('item_description_td_width', 80);

        // If show item taxes is disabled in PDF we should increase the item width table heading
        //return $this->show_tax_per_item() == 0 ? $item_width + 15 : $item_width;
        return $item_width;
    }

    protected function get_regular_items_width($adjustment)
    {
        $descriptionItemWidth = $this->get_description_item_width();
        $totalheadings = 1;

        return (100 - ($descriptionItemWidth + $adjustment)) / $totalheadings;
    }
}
