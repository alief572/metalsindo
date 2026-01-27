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
        $draw   = $this->input->post('draw');
        $length = $this->input->post('length');
        $start  = $this->input->post('start');
        $search = $this->input->post('search');
        $order  = $this->input->post('order'); // Ambil data order dari DataTable

        // Mapping kolom: Indeks array harus sesuai dengan urutan kolom di HTML/JS
        $column_order = [
            1 => 'a.no_pr',
            2 => 'a.no_surat_po',
            3 => 'a.name_suplier',
            4 => 'a.nama_material',
            5 => 'a.width_po',
            6 => 'a.qty_po',
            7 => 'a.qty_po', // Placeholder untuk receive/balance jika perlu
            8 => 'a.qty_po',
            9 => 'a.close_po'
        ];

        $this->db->select('a.*');
        $this->db->from('view_control_po a');

        // Count All (Tanpa Filter)
        $db_clone = clone $this->db;
        $count_all = $db_clone->count_all_results();

        // Filtering (Search)
        if (!empty($search['value'])) {
            $this->db->group_start();
            $this->db->like('a.no_surat', $search['value'], 'both');
            $this->db->or_like('a.no_surat_po', $search['value'], 'both');
            $this->db->or_like('a.name_suplier', $search['value'], 'both');
            $this->db->or_like('a.nama_material', $search['value'], 'both');
            $this->db->group_end();
        }

        // --- FITUR ORDER BY DINAMIS ---
        if (isset($order) && isset($column_order[$order[0]['column']])) {
            $this->db->order_by($column_order[$order[0]['column']], $order[0]['dir']);
        } else {
            $this->db->order_by('a.no_surat_po', 'DESC'); // Default sorting
        }
        // ------------------------------

        // Count Filtered
        $db_clone2 = clone $this->db;
        $count_filtered = $db_clone2->count_all_results();

        // Limit & Offset
        $this->db->limit($length, $start);
        $get_data = $this->db->get();

        $no = $start;
        $hasil = [];

        foreach ($get_data->result() as $item) {
            $no++;

            // Ambil data incoming (Cukup panggil sekali saja)
            $get_incoming = $this->db->get_where('dt_incoming', array('id_dt_po' => $item->id_dt_po))->row();
            $incoming = (!empty($get_incoming->width_recive)) ? $get_incoming->width_recive : 0;

            // Status badge
            $status = ($item->close_po == 'Y')
                ? '<span class="badge bg-red">Closed</span>'
                : '<span class="badge bg-green">Open</span>';

            // Tombol aksi
            $option = '<button type="button" class="btn btn-sm btn-primary detail" data-id_po="' . $item->id_dt_po . '"><i class="fa fa-eye"></i></button>';
            if ($item->close_po == 'N') {
                $option .= ' <button type="button" class="btn btn-sm btn-success checked" data-id_po="' . $item->id_dt_po . '"><i class="fa fa-check"></i></button>';
            }

            $hasil[] = [
                'no'          => $no,
                'no_pr'       => $item->no_pr,
                'no_po'       => $item->no_surat_po,
                'supplier'    => $item->name_suplier,
                'material'    => $item->nama_material,
                'width'       => number_format($item->width_po, 2),
                'qty_order'   => number_format($item->qty_po, 2),
                'qty_receive' => number_format($incoming, 2),
                'balance'     => number_format(($item->qty_po - $incoming), 2),
                'status'      => $status,
                'option'      => $option
            ];
        }

        echo json_encode([
            'draw'            => intval($draw),
            'recordsTotal'    => $count_all,
            'recordsFiltered' => $count_filtered,
            'data'            => $hasil
        ]);
    }
}
