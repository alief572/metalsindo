<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kartu_stock extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Kartu_stock_material.View';
  protected $addPermission    = 'Kartu_stock_material.Add';
  protected $managePermission = 'Kartu_stock_material.Manage';
  protected $deletePermission = 'Kartu_stock_material.Delete';

  public function __construct()
  {
    parent::__construct();
    $this->load->model(array(
      'Kartu_stock/kartu_stock_model'
    ));
    $this->template->title('Manage Data Supplier');
    $this->template->page_icon('fa fa-building-o');

    date_default_timezone_set('Asia/Bangkok');
  }

  //==========================================================================================================
  //============================================STOCK=========================================================
  //==========================================================================================================

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');

    $list_warehouse = $this->kartu_stock_model->get_list_warehouse();

    $data = [
      'list_warehouse' => $list_warehouse
    ];
    // history("View data gudang pusat");
    $this->template->set($data);
    $this->template->title('Kartu Stock');
    $this->template->render('index');
  }

  public function _render_action_history($id_material, $id_gudang)
  {
    $btn_history = '<button class="btn btn-info btn-sm showHistory" data-id_material="' . $id_material . '" data-id_gudang="' . $id_gudang . '" title="List History"><i class="fa fa-list"></i></button>';

    return $btn_history;
  }

  public function data_side_stock()
  {
    // $this->kartu_stock_model->get_json_stock();

    $draw = $this->input->post('draw', true);
    $length = $this->input->post('length', true);
    $start = $this->input->post('start', true);
    $search = $this->input->post('search', true);
    $order = $this->input->post('order', true);

    $date_filter = $this->input->post('date_filter', true);
    $gudang_filter = $this->input->post('gudang_filter', true);

    $this->db->select('a.id_category3 as id_material, a.nama as nm_material, a.maker as nm_supplier');
    $this->db->from('ms_inventory_category3 a');
    $this->db->where('a.deleted', 0);

    // $this->db->select('a.id_material, a.nm_material, a.ttl_stock, a.ttl_booking, a.ttl_stock_free, b.maker as nm_supplier');
    // $this->db->from('v_stock_lot_realtime a');
    // $this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_material');
    // $this->db->where('a.id_gudang', $gudang_filter);

    $count_all = $this->db->count_all_results('', false);

    if (!empty($search['value'])) {
      $this->db->group_start();
      $this->db->like('a.nama', $search['value'], 'both');
      $this->db->or_like('a.id_category3', $search['value'], 'both');
      $this->db->or_like('a.maker', $search['value'], 'both');
      $this->db->group_end();
    }

    $count_filtered = $this->db->count_all_results('', false);

    if (!empty($order)) {
      $columns = ['a.id_category3', 'a.nama', 'b.maker'];
      $this->db->order_by($columns[$order[0]['column']], $order[0]['dir']);
    } else {
      $this->db->order_by('a.id_category3', 'DESC');
    }

    $this->db->limit($length, $start);

    $get_data = $this->db->get()->result_array();

    $no = (0 + $start);
    $data = [];

    $total_stock_unit = 0;
    $total_stock_booking = 0;
    $total_stock_avail = 0;
    foreach ($get_data as $item) {
      $no++;

      $stock_unit = $this->kartu_stock_model->get_stock($item['id_material'], $this->input->post('gudang_filter'));

      $qty_stock = (!empty($stock_unit->qty_stock)) ? $stock_unit->qty_stock : 0;
      $qty_booking = (!empty($stock_unit->qty_booking)) ? $stock_unit->qty_booking : 0;
      $qty_free = (!empty($stock_unit->qty_free)) ? $stock_unit->qty_free : 0;

      $action = $this->_render_action_history($item['id_material'], $gudang_filter);

      $data[] = [
        'no' => $no,
        'id_material' => $item['id_material'],
        'nama_material' => $item['nm_material'],
        'supplier' => $item['nm_supplier'],
        'stock_unit' => number_format($qty_stock, 2),
        'booking' => number_format($qty_booking, 2),
        'available' => number_format($qty_free, 2),
        'history' => $action
      ];

      $total_stock_unit += $qty_stock;
      $total_stock_booking += $qty_booking;
      $total_stock_avail += $qty_free;
    }

    $response = [
      'draw' => $draw,
      'recordsTotal' => $count_all,
      'recordsFiltered' => $count_filtered,
      'data' => $data,
      'total_stock_unit' => $total_stock_unit,
      'total_stock_booking' => $total_stock_booking,
      'total_stock_avail' => $total_stock_avail,
    ];

    echo json_encode($response);
  }

  public function modal_history()
  {
    $data     = $this->input->post();
    $gudang   = $data['gudang'];
    $material = $data['material'];

    // $sql = "SELECT a.* FROM warehouse_history a  WHERE a.id_gudang='" . $gudang . "' AND a.id_material='" . $material . "' ORDER BY a.id ASC ";
    // $data = $this->db->query($sql)->result_array();

    $data = $this->kartu_stock_model->get_history_stock($material, $gudang);

    $dataArr = array(
      'gudang' => $gudang,
      'material' => $material,
      'data'  => $data
    );
    $this->load->view('modal_history', $dataArr);
  }
  public function modal_lot_detail()
  {
    $data     = $this->input->post();
    $gudang   = $data['gudang'];
    $material = $data['material'];

    // $sql = "SELECT a.* FROM warehouse_history a WHERE a.id_gudang='" . $gudang . "' AND a.id_material='" . $material . "' ORDER BY a.id ASC ";
    // $data = $this->db->query($sql)->result_array();

    $data = $this->db->select('a.*, b.nm_lengkap, c.konversi as nil_kon')
      ->from('tr_checked_incoming_detail a')
      ->join('users b', 'b.id_user = a.created_by', 'left')
      ->join('new_inventory_4 c', 'c.code_lv4 = a.id_material', 'left')
      ->where('a.id_material', $material)
      ->where('a.sts', '1')
      ->where('(a.qty_oke - a.qty_used) >', 0)
      ->get()
      ->result_array();

    // print_r($data);
    // exit;

    $dataArr = array(
      'gudang' => $gudang,
      'material' => $material,
      'data'  => $data
    );
    $this->load->view('modal_lot_detail', $dataArr);
  }

  public function export_excel($material, $gudang)
  {

    $data = $this->kartu_stock_model->get_history_stock($material, $gudang);

    $dataArr = array(
      'gudang' => $gudang,
      'material' => $material,
      'data'  => $data
    );
    $this->load->view('excel_history', $dataArr);
  }

  public function download_excel()
  {
    $tanggal = $this->uri->segment(3);

    $get_material = $this->db->get_where('new_inventory_4', ['deleted_by' => null, 'category' => 'material'])->result_array();
    $get_satuan = $this->db->get_where('ms_satuan', ['deleted' => 'N'])->result_array();

    $list_packing = [];
    $list_unit = [];

    foreach ($get_satuan as $item_satuan) {
      if ($item_satuan['category'] == 'unit') {
        $list_unit[$item_satuan['id']] = $item_satuan['code'];
      } else {
        $list_packing[$item_satuan['id']] = $item_satuan['code'];
      }
    }


    if (date('Y-m-d', strtotime($tanggal)) == date('Y-m-d')) {
      $this->db->select('a.id_material, a.nm_material, a.qty_stock as stok');
      $this->db->from('warehouse_stock a');
      $this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_material');
      $this->db->where('a.id_gudang', 1);
      $this->db->group_by('a.id_material');
      $get_stok_material = $this->db->get()->result_array();
    } else {
      $this->db->select('a.id_material, a.nm_material, max(a.qty_stock) as stok');
      $this->db->from('warehouse_stock_per_day a');
      $this->db->where('DATE_FORMAT(a.hist_date, "%Y-%m-%d") = ', $tanggal);
      $this->db->where('a.id_gudang', 1);
      $this->db->group_by('a.id_material');
      $get_stok_material = $this->db->get()->result_array();
    }

    $list_stok = [];
    foreach ($get_stok_material as $item_stok) {
      $list_stok[$item_stok['id_material']] = $item_stok['stok'];
    }

    $data = [
      'list_material' => $get_material,
      'list_unit' => $list_unit,
      'list_packing' => $list_packing,
      'list_stok' => $list_stok,
      'tanggal' => $tanggal
    ];

    // $this->load->set('results', $data);
    $this->load->view('excel_stok_gudang_pusat', ['results' => $data]);
  }

  public function updateStock()
  {
    header('Content-Type: application/json');

    $this->db->select('a.id_material, a.nm_material, a.id_gudang, a.ttl_stock, a.ttl_booking, a.ttl_stock_free, b.wh_name as nm_gudang');
    $this->db->from('v_stock_lot_realtime a');
    $this->db->join('ms_warehouse b', 'b.id = a.id_gudang');
    $get_data = $this->db->get()->result();

    if (empty($get_data)) {
      echo json_encode(['status' => 1, 'message' => 'Data kosong, tidak ada yang diupdate.']);
      return;
    }

    $this->db->trans_begin();

    try {
      $arr_insert = [];
      $arr_insert_stock = [];
      $user_id = $this->auth->user_id();
      $now = date('Y-m-d H:i:s');

      foreach ($get_data as $item) {
        $arr_insert[] = [
          'id_material'      => $item->id_material,
          'idmaterial'       => $item->id_material,
          'nm_material'      => $item->nm_material,
          'id_gudang'        => $item->id_gudang,
          'kd_gudang'        => $item->nm_gudang,
          'id_gudang_ke'     => $item->id_gudang,
          'kd_gudang_ke'     => $item->nm_gudang,
          'qty_stock_awal'   => 0,
          'qty_stock_akhir'  => $item->ttl_stock, // Pakai ttl_stock sesuai info kamu
          'qty_booking_awal' => 0,
          'qty_booking_akhir' => $item->ttl_booking, // Pakai ttl_booking sesuai info kamu
          'no_ipp'           => 'BEGINNING',
          'jumlah_mat'       => $item->ttl_stock_free, // Biasanya history catat qty yang tersedia
          'ket'              => 'BEGINNING',
          'update_by'        => $user_id,
          'update_date'      => $now,
          'jenis_transaksi'  => 'BEGINNING'
        ];

        $arr_insert_stock[] = [
          'id_material'      => $item->id_material,
          'idmaterial'       => $item->id_material,
          'nm_material'      => $item->nm_material,
          'id_gudang'        => $item->id_gudang,
          'kd_gudang'        => $item->nm_gudang,
          'qty_stock' => $item->ttl_stock,
          'qty_booking' => $item->ttl_booking,
          'update_by'        => $user_id,
          'update_date'      => $now,
        ];
      }

      $this->db->insert_batch('warehouse_history', $arr_insert);
      $this->db->insert_batch('warehouse_stock', $arr_insert_stock);

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
        throw new Exception('Gagal melakukan insert batch ke history.');
      } else {
        $this->db->trans_commit();
        echo json_encode(['status' => 1, 'message' => 'Success update history']);
      }
    } catch (Exception $e) {
      $this->db->trans_rollback();
      http_response_code(500);
      echo json_encode([
        'status' => 0,
        'message' => $e->getMessage()
      ]);
    }
  }
}
