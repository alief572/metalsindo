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

    public function get_control_po() {
        $draw = $this->input->post('draw');
        $length = $this->input->post('length');
        $start = $this->input->post('start');
        $search = $this->input->post('search');

        $this->db->select('a.*, d.no_surat, e.no_surat AS no_surat_po, b.no_po, c.name_suplier, b.width AS width_po, f.qty_order AS qty_po, SUM(f.width_recive) AS incoming, b.close_po, b.id_dt_po');
        $this->db->from('dt_trans_pr a');
        $this->db->join('tr_purchase_request d', 'a.no_pr = d.no_pr', 'left');
        $this->db->join('master_supplier c', 'a.suplier = c.id_suplier', 'left');
        $this->db->join('dt_trans_po b', 'a.id_dt_pr = b.idpr', 'left');
        $this->db->join('tr_purchase_order e', 'b.no_po=e.no_po', 'left');
        $this->db->join('dt_incoming f', 'f.id_dt_po=b.id_dt_po', 'left');
        $this->db->where('b.id_dt_po <>', '');
        if(!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('d.no_surat', $search['value'], 'both');
            $this->db->or_like('e.no_surat', $search['value'], 'both');
            $this->db->or_like('c.name_suplier', $search['value'], 'both');
            $this->db->or_like('a.nama_material', $search['value'], 'both');
            $this->db->or_like('b.width', $search['value'], 'both');
            $this->db->or_like('f.qty_order', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('b.id_dt_po');
        $this->db->limit($length, $start);

        $get_data = $this->db->get();

        $this->db->select('a.*, d.no_surat, e.no_surat AS no_surat_po, b.no_po, c.name_suplier, b.width AS width_po, f.qty_order AS qty_po, SUM(f.width_recive) AS incoming, b.close_po, b.id_dt_po');
        $this->db->from('dt_trans_pr a');
        $this->db->join('tr_purchase_request d', 'a.no_pr = d.no_pr', 'left');
        $this->db->join('master_supplier c', 'a.suplier = c.id_suplier', 'left');
        $this->db->join('dt_trans_po b', 'a.id_dt_pr = b.idpr', 'left');
        $this->db->join('tr_purchase_order e', 'b.no_po=e.no_po', 'left');
        $this->db->join('dt_incoming f', 'f.id_dt_po=b.id_dt_po', 'left');
        $this->db->where('b.id_dt_po <>', '');
        if(!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('d.no_surat', $search['value'], 'both');
            $this->db->or_like('e.no_surat', $search['value'], 'both');
            $this->db->or_like('c.name_suplier', $search['value'], 'both');
            $this->db->or_like('a.nama_material', $search['value'], 'both');
            $this->db->or_like('b.width', $search['value'], 'both');
            $this->db->or_like('f.qty_order', $search['value'], 'both');
            $this->db->group_end();
        }
        $this->db->group_by('b.id_dt_po');

        $get_data_all = $this->db->get();

        $no = (0 + $start);
        $hasil = [];

        foreach($get_data->result() as $item) {
            $no++;

            $option = '<button type="button" class="btn btn-sm btn-primary detail" data-id_po="'.$item->id_dt_po.'"><i class="fa fa-eye"></i></button>';

            if($item->close_po == 'N') {
                $option .= ' <button> type="button" class="btn btn-sm btn-success checked" data-id_po="'.$item->id_dt_po.'"><i class="fa fa-check"></i></button>';
            }

            $hasil[] = [
                'no' => $no,
                'no_pr' => $item->no_pr,
                'no_po' => $item->no_surat_po,
                'supplier' => $item->name_supplier,
                'material' => $item->nama_material,
                'width' => number_format($item->width_po, 2),
                'qty_order' => number_format($item->qty_po, 2),
                'qty_receive' => number_format($item->incoming, 2),
                'balance' => number_format(($item->qty_po - $item->incoming), 2),
                'option' => $option
            ];
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $get_data_all->num_rows(),
            'recordsFiltered' => $get_data_all->num_rows(),
            'data' => $hasil
        ]);
    }
}
