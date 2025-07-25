<?php

class Progress_order_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
        // Your own constructor code
    }

    public function get_data()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');
        $customer_id = $this->input->post('customer_id');

        $this->db->select('a.*');
        $this->db->from('tr_progess_order a');
        if (!empty($customer_id)) {
            $this->db->where('a.idcustomer', $customer_id);
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.no_surat', $search['value'], 'both');
            $this->db->or_like('a.no_surat', $search['value'], 'both');
            $this->db->or_like('a.namacustomer', $search['value'], 'both');
            $this->db->or_like('a.no_alloy', $search['value'], 'both');
            $this->db->or_like('a.thickness', $search['value'], 'both');
            $this->db->or_like('a.weight', $search['value'], 'both');
            $this->db->or_like('a.delivery', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.no_surat', 'desc');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*');
        $this->db->from('tr_progess_order a');
        if (!empty($customer_id)) {
            $this->db->where('a.idcustomer', $customer_id);
        }
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.no_surat', $search['value'], 'both');
            $this->db->or_like('a.no_surat', $search['value'], 'both');
            $this->db->or_like('a.namacustomer', $search['value'], 'both');
            $this->db->or_like('a.no_alloy', $search['value'], 'both');
            $this->db->or_like('a.thickness', $search['value'], 'both');
            $this->db->or_like('a.weight', $search['value'], 'both');
            $this->db->or_like('a.delivery', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.no_surat', 'desc');

        $get_data_all = $this->db->get();

        $no = (0 + $start);
        $hasil = [];

        foreach ($get_data->result() as $item) :
            $no++;

            $get_fg_booking = $this->db->select('SUM(berat) AS qty_booking')->get_where('stock_material_customer', array('id_dt_spkmarketing' => $item->id_dt_spkmarketing))->result();
            $get_qty_aktual = $this->db->select('SUM(qtyaktual) AS qty_aktual')->get_where('dt_spk_aktual', array('no_surat' => $item->id_dt_spkmarketing))->result();
            $get_qty_produksi = $this->db->select('SUM(totalwidth) AS qty_produksi')->get_where('dt_spk_produksi', array('no_surat' => $item->id_dt_spkmarketing))->result();

            $fg_booking     = (!empty($get_fg_booking)) ? $get_fg_booking[0]->qty_booking : 0;
            $qty_aktual     = (!empty($get_qty_aktual)) ? $get_qty_aktual[0]->qty_aktual : 0;
            $qty_produksi   = (!empty($get_qty_produksi)) ? $get_qty_produksi[0]->qty_produksi : 0;

            $stock          = $qty_aktual + $fg_booking;
            $balance_stock  = $item->totalwidth - $stock;
            $spk_produksi   = $qty_produksi;
            $balance_order  = $qty_produksi - $balance_stock;

            $hasil[] = [
                'no' => $no,
                'spk_marketing' => $item->no_surat,
                'customer' => $item->namacustomer,
                'alloy' => $item->no_alloy,
                'thickness' => number_format($item->thickness, 2),
                'width' => number_format($item->weight, 2),
                'delivery_date' => date('d F Y', strtotime($item->delivery)),
                'qty_order' => number_format($item->totalwidth, 2),
                'stock' => number_format($stock, 2),
                'balance_stock' => number_format($balance_stock, 2),
                'spk_produksi' => number_format($spk_produksi, 2),
                'balance_order' => number_format($balance_order, 2)
            ];
        endforeach;

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }
}
