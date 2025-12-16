<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Control_po_model extends BF_Model
{
    protected $viewPermission     = 'Control_PO.View';
    protected $addPermission      = 'Control_PO.Add';
    protected $managePermission = 'Control_PO.Manage';
    protected $deletePermission = 'Control_PO.Delete';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_control_po()
    {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $this->db->select('a.*');
        $this->db->from('view_control_po a');

        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.no_surat', $search['value'], 'both');
            $this->db->or_like('a.no_surat', $search['value'], 'both');
            $this->db->or_like('a.name_suplier', $search['value'], 'both');
            $this->db->or_like('a.nama_material', $search['value'], 'both');
            $this->db->or_like('a.width', $search['value'], 'both');
            $this->db->or_like('a.qty_order', $search['value'], 'both');
            $this->db->group_end();
        }

        $db_clone2 = clone $this->db;
        $count_filtered = $db_clone2->count_all_results();

        $this->db->limit($length, $start);
        $get_data = $this->db->get();

        $no = (0 + $start);
        $hasil = [];

        foreach ($get_data->result() as $item) {
            $no++;

            $option = '<button type="button" class="btn btn-sm btn-primary detail" data-id_po="' . $item->id_dt_po . '"><i class="fa fa-eye"></i></button>';

            if ($item->close_po == 'N') {
                $option .= ' <button type="button" class="btn btn-sm btn-success checked" data-id_po="' . $item->id_dt_po . '"><i class="fa fa-check"></i></button>';
            }

            $get_incoming = $this->db->get_where('dt_incoming', array('id_dt_po' => $item->id_dt_po))->row();

            $incoming = (!empty($get_incoming->width_recive)) ? $get_incoming->width_recive : 0;

            $status = '<span class="badge bg-green">Open</span>';
            if ($item->close_po == 'Y') {
                $status = '<span class="badge bg-red">Closed</span>';
            }

            $hasil[] = [
                'no' => $no,
                'no_pr' => $item->no_pr,
                'no_po' => $item->no_surat_po,
                'supplier' => $item->name_suplier,
                'material' => $item->nama_material,
                'width' => number_format($item->width_po, 2),
                'qty_order' => number_format($item->qty_po, 2),
                'qty_receive' => number_format($incoming, 2),
                'balance' => number_format(($item->qty_po - $incoming), 2),
                'status' => $status,
                'option' => $option
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $count_all,
            'recordsFiltered' => $count_filtered,
            'data' => $hasil
        ]);
    }
}
