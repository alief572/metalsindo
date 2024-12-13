<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ros extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'ROS.View';
    protected $addPermission    = 'ROS.Add';
    protected $managePermission = 'ROS.Manage';
    protected $deletePermission = 'ROS.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('Ros/ros_model', 'all/All_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session  = $this->session->userdata('app_session');

        $this->template->title('ROS');
        $this->template->render('index');
    }

    public function add()
    {
        $this->auth->restrict($this->addPermission);
        $session  = $this->session->userdata('app_session');

        $list_ros_no_po = $this->ros_model->list_po_no_ros();
        $list_custom_pib = $this->ros_model->list_custom_pib($this->auth->user_id());

        $this->db->select('a.*');
        $this->db->from('master_supplier a');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.suplier_location', 'international');
        $this->db->order_by('a.name_suplier', 'asc');
        $list_supplier = $this->db->get()->result();

        $this->template->set('list_ros_no_po', $list_ros_no_po);
        $this->template->set('list_custom_pib', $list_custom_pib);
        $this->template->set('list_supplier', $list_supplier);
        $this->template->title('Add ROS');
        $this->template->render('add');
    }

    public function edit($no_ros)
    {
        $this->auth->restrict($this->managePermission);
        $session  = $this->session->userdata('app_session');

        $list_ros_no_po = $this->ros_model->list_po_no_ros();
        $list_custom_pib = $this->ros_model->list_custom_pib($no_ros);
        $get_ros = $this->db->get_where('tr_ros', ['id' => $no_ros])->row_array();
        // $get_ros_detail = $this->db->get_where('tr_ros_detail', ['no_ros' => $no_ros])->result_array();
        $this->db->select('a.*');
        $this->db->from('master_supplier a');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.name_suplier', 'asc');
        $list_supplier = $this->db->get()->result();

        // $this->db->select('a.*, IF(d.code IS NULL, IF(e.code IS NULL, IF(h.code IS NULL, "Pcs", h.code), e.code), d.code) as unit_satuan');
        // $this->db->from('tr_ros_detail a');
        // $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_barang', 'left');
        // $this->db->join('accessories c', 'c.id = a.id_barang', 'left');
        // $this->db->join('dt_trans_po f', 'f.id = a.id_po_detail AND f.tipe IS NOT NULL', 'left');
        // $this->db->join('rutin_non_planning_detail g', 'g.id = f.idpr', 'left');
        // $this->db->join('ms_satuan d', 'b.id_unit = d.id', 'left');
        // $this->db->join('ms_satuan e', 'c.id_unit_gudang = e.id', 'left');
        // $this->db->join('ms_satuan h', 'g.satuan = h.id', 'left');
        // $this->db->where('a.no_ros', $no_ros);
        // $this->db->group_by('a.id');
        // $get_ros_detail = $this->db->get()->result_array();

        $this->db->select('a.*, "Pcs" as unit_satuan');
        $this->db->from('tr_ros_detail a');
        $this->db->where('a.no_ros', $no_ros);
        $this->db->where('a.tipe_po', 'PO Material');
        $this->db->group_by('a.id');
        $query1 = $this->db->get_compiled_select();

        $this->db->select('a.*, IF(d.nama IS NULL, IF(f.nama IS NULL, "Pcs", f.nama), d.nama) as unit_satuan');
        $this->db->from('tr_ros_detail a');
        $this->db->join('dt_trans_po_non_material b', 'b.id = a.id_po_detail', 'left');
        $this->db->join('rutin_non_planning_detail c', 'c.id = b.idpr', 'left');
        $this->db->join('ms_satuan d', 'd.id = c.satuan', 'left');
        $this->db->join('accessories e', 'e.id = a.id_barang', 'left');
        $this->db->join('ms_satuan f', 'f.id = e.id_unit_gudang', 'left');
        $this->db->where('a.no_ros', $no_ros);
        $this->db->group_by('a.id');
        $query2 = $this->db->get_compiled_select();

        $sql_query = $query1 . ' UNION ALL ' . $query2;

        // $sql_query = $query1;

        $get_ros_detail = $this->db->query($sql_query)->result_array();

        $this->template->set('list_ros_no_po', $list_ros_no_po);
        $this->template->set('list_custom_pib', $list_custom_pib);
        $this->template->set('header_ros', $get_ros);
        $this->template->set('detail_ros', $get_ros_detail);
        $this->template->set('list_supplier', $list_supplier);
        $this->template->title('Edit ROS');
        $this->template->render('add');
    }

    public function view($no_ros)
    {
        $this->auth->restrict($this->managePermission);
        $session  = $this->session->userdata('app_session');

        $list_ros_no_po = $this->ros_model->list_po_no_ros();
        $list_custom_pib = $this->ros_model->list_custom_pib($no_ros);
        $get_ros = $this->db->get_where('tr_ros', ['id' => $no_ros])->row_array();

        $this->db->select('a.*, "Pcs" as unit_satuan');
        $this->db->from('tr_ros_detail a');
        $this->db->where('a.no_ros', $no_ros);
        $this->db->where('a.tipe_po', 'PO Material');
        $this->db->group_by('a.id');
        $query1 = $this->db->get_compiled_select();

        // $this->db->select('a.*, IF(d.nama IS NULL, f.nama, d.nama) as unit_satuan');
        // $this->db->from('tr_ros_detail a');
        // $this->db->join('dt_trans_po_non_material b', 'b.id = a.id_po_detail', 'left');
        // $this->db->join('rutin_non_planning_detail c', 'c.id = b.idpr', 'left');
        // $this->db->join('ms_satuan d', 'd.id = c.satuan', 'left');
        // $this->db->join('accessories e', 'e.id = a.id_barang', 'left');
        // $this->db->join('ms_satuan f', 'f.id = e.id_unit_gudang', 'left');
        // $this->db->where('a.no_ros', $no_ros);
        // $this->db->group_by('a.id');
        // $query2 = $this->db->get_compiled_select();

        $sql_query = $query1;

        $get_ros_detail = $this->db->query($sql_query)->result_array();

        // print_r($this->db->error($get_ros_detail));
        // exit;

        $this->template->set('list_ros_no_po', $list_ros_no_po);
        $this->template->set('list_custom_pib', $list_custom_pib);
        $this->template->set('header_ros', $get_ros);
        $this->template->set('detail_ros', $get_ros_detail);
        $this->template->title('View ROS');
        $this->template->render('view');
    }

    public function data_side_ros()
    {
        $this->ros_model->data_side_ros();
    }

    public function get_no_po_detail()
    {
        $post = $this->input->post();

        $this->db->select('a.*');
        $this->db->from('tr_purchase_order a');
        $this->db->where_in('a.no_surat', explode(',', $post['no_po']));
        $get_id_po = $this->db->get()->row_array();

        $hasil = '';

        $no = 1;

        $this->db->select('a.id, a.no_po, a.id_dt_po, a.idpr, a.idmaterial, a.namamaterial, a.description, a.totalwidth, a.qty, a.hargasatuan, a.jumlahharga, b.matauang, "Pcs" as unit_satuan, "PO Material" as tipe_po');
        $this->db->from('dt_trans_po a');
        $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po');
        $this->db->join('ms_inventory_new c', 'c.id_category3 = a.idmaterial', 'left');
        $this->db->where_in('b.no_surat', explode(',', $post['no_po']));
        $this->db->group_by('a.id');
        $query1 = $this->db->get_compiled_select();

        $this->db->select('a.id, a.no_po, a.id_dt_po, a.idpr, a.idmaterial, a.namamaterial, a.description, a.width, a.qty, a.hargasatuan, a.jumlahharga, b.matauang, IF(h.nama IS NULL, "Pcs", h.nama) as unit_satuan, "PO Non Material" as tipe_po');
        $this->db->from('dt_trans_po_non_material a');
        $this->db->join('tr_purchase_order_non_material b', 'b.no_po = a.no_po');
        $this->db->join('accessories d', 'd.id = a.idmaterial', 'left');
        $this->db->join('rutin_non_planning_detail e', 'e.id = a.idpr', 'left');
        $this->db->join('ms_satuan h', 'h.id = e.satuan', 'left');
        $this->db->where_in('b.no_surat', explode(',', $post['no_po']));
        $this->db->group_by('a.id');
        $query2 = $this->db->get_compiled_select();

        $sql_query = $query1 . ' UNION ALL ' . $query2;

        // $sql_query = $query1;

        // $this->db->select('a.*, IF(f.code IS NULL, IF(g.code IS NULL, IF(h.code IS NULL, "Pcs", h.code), g.code), f.code) as unit_satuan');
        // $this->db->from('dt_trans_po a');
        // $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po');
        // $this->db->join('new_inventory_4 c', 'c.code_lv4 = a.idmaterial', 'left');
        // $this->db->join('accessories d', 'd.id = a.idmaterial', 'left');
        // $this->db->join('rutin_non_planning_detail e', 'e.id = a.idpr', 'left');
        // $this->db->join('ms_satuan f', 'f.id = c.id_unit', 'left');
        // $this->db->join('ms_satuan g', 'g.id = d.id_unit_gudang', 'left');
        // $this->db->join('ms_satuan h', 'h.id = e.satuan', 'left');
        // $this->db->where_in('b.no_surat', explode(',', $post['no_po']));
        // $this->db->group_by('a.id');
        $get_detail_po = $this->db->query($sql_query)->result_array();
        // print_r($this->db->error($get_detail_po));
        // exit;
        // $get_detail_po = $this->db->get_where('dt_trans_po', ['no_po' => $get_id_po['no_po']])->result_array();
        $ttl_price_detail = 0;
        $ttl_nilai_freight = 0;
        foreach ($get_detail_po as $item_po) {

            $nilai_pengurang = 0;
            $this->db->select('IF(SUM(a.qty_packing_list) IS NULL, 0, SUM(a.qty_packing_list)) as nilai_pengurang');
            $this->db->from('tr_ros_detail a');
            $this->db->where('a.id_po_detail', $item_po['id']);
            $get_nilai_ros_used = $this->db->get()->row_array();
            if (!empty($get_nilai_ros_used)) {
                $nilai_pengurang += $get_nilai_ros_used['nilai_pengurang'];
            }

            $valids = 1;

            if ($item_po['tipe_po'] == 'PO Material') {
                if (($item_po['totalwidth'] - $nilai_pengurang) <= 0) {
                    $valids = 0;
                }
            } else {
                if (($item_po['qty'] - $nilai_pengurang) <= 0) {
                    $valids = 0;
                }
            }

            $totalwidth = $item_po['totalwidth'];
            if ($item_po['tipe_po'] !== 'PO Material') {
                $totalwidth = $item_po['qty'];
            }

            if ($post['edit'] == '1') {
                $valids = 1;
            }

            if ($valids > 0) {

                $nilai_freight = (($totalwidth * $post['standard_logic_cost']) * $post['kurs_pib']);

                $hasil .= '<tr>';
                $hasil .= '<td class="text-center">' . $no . '</td>';
                $hasil .= '<td class="text-center">' . $item_po['namamaterial'] . '</td>';
                $hasil .= '<td class="text-center">' . ucfirst($item_po['unit_satuan']) . '</td>';
                $hasil .= '<td class="text-center">' . strtoupper($item_po['matauang']) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item_po['hargasatuan'], 2) . '</td>';
                $hasil .= '<td class="text-right">' . number_format($item_po['hargasatuan'] * $post['kurs_pib'], 2) . '</td>';
                $hasil .= '<td class="text-center">' . number_format($totalwidth, 2) . '</td>';
                $hasil .= '<td class="text-center">';
                $hasil .= number_format($nilai_pengurang, 2);
                $hasil .= '<input type="hidden" name="in_qty_' . $item_po['id'] . '" value="' . $nilai_pengurang . '">';
                $hasil .= '</td>';
                $hasil .= '<td class="text-center">';
                $hasil .= number_format(($totalwidth - $nilai_pengurang), 2);
                $hasil .= '<input type="hidden" name="sisa_qty_' . $item_po['id'] . '" value="' . ($totalwidth - $nilai_pengurang) . '">';
                $hasil .= '</td>';
                $hasil .= '<td class="text-center">';
                $hasil .= '<input type="text" class="form-control form-control-sm text-right weight_packing auto_num" name="weight_packing_' . $item_po['id'] . '" value="' . ($totalwidth - $nilai_pengurang) . '" data-id="' . $item_po['id'] . '">';
                $hasil .= '<input type="hidden" name="harga_satuan_' . $item_po['id'] . '" value="' . $item_po['hargasatuan'] . '">';
                $hasil .= '<input type="hidden" class="grand_total grand_total_' . $item_po['id'] . '" value="' . ((($totalwidth - $nilai_pengurang) * $item_po['hargasatuan']) * $post['kurs_pib']) . '">';
                $hasil .= '</td>';
                $hasil .= '<td class="text-center">';
                $hasil .= '<input type="hidden" class="total_price" value="' . ($item_po['jumlahharga'] * $post['kurs_pib']) . '">';
                $hasil .= '<input type="text" class="form-control form-control-sm text-right auto_num nilai_bm" name="nilai_bm_' . $item_po['id'] . '">';
                $hasil .= '</td>';
                $hasil .= '<td class="text-center">';
                $hasil .= '<input type="hidden" name="weight_' . $item_po['id'] . '" class="weight_' . $no . '" value="' . $totalwidth . '">';
                $hasil .= '<input type="text" class="form-control form-control-sm text-right auto_num nilai_freight" name="nilai_freight_' . $item_po['id'] . '" value="' . number_format($nilai_freight, 2) . '" data-no="' . $no . '" readonly>';

                $hasil .= '</td>';
                $hasil .= '<td class="text-right total_price_' . $item_po['id'] . '">';
                $hasil .= number_format((($totalwidth - $nilai_pengurang) * $item_po['hargasatuan']) * $post['kurs_pib'], 2);
                $hasil .= '</td>';
                $hasil .= '</tr>';
                $no++;

                $ttl_price_detail += ((($totalwidth - $nilai_pengurang) * $item_po['hargasatuan']) * $post['kurs_pib']);
                $ttl_nilai_freight += $nilai_freight;
            }
        }

        echo json_encode([
            'list_detail_pr' => $hasil,
            'ttl_price_detail' => $ttl_price_detail,
            'ttl_nilai_freight' => $ttl_nilai_freight
        ]);
    }

    public function add_custom_pembiayaan()
    {
        $post = $this->input->post();

        $no_ros = ($post['no_ros'] == 'new') ? $this->auth->user_id() : $post['no_ros'];

        $this->db->trans_begin();

        $this->db->insert('tr_ros_custom_pib', [
            'no_ros' => $no_ros,
            'nm_item_pembiayaan' => $post['biaya_name'],
            'nilai_cost' => $post['cost_biaya'],
            'created_by' => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s')
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function hitung_custom_pib()
    {
        $no_ros = $this->input->post('no_ros');
        if ($no_ros == 'new') {
            $no_ros = $this->auth->user_id();
        }

        $nilai = 0;
        $get_custom_pib = $this->db->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->result_array();
        foreach ($get_custom_pib as $item) {
            $nilai += $item['nilai_cost'];
        }

        echo json_encode(['ttl_custom_pib' => $nilai]);
    }

    public function refresh_list_pib()
    {
        $no_ros = $this->input->post('no_ros');
        if ($no_ros == 'new') {
            $no_ros = $this->auth->user_id();
        }

        $hasil = '';

        $no = 4;
        $get_custom_pib = $this->db->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->result_array();
        foreach ($get_custom_pib as $item) {
            $hasil .= '<tr>';
            $hasil .= '<td class="text-center">' . $no . '</td>';
            $hasil .= '<td class="text-center">' . $item['nm_item_pembiayaan'] . '</td>';
            $hasil .= '<td class="text-center">
            <input type="text" name="cost_pib_' . $item['id'] . '" id="" class="form-control form-control-sm auto_num text-right cost_pib_custom cost_pib_custom_' . $item['id'] . '" data-id="' . $item['id'] . '" value="' . $item['nilai_cost'] . '">
            </td>';
            $hasil .= '<td class="text-center">
                <button type="button" class="btn btn-sm btn-danger del_custom_pib" data-id="' . $item['id'] . '"><i class="fa fa-trash"></i></button>
            </td>';
            $hasil .= '</tr>';

            $no++;
        }

        echo json_encode([
            'hasil' => $hasil
        ]);
    }

    public function del_custom_pib()
    {
        $post = $this->input->post();

        $this->db->trans_begin();

        $this->db->delete('tr_ros_custom_pib', ['id' => $post['id']]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo $valid;
    }

    public function save_ros()
    {
        $post = $this->input->post();

        $config['upload_path'] = './uploads/ros'; //path folder
        $config['allowed_types'] = '*'; //type yang dapat diakses bisa anda sesuaikan
        $config['max_size'] = 100000000; // Maximum file size in kilobytes (2MB).
        $config['encrypt_name'] = TRUE; // Encrypt the uploaded file's name.
        $config['remove_spaces'] = FALSE; // Remove spaces from the file name.

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        $this->db->trans_begin();

        $valid = 1;
        if ($post['no_ros'] == 'new') {
            $no_ros = $this->ros_model->generate_no_ros();

            $upload_pib = '';

            if ($this->upload->do_upload('upload_pib')) {
                $data_upload_po = $this->upload->data();
                $upload_pib = 'uploads/ros/' . $data_upload_po['file_name'];
            }

            $get_supplier = $this->db->get_where('master_supplier', ['id_suplier' => $post['supplier_name']])->row_array();

            $insert_ros = $this->db->insert('tr_ros', [
                'id' => $no_ros,
                'no_po' => implode(',', $post['no_po']),
                'id_supplier' => $post['supplier_name'],
                'nm_supplier' => $get_supplier['name_suplier'],
                'link_doc' => $upload_pib,
                'kurs_pib' => str_replace(',', '', $post['kurs_pib']),
                'cost_bm' => str_replace(',', '', $post['cost_bm']),
                'cost_ppn' => str_replace(',', '', $post['cost_ppn']),
                'cost_pph' => str_replace(',', '', $post['cost_pph']),
                'awb_bl_date' => $post['awb_bl_date'],
                'awb_bl_number' => $post['awb_bl_number'],
                'eta_warehouse' => $post['eta_warehouse'],
                'ata_pod' => $post['ata_pod'],
                'no_pengajuan_pib' => $post['no_pengajuan_pib'],
                'no_biling' => $post['no_billing'],
                'freight_cost' => $post['ttl_freight_cost'],
                'standard_logic_cost' => $post['standard_logic_cost'],
                'keterangan' => $post['keterangan'],
                'created_by' => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s')
            ]);

            // $get_po_detail = $this->db->get_where('dt_trans_po', ['no_po' => $get_po['no_po']])->result_array();
            $this->db->select('a.no_po, a.id, a.qty, a.idmaterial, a.namamaterial, a.hargasatuan, "PO Material" as tipe_po');
            $this->db->from('dt_trans_po a');
            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
            $this->db->where_in('b.no_surat', $post['no_po']);
            $query1 = $this->db->get_compiled_select();

            $this->db->select('a.no_po, a.id, a.qty, a.idmaterial, a.namamaterial, a.hargasatuan, "PO Non Material" as tipe_po');
            $this->db->from('dt_trans_po_non_material a');
            $this->db->join('tr_purchase_order_non_material b', 'b.no_po = a.no_po', 'left');
            $this->db->where_in('b.no_surat', $post['no_po']);
            $query2 = $this->db->get_compiled_select();

            $sql_query = $query1 . ' UNION ALL ' . $query2;

            $get_po_detail = $this->db->query($sql_query)->result_array();

            // print_r($get_po_detail);
            // exit;

            foreach ($get_po_detail as $po_detail) {
                $mata_uang = '';
                $get_po_curr = $this->db->select('matauang')->get_where('tr_purchase_order', ['no_po' => $po_detail['no_po']])->row_array();
                if (!empty($get_po_curr)) {
                    $mata_uang = $get_po_curr['matauang'];
                }

                $get_po_curr = $this->db->select('matauang')->get_where('tr_purchase_order_non_material', ['no_po' => $po_detail['no_po']])->row_array();
                if (!empty($get_po_curr)) {
                    $mata_uang = $get_po_curr['matauang'];
                }

                $this->db->insert('tr_ros_detail', [
                    'id_po_detail' => $po_detail['id'],
                    'no_po' => implode(',', $post['no_po']),
                    'no_ros' => $no_ros,
                    'id_barang' => $po_detail['idmaterial'],
                    'nm_barang' => $po_detail['namamaterial'],
                    'currency' => $mata_uang,
                    'price_unit' => $po_detail['hargasatuan'],
                    'qty_po' => $post['weight_' . $po_detail['id']],
                    'in_qty' => str_replace(',', '', $post['in_qty_' . $po_detail['id']]),
                    'sisa_qty' => str_replace(',', '', $post['sisa_qty_' . $po_detail['id']]),
                    'qty_packing_list' => str_replace(',', '', $post['weight_packing_' . $po_detail['id']]),
                    'nilai_bm' => str_replace(',', '', $post['nilai_bm_' . $po_detail['id']]),
                    'nilai_freight' => str_replace(',', '', $post['nilai_freight_' . $po_detail['id']]),
                    'tipe_po' => 'PO Material',
                    'created_by' => $this->auth->user_id(),
                    'created_on' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->update('tr_ros_custom_pib', [
                'no_ros' => $no_ros
            ], [
                'no_ros' => $this->auth->user_id()
            ]);
        } else {

            $upload_pib = '';

            if ($this->upload->do_upload('upload_pib')) {
                $data_upload_po = $this->upload->data();
                $upload_pib = 'uploads/ros/' . $data_upload_po['file_name'];
            } else {
                $get_ros = $this->db->get_where('tr_ros', ['id' => $post['no_ros']])->row_array();

                $upload_pib = $get_ros['link_doc'];
            }

            $this->db->update('tr_ros', [
                'no_po' => $post['no_po'],
                'link_doc' => $upload_pib,
                'kurs_pib' => str_replace(',', '', $post['kurs_pib']),
                'cost_bm' => str_replace(',', '', $post['cost_bm']),
                'cost_ppn' => str_replace(',', '', $post['cost_ppn']),
                'cost_pph' => str_replace(',', '', $post['cost_pph']),
                'freight_cost' => $post['ttl_freight_cost'],
                'awb_bl_date' => $post['awb_bl_date'],
                'awb_bl_number' => $post['awb_bl_number'],
                'eta_warehouse' => $post['eta_warehouse'],
                'ata_pod' => $post['ata_pod'],
                'no_pengajuan_pib' => $post['no_pengajuan_pib'],
                'no_biling' => $post['no_billing'],
                'keterangan' => $post['keterangan'],
                'standard_logic_cost' => $post['standard_logic_cost']
            ], [
                'id' => $post['no_ros']
            ]);

            $this->db->select('a.no_po, a.id, a.qty, a.idmaterial, a.namamaterial, a.hargasatuan, "PO Material" as tipe_po');
            $this->db->from('dt_trans_po a');
            $this->db->join('tr_purchase_order b', 'b.no_po = a.no_po', 'left');
            $this->db->where_in('b.no_surat', $post['no_po']);
            $query1 = $this->db->get_compiled_select();

            $this->db->select('a.no_po, a.id, a.qty, a.idmaterial, a.namamaterial, a.hargasatuan, "PO Non Material" as tipe_po');
            $this->db->from('dt_trans_po_non_material a');
            $this->db->join('tr_purchase_order_non_material b', 'b.no_po = a.no_po', 'left');
            $this->db->where_in('b.no_surat', $post['no_po']);
            $query2 = $this->db->get_compiled_select();

            $sql_query = $query1 . ' UNION ALL ' . $query2;

            $get_po_detail = $this->db->query($sql_query)->result_array();

            $this->db->delete('tr_ros_detail', ['no_ros' => $post['no_ros']]);
            foreach ($get_po_detail as $po_detail) {
                $mata_uang = '';
                $get_po_curr = $this->db->select('matauang')->get_where('tr_purchase_order', ['no_po' => $po_detail['no_po']])->row_array();
                if (!empty($get_po_curr)) {
                    $mata_uang = $get_po_curr['matauang'];
                }

                $get_po_curr = $this->db->select('matauang')->get_where('tr_purchase_order_non_material', ['no_po' => $po_detail['no_po']])->row_array();
                if (!empty($get_po_curr)) {
                    $mata_uang = $get_po_curr['matauang'];
                }

                $this->db->insert('tr_ros_detail', [
                    'id_po_detail' => $po_detail['id'],
                    'tipe_po' => $po_detail['tipe_po'],
                    'no_po' => $post['no_po'],
                    'no_ros' => $post['no_ros'],
                    'id_barang' => $po_detail['idmaterial'],
                    'nm_barang' => $po_detail['namamaterial'],
                    'currency' => $mata_uang,
                    'price_unit' => $po_detail['hargasatuan'],
                    'qty_po' => $post['weight_' . $po_detail['id']],
                    'qty_packing_list' => str_replace(',', '', $post['weight_packing_' . $po_detail['id']]),
                    'in_qty' => str_replace(',', '', $post['in_qty_' . $po_detail['id']]),
                    'sisa_qty' => str_replace(',', '', $post['sisa_qty_' . $po_detail['id']]),
                    'nilai_bm' => str_replace(',', '', $post['nilai_bm_' . $po_detail['id']]),
                    'nilai_freight' => str_replace(',', '', $post['nilai_freight_' . $po_detail['id']]),
                    'created_by' => $this->auth->user_id(),
                    'created_on' => date('Y-m-d H:i:s')
                ]);
            }
        }

        $msg = '';
        if ($this->db->trans_status() === false || $valid == 2) {
            $this->db->trans_rollback();
            $msg = 'Sorry, ROS not been saved !';
            if ($valid == 2) {
                $msg = 'Sorry, your qty packing list is greater than the remaining PO Qty !';
            }
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid,
            'msg' => $msg
        ]);
    }

    public function del_ros()
    {
        $no_ros = $this->input->post('no_ros');

        $this->db->trans_begin();

        $this->db->delete('tr_ros', ['id' => $no_ros]);
        $this->db->delete('tr_ros_detail', ['no_ros' => $no_ros]);
        $this->db->delete('tr_ros_custom_pib', ['no_ros' => $no_ros]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function req_payment_ros()
    {
        $no_ros = $this->input->post('no_ros');

        $get_ros = $this->db->get_where('tr_ros', ['id' => $no_ros])->row_array();
        $get_ros_custom_pib = $this->db->select('SUM(nilai_cost) as ttl_custom_pib')->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->row_array();

        $nilai_request = ($get_ros['cost_bm'] + $get_ros['cost_ppn'] + $get_ros['cost_pph'] + $get_ros_custom_pib['ttl_custom_pib']);

        $this->db->trans_begin();

        $get_nm_lengkap = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row_array();

        $no_doc = $this->All_model->GetAutoGenerate('format_expense');

        $insert_exp = $this->db->insert('tr_expense', [
            'no_doc' => $no_ros,
            'tgl_doc' => date('Y-m-d'),
            'departement' => '6',
            'nama' => $get_nm_lengkap['nm_lengkap'],
            'approval' => $get_nm_lengkap['nm_lengkap'],
            'status' => 1,
            'created_by' => $get_nm_lengkap['nm_lengkap'],
            'created_on' => date('Y-m-d H:i:s'),
            'approved_by' => $get_nm_lengkap['nm_lengkap'],
            'approved_on' => date('Y-m-d H:i:s'),
            'jumlah' => $nilai_request,
            'informasi' => 'Pembayaran PIB : ' . $get_ros['no_pengajuan_pib'] . '',
            'accnumber' => $get_ros['no_biling'],
            'exp_pib' => 1
        ]);
        if (!$insert_exp) {
            print_r($this->db->error($insert_exp));
            exit;
        }

        $arrData = array();

        $arrData[] = [
            'tanggal' => date('Y-m-d'),
            'no_doc' => $no_ros,
            'deskripsi' => 'BM',
            'qty' => 1,
            'harga' => $get_ros['cost_bm'],
            'total_harga' => $get_ros['cost_bm'],
            'keterangan' => 'Cost BM : ' . $get_ros['no_po'],
            'status' => 0,
            'expense' => $get_ros['cost_bm'],
            'created_by' => $get_nm_lengkap['nm_lengkap'],
            'created_on' => date('Y-m-d H:i:s')
        ];

        $arrData[] = [
            'tanggal' => date('Y-m-d'),
            'no_doc' => $no_ros,
            'deskripsi' => 'PPN',
            'qty' => 1,
            'harga' => $get_ros['cost_ppn'],
            'total_harga' => $get_ros['cost_ppn'],
            'keterangan' => 'Cost PPN : ' . $get_ros['no_po'],
            'status' => 0,
            'expense' => $get_ros['cost_ppn'],
            'created_by' => $get_nm_lengkap['nm_lengkap'],
            'created_on' => date('Y-m-d H:i:s')
        ];

        $arrData[] = [
            'tanggal' => date('Y-m-d'),
            'no_doc' => $no_ros,
            'deskripsi' => 'PPH',
            'qty' => 1,
            'harga' => $get_ros['cost_pph'],
            'total_harga' => $get_ros['cost_pph'],
            'keterangan' => 'Cost BM : ' . $get_ros['no_po'],
            'status' => 0,
            'expense' => $get_ros['cost_pph'],
            'created_by' => $get_nm_lengkap['nm_lengkap'],
            'created_on' => date('Y-m-d H:i:s')
        ];

        $list_ros_custom_pib = $this->db->get_where('tr_ros_custom_pib', ['no_ros' => $no_ros])->result_array();
        foreach ($list_ros_custom_pib as $item) {
            $arrData[] = [
                'tanggal' => date('Y-m-d'),
                'no_doc' => $no_ros,
                'deskripsi' => $item['nm_item_pembiayaan'],
                'qty' => 1,
                'harga' => $item['nilai_cost'],
                'total_harga' => $item['nilai_cost'],
                'keterangan' => 'Cost ' . $item['nm_item_pembiayaan'] . ' : ' . $get_ros['no_po'],
                'status' => 0,
                'expense' => $item['nilai_cost'],
                'created_by' => $get_nm_lengkap['nm_lengkap'],
                'created_on' => date('Y-m-d H:i:s')
            ];
        }

        $insert_exp_detail = $this->db->insert_batch('tr_expense_detail', $arrData);
        if (!$insert_exp_detail) {
            print_r($this->db->error($insert_exp_detail));
            exit;
        }

        $update_ros_sts = $this->db->update('tr_ros', ['sts' => 1], ['id' => $no_ros]);
        if (!$update_ros_sts) {
            print_r($this->db->error($update_ros_sts));
            exit;
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $valid = 0;
        } else {
            $this->db->trans_commit();
            $valid = 1;
        }

        echo json_encode([
            'status' => $valid
        ]);
    }

    public function get_po_by_supplier()
    {
        $kode_supplier = $this->input->post('supplier');

        $this->db->select('a.no_surat, a.no_po, "PO Material" as tipe_po');
        $this->db->from('tr_purchase_order a');
        $this->db->join('tr_ros b', 'b.no_po = a.no_surat', 'left');
        $this->db->where('a.id_suplier', $kode_supplier);
        $this->db->where('a.loi', 'Import');
        $this->db->where('a.status', '2');
        $this->db->order_by('a.no_surat', 'desc');
        $query1 = $this->db->get_compiled_select();

        // $this->db->select('a.no_surat, a.no_po, "PO Non Material" as tipe_po');
        // $this->db->from('tr_purchase_order_non_material a');
        // $this->db->join('tr_ros b', 'b.no_po = a.no_surat', 'left');
        // // $this->db->where('b.no_po', null);
        // $this->db->where('a.id_suplier', $kode_supplier);
        // $query2 = $this->db->get_compiled_select();

        // $sql_query = $query1 . ' UNION ALL ' . $query2;
        $sql_query = $query1;

        $list_po = $this->db->query($sql_query)->result_array();

        $hasil = '';
        foreach ($list_po as $item) {

            $nilai_sisa = 0;
            // if ($item['tipe_po'] == 'PO Material') {

            // } else {
            //     $get_po_detail = $this->db->get_where('dt_trans_po_non_material', ['no_po' => $item['no_po']])->result_array();
            //     foreach ($get_po_detail as $item2) {
            //         $nilai_sisa += $item2['qty'];

            //         $get_used_ros_detail = $this->db->get_where('tr_ros_detail', ['id_po_detail' => $item2['id']])->result_array();
            //         if (!empty($get_used_ros_detail)) {
            //             foreach ($get_used_ros_detail as $item3) {
            //                 $nilai_sisa -= $item3['qty_packing_list'];
            //             }
            //         }
            //     }
            // }

            $get_po_detail = $this->db->get_where('dt_trans_po', ['no_po' => $item['no_po']])->result_array();
            foreach ($get_po_detail as $item2) {

                $berat_terima = ($item2['totalwidth'] !== null) ? $item2['totalwidth'] : 0;

                $nilai_sisa += $berat_terima;

                $get_used_ros_detail = $this->db->get_where('tr_ros_detail', ['id_po_detail' => $item2['id']])->result_array();
                if (!empty($get_used_ros_detail)) {
                    foreach ($get_used_ros_detail as $item3) {
                        $nilai_sisa -= $item3['qty_packing_list'];
                    }
                }
            }

            // if($item['no_surat'] == 'PO-004/MP/03/2024'){
            //     print_r($nilai_sisa);
            //     exit;
            // }



            if ($nilai_sisa > 0) {
                $hasil .= '<tr>';
                $hasil .= '<td class="text-center">' . $item['no_surat'] . '</td>';
                $hasil .= '<td class="text-center">' . $item['tipe_po'] . '</td>';
                $hasil .= '<td class="text-center"><input type="checkbox" name="no_po[]" class="no_po" value="' . $item['no_surat'] . '"></td>';
                $hasil .= '</tr>';
            }
        }

        echo $hasil;
    }

    public function get_no_po()
    {
        $post = $this->input->post();

        $draw = $post['draw']; // Counter for DataTable request
        $start = $post['start']; // Starting record for pagination
        $length = $post['length']; // Number of records per page
        $searchValue = $post['search']['value']; // Search term entered by user

        $kode_supplier = $post['id_supplier'];

        $this->db->select('a.no_surat, a.no_po, "PO Material" as tipe_po');
        $this->db->from('tr_purchase_order a');
        $this->db->join('tr_ros b', 'b.no_po = a.no_surat', 'left');
        $this->db->where('a.id_suplier', $kode_supplier);
        $this->db->where('a.loi', 'Import');
        $this->db->where('a.status', '2');
        if (!empty($searchValue)) {
            $this->db->group_start();
            $this->db->like('a.no_surat', $searchValue, 'both');
            $this->db->group_end();
        }
        // $this->db->order_by('a.no_surat', 'desc');
        $query1 = $this->db->get_compiled_select();

        $this->db->select('a.no_surat, a.no_po, "PO Non Material" as tipe_po');
        $this->db->from('tr_purchase_order_non_material a');
        $this->db->join('tr_ros b', 'b.no_po = a.no_surat', 'left');
        $this->db->where('a.id_suplier', $kode_supplier);
        $this->db->where('a.loi', 'Import');
        if (!empty($searchValue)) {
            $this->db->group_start();
            $this->db->like('a.no_surat', $searchValue, 'both');
            $this->db->group_end();
        }
        // $this->db->order_by('a.no_surat', 'desc');
        $query2 = $this->db->get_compiled_select();

        $sql_query = $query1 . ' UNION ALL ' . $query2 . ' LIMIT ' . $length . ' OFFSET ' . $start;
        // $sql_query = $query1;

        $list_po = $this->db->query($sql_query)->result_array();

        // $this->db->select('a.no_surat, a.no_po, "PO Material" as tipe_po');
        // $this->db->from('tr_purchase_order a');
        // $this->db->join('tr_ros b', 'b.no_po = a.no_surat', 'left');
        // $this->db->where('a.id_suplier', $kode_supplier);
        // $this->db->where('a.loi', 'Import');
        // $this->db->where('a.status', '2');
        // if(!empty($searchValue)) {
        //     $this->db->group_start();
        //     $this->db->like('a.no_surat', $searchValue, 'both');
        //     $this->db->group_end();
        // }
        // $this->db->order_by('a.no_surat', 'desc');
        // $query1 = $this->db->get()->result();

        $hasil = [];

        foreach ($list_po as $item) {

            $nilai_sisa = 0;

            if ($item['tipe_po'] == 'PO Material') {
                $get_po_detail = $this->db->get_where('dt_trans_po', ['no_po' => $item['no_po']])->result_array();
                foreach ($get_po_detail as $item2) {

                    $berat_terima = ($item2['totalwidth'] !== null) ? $item2['totalwidth'] : 0;

                    $nilai_sisa += $berat_terima;

                    $get_used_ros_detail = $this->db->get_where('tr_ros_detail', ['id_po_detail' => $item2['id']])->result_array();
                    if (!empty($get_used_ros_detail)) {
                        foreach ($get_used_ros_detail as $item3) {
                            $nilai_sisa -= $item3['qty_packing_list'];
                        }
                    }
                }
            } else {
                $get_po_detail = $this->db->get_where('dt_trans_po_non_material', ['no_po' => $item['no_po']])->result_array();
                foreach ($get_po_detail as $item2) {

                    $berat_terima = ($item2['qty'] !== null) ? $item2['qty'] : 0;

                    $nilai_sisa += $berat_terima;

                    $get_used_ros_detail = $this->db->get_where('tr_ros_detail', ['id_po_detail' => $item2['id']])->result_array();
                    if (!empty($get_used_ros_detail)) {
                        foreach ($get_used_ros_detail as $item3) {
                            $nilai_sisa -= $item3['qty_packing_list'];
                        }
                    }
                }
            }

            // if($item['no_surat'] == 'PO-004/MP/03/2024'){
            //     print_r($nilai_sisa);
            //     exit;
            // }

            if ($nilai_sisa > 0) {
                $hasil[] = [
                    'no_po' => $item['no_surat'],
                    'tipe_po' => $item['tipe_po'],
                    'option' => '<input type="checkbox" name="no_po[]" class="no_po" value="' . $item['no_surat'] . '">'
                ];
            }
        }

        $no = 1;

        $this->db->select('a.no_surat, a.no_po, "PO Material" as tipe_po');
        $this->db->from('tr_purchase_order a');
        $this->db->join('tr_ros b', 'b.no_po = a.no_surat', 'left');
        $this->db->where('a.id_suplier', $kode_supplier);
        $this->db->where('a.loi', 'Import');
        $this->db->where('a.status', '2');
        if (!empty($searchValue)) {
            $this->db->group_start();
            $this->db->like('a.no_surat', $searchValue, 'both');
            $this->db->group_end();
        }
        $this->db->order_by('a.no_surat', 'desc');
        $get_data_all = $this->db->get()->result_array();

        foreach ($get_data_all as $item) {
            $nilai_sisa = 0;

            $get_po_detail = $this->db->get_where('dt_trans_po', ['no_po' => $item['no_po']])->result_array();
            foreach ($get_po_detail as $item2) {

                $berat_terima = ($item2['totalwidth'] !== null) ? $item2['totalwidth'] : 0;

                $nilai_sisa += $berat_terima;

                $get_used_ros_detail = $this->db->get_where('tr_ros_detail', ['id_po_detail' => $item2['id']])->result_array();
                if (!empty($get_used_ros_detail)) {
                    foreach ($get_used_ros_detail as $item3) {
                        $nilai_sisa -= $item3['qty_packing_list'];
                    }
                }
            }

            if ($nilai_sisa > 0) {
                $no++;
            }
        }

        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $no,
            'recordsFiltered' => $no,
            'data' => $hasil
        ]);
    }
}
