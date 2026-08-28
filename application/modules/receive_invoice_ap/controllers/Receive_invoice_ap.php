<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Receive_invoice_ap extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Receive_Invoice_AP.View';
  protected $addPermission    = 'Receive_Invoice_AP.Add';
  protected $managePermission = 'Receive_Invoice_AP.Manage';
  protected $deletePermission = 'Receive_Invoice_AP.Delete';

  public function __construct()
  {
    parent::__construct();
    $this->load->library(array('Mpdf', 'upload', 'Image_lib'));

    $this->load->model(array('Receive_invoice_ap/Receive_invoice_ap_model'));
    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session  = $this->session->userdata('app_session');
    $this->template->title('Receive Invoice AP');
    $this->template->render('index');
  }

  public function add()
  {

    $this->db->select('a.id_suplier, b.name_suplier');
    $this->db->from('tr_incoming a');
    $this->db->join('master_supplier b', 'b.id_suplier = a.id_suplier');
    $this->db->where('a.no_invoice_rec_ap', null);
    $this->db->or_where('a.no_invoice_rec_ap', '');
    $this->db->group_by('a.id_suplier');
    $this->db->order_by('b.name_suplier', 'asc');
    $get_supplier = $this->db->get()->result();

    $data = [
      'list_supplier' => $get_supplier
    ];

    $this->template->set($data);
    $this->template->render('add');
  }

  public function view($id_rec_inv_ap)
  {
    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_header a');
    $this->db->where('a.id_rec_inv_ap', $id_rec_inv_ap);
    $get_data_header = $this->db->get()->row();

    $this->db->select('a.*, b.sj_supplier as no_sj_supplier');
    $this->db->from('tr_receive_invoice_ap_detail a');
    $this->db->join('tr_incoming b', 'b.id_incoming = a.id_incoming', 'left');
    $this->db->where('a.id_rec_inv_ap', $id_rec_inv_ap);
    $get_data_detail = $this->db->get()->result();

    $this->db->select('a.*');
    $this->db->from('master_supplier a');
    $this->db->where('a.deleted', 0);
    $get_supplier = $this->db->get()->result();

    $data = [
      'header' => $get_data_header,
      'detail' => $get_data_detail,
      'list_supplier' => $get_supplier
    ];

    $this->template->set($data);
    $this->template->render('view');
  }

  public function edit($id_rec_inv_ap)
  {
    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_header a');
    $this->db->where('a.id_rec_inv_ap', $id_rec_inv_ap);
    $get_data_header = $this->db->get()->row();

    $this->db->select('a.*, b.sj_supplier as no_sj_supplier');
    $this->db->from('tr_receive_invoice_ap_detail a');
    $this->db->join('tr_incoming b', 'b.id_incoming = a.id_incoming', 'left');
    $this->db->where('a.id_rec_inv_ap', $id_rec_inv_ap);
    $get_data_detail = $this->db->get()->result();

    $this->db->select('a.*');
    $this->db->from('master_supplier a');
    $this->db->where('a.deleted', 0);
    $get_supplier = $this->db->get()->result();

    $data = [
      'header' => $get_data_header,
      'detail' => $get_data_detail,
      'list_supplier' => $get_supplier
    ];

    $this->template->set($data);
    $this->template->render('edit');
  }

  public function get_data_incoming()
  {
    $draw = $this->input->post('draw');
    $start = $this->input->post('start');
    $length = $this->input->post('length');
    $search = $this->input->post('search');

    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_header a');
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->or_like('a.no_invoice', $search['value'], 'both');
      $this->db->or_like('a.nm_suplier', $search['value'], 'both');
      $this->db->group_end();
    }
    $this->db->order_by('a.created_date', 'desc');
    $this->db->limit($length, $start);

    $get_data = $this->db->get();

    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_header a');
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->or_like('a.no_invoice', $search['value'], 'both');
      $this->db->or_like('a.nm_suplier', $search['value'], 'both');
      $this->db->group_end();
    }
    $this->db->order_by('a.created_date', 'desc');

    $get_data_all = $this->db->get();

    $hasil = [];

    $no = ($start + 1);
    foreach ($get_data->result() as $item) {

      $this->db->select('SUM(a.total_nilai) as nominal_invoice');
      $this->db->from('tr_receive_invoice_ap_detail a');
      $this->db->where('a.id_rec_inv_ap', $item->id_rec_inv_ap);
      $get_nominal_invoice = $this->db->get()->row();

      $nominal_invoice = (!empty($get_nominal_invoice->nominal_invoice)) ? $get_nominal_invoice->nominal_invoice : 0;
      $nominal_invoice += (isset($item->ppn) ? $item->ppn : 0);

      $btn_view = '<a href="' . base_url('receive_invoice_ap/view/' . $item->id_rec_inv_ap) . '" class="btn btn-sm btn-info" title="View Receiving Invoice"><i class="fa fa-eye"></i></a>';

      $btn_edit = '<a href="' . base_url('receive_invoice_ap/edit/' . $item->id_rec_inv_ap) . '" class="btn btn-sm btn-warning" title="Edit Receiving Invoice" style="margin-left: 0.5rem;"><i class="fa fa-pencil"></i></a>';
      if (!has_permission('Receive_Invoice_AP.Manage')) {
        $btn_edit = '';
      }

      $btn_delete = '<button type="button" class="btn btn-sm btn-danger del_rec_inv" data-id="' . $item->id_rec_inv_ap . '" title="Delete Receiving Invoice" style="margin-left: 0.5rem;"><i class="fa fa-trash"></i></button>';
      if (!has_permission('Receive_Invoice_AP.Delete')) {
        $btn_delete = '';
      }

      $option = $btn_view . $btn_edit . $btn_delete;

      $hasil[] = [
        'no' => $no,
        'no_receive_invoice' => $item->id_rec_inv_ap,
        'no_invoice' => $item->no_invoice,
        'nm_suplier' => $item->nm_suplier,
        'nominal_invoice' => number_format($nominal_invoice, 2),
        'option' => $option
      ];

      $no++;
    }

    echo json_encode([
      'draw' => intval($draw),
      'recordsTotal' => $get_data_all->num_rows(),
      'recordsFiltered' => $get_data_all->num_rows(),
      'data' => $hasil
    ]);
  }

  public function save_receive_invoice()
  {
    $post = $this->input->post();

    $this->db->trans_begin();

    $data_update = [
      'no_invoice_rec_ap' => $post['no_invoice'],
      'receive_date' => $post['receive_date'],
      'nilai_invoice' => str_replace(',', '', $post['total_invoice']),
      'nilai_ppn' => str_replace(',', '', $post['nilai_ppn']),
      'no_faktur_pajak' => $post['no_faktur_pajak'],
      'rec_ap' => 1
    ];

    $this->db->update('tr_incoming', $data_update, array('id_incoming' => $post['id_incoming']));

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
      $pesan = 'Please try again later !';
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $pesan = 'Receive Invoice has been success !';
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);
  }



  public function view_inv()
  {
    $id_incoming = $this->input->post('id_incoming');

    $get_incoming = $this->db->get_where('tr_incoming', ['id_incoming' => $id_incoming])->row();

    echo json_encode([
      'data_incoming' => $get_incoming
    ]);
  }

  public function test_query()
  {
    $id = 'IC-001/MP-06/2026';

    $sql = "
      SELECT 
        a.hargasatuan, a.idmaterial, a.totalwidth, a.jumlahharga,
        b.width_recive, b.qty_sheet, b.id_material,
        mic.id_bentuk, mic.total_weight, mic.nama
      FROM dt_trans_po a
      JOIN dt_incoming b ON b.id_dt_po = a.id_dt_po AND b.id_material = a.idmaterial
      JOIN ms_inventory_category3 mic ON mic.id_category3 = a.idmaterial
      WHERE b.id_incoming = '$id'
    ";
    $data = $this->db->query($sql)->result_array();

    echo "<pre>";
    echo "=== DATA UNTUK INCOMING: $id ===\n\n";

    $total_old = 0;
    $total_new = 0;
    $total_width = 0;

    foreach ($data as $i => $row) {
      echo "--- Item " . ($i + 1) . " ---\n";
      echo "Material: " . $row['nama'] . "\n";
      echo "id_bentuk: " . $row['id_bentuk'] . "\n";
      echo "hargasatuan (per kg): " . $row['hargasatuan'] . "\n";
      echo "qty_sheet (dt_incoming): " . $row['qty_sheet'] . "\n";
      echo "width_recive (dt_incoming): " . $row['width_recive'] . "\n";
      echo "total_weight (ms_inventory_category3): " . $row['total_weight'] . "\n";
      echo "totalwidth (dt_trans_po): " . $row['totalwidth'] . "\n";
      echo "jumlahharga (dt_trans_po): " . $row['jumlahharga'] . "\n";
      echo "\n";

      // Old formula: hargasatuan * qty_sheet
      $total_old += $row['hargasatuan'] * $row['qty_sheet'];

      // New formula (PO style): qty_sheet * hargasatuan * total_weight
      $total_new += $row['qty_sheet'] * $row['hargasatuan'] * $row['total_weight'];

      // Alternative: hargasatuan * width_recive (same as coil)
      $total_width += $row['hargasatuan'] * $row['width_recive'];

      echo "  Calc old (harga*qty_sheet): " . number_format($row['hargasatuan'] * $row['qty_sheet'], 2) . "\n";
      echo "  Calc new (qty_sheet*harga*total_weight): " . number_format($row['qty_sheet'] * $row['hargasatuan'] * $row['total_weight'], 2) . "\n";
      echo "  Calc alt (harga*width_recive): " . number_format($row['hargasatuan'] * $row['width_recive'], 2) . "\n";
      echo "\n";
    }

    echo "=== TOTALS ===\n";
    echo "Old (harga*qty_sheet): " . number_format($total_old, 2) . "\n";
    echo "New (qty_sheet*harga*total_weight): " . number_format($total_new, 2) . "\n";
    echo "Alt (harga*width_recive): " . number_format($total_width, 2) . "\n";
    echo "Target: 174,141,714.80\n";

    echo "\n=== CHECK DUPLICATES ===\n";
    $sql2 = "
      SELECT 
        b.id_dt_incoming, b.id_dt_po, b.id_material, b.qty_sheet, b.width_recive,
        a.id_dt_po as po_id_dt_po, a.idmaterial, a.hargasatuan
      FROM dt_trans_po a
      JOIN dt_incoming b ON b.id_dt_po = a.id_dt_po AND b.id_material = a.idmaterial
      WHERE b.id_incoming = '$id'
    ";
    $raw = $this->db->query($sql2)->result_array();
    echo "Total rows from JOIN: " . count($raw) . "\n\n";

    // Check unique dt_incoming rows
    $sql3 = "SELECT * FROM dt_incoming WHERE id_incoming = '$id'";
    $inc_rows = $this->db->query($sql3)->result_array();
    echo "Actual dt_incoming rows: " . count($inc_rows) . "\n\n";

    // Try correct formula: per dt_incoming row, use width_recive * hargasatuan but only match ONCE
    echo "=== CORRECT CALC (per unique incoming row) ===\n";
    $total_correct = 0;
    foreach ($inc_rows as $inc) {
      // Get hargasatuan from dt_trans_po matching this specific incoming detail
      $sql4 = "SELECT a.hargasatuan FROM dt_trans_po a WHERE a.id_dt_po = '" . $inc['id_dt_po'] . "' AND a.idmaterial = '" . $inc['id_material'] . "' LIMIT 1";
      $po_row = $this->db->query($sql4)->row_array();
      $harga = ($po_row) ? $po_row['hargasatuan'] : 0;
      $sub = $harga * $inc['width_recive'];
      $total_correct += $sub;
      echo "  inc id_dt_po=" . $inc['id_dt_po'] . " | material=" . $inc['id_material'] . " | width_recive=" . $inc['width_recive'] . " | harga=" . $harga . " | sub=" . number_format($sub, 2) . "\n";
    }
    echo "\nTotal (unique incoming × harga × width_recive): " . number_format($total_correct, 2) . "\n";

    echo "\n=== TEST GROUP BY id_dt_po ===\n";
    $sql5 = "
      SELECT 
        b.id_dt_po, b.id_material, 
        SUM(b.width_recive) as total_width_recive,
        SUM(b.qty_sheet) as total_qty_sheet,
        a.hargasatuan,
        mic.total_weight,
        mic.id_bentuk
      FROM dt_incoming b
      JOIN dt_trans_po a ON a.id_dt_po = b.id_dt_po AND a.idmaterial = b.id_material
      JOIN ms_inventory_category3 mic ON mic.id_category3 = b.id_material
      WHERE b.id_incoming = '$id'
      GROUP BY b.id_dt_po, b.id_material
    ";
    $grouped = $this->db->query($sql5)->result_array();

    $total_grouped_width = 0;
    $total_grouped_sheet = 0;
    $total_grouped_sheet_weight = 0;

    foreach ($grouped as $g) {
      $sub_width = $g['hargasatuan'] * $g['total_width_recive'];
      $sub_sheet = $g['total_qty_sheet'] * $g['hargasatuan'] * $g['total_weight'];
      $sub_sheet2 = $g['total_qty_sheet'] * ($g['hargasatuan'] * $g['total_weight']);

      $total_grouped_width += $sub_width;
      $total_grouped_sheet += $sub_sheet;

      echo "  id_dt_po=" . $g['id_dt_po'] . " | material=" . $g['id_material'] . "\n";
      echo "    SUM(width_recive)=" . $g['total_width_recive'] . " | SUM(qty_sheet)=" . $g['total_qty_sheet'] . "\n";
      echo "    hargasatuan=" . $g['hargasatuan'] . " | total_weight=" . $g['total_weight'] . "\n";
      echo "    -> harga*SUM(width): " . number_format($sub_width, 2) . "\n";
      echo "    -> SUM(qty_sheet)*harga*total_weight: " . number_format($sub_sheet, 2) . "\n";
      echo "\n";
    }

    echo "Total grouped (harga*SUM(width_recive)): " . number_format($total_grouped_width, 2) . "\n";
    echo "Total grouped (SUM(qty_sheet)*harga*total_weight): " . number_format($total_grouped_sheet, 2) . "\n";
    echo "Target: 174,141,714.80\n";

    // Also try: per dt_incoming row, use qty_sheet * harga * total_weight but from PR
    echo "\n=== TEST WITH dt_trans_pr weight_sheet ===\n";
    $sql6 = "
      SELECT 
        b.id_dt_po, b.id_material, b.qty_sheet, b.width_recive,
        a.hargasatuan, a.idpr,
        pr.qty_sheet as pr_qty_sheet, pr.weight_sheet as pr_weight_sheet,
        mic.total_weight, mic.id_bentuk
      FROM dt_incoming b
      JOIN dt_trans_po a ON a.id_dt_po = b.id_dt_po AND a.idmaterial = b.id_material
      JOIN ms_inventory_category3 mic ON mic.id_category3 = b.id_material
      LEFT JOIN dt_trans_pr pr ON pr.id_dt_pr = a.idpr
      WHERE b.id_incoming = '$id'
    ";
    $with_pr = $this->db->query($sql6)->result_array();

    $total_pr_formula = 0;
    foreach ($with_pr as $p) {
      // Formula from PO Print: qty_sheet (incoming) * hargasatuan * weight_sheet (from PR)
      $ws = (!empty($p['pr_weight_sheet'])) ? $p['pr_weight_sheet'] : $p['total_weight'];
      $sub_pr = $p['qty_sheet'] * $p['hargasatuan'] * $ws;
      $total_pr_formula += $sub_pr;
    }
    echo "Total (incoming.qty_sheet * harga * pr.weight_sheet): " . number_format($total_pr_formula, 2) . "\n";
    echo "Target: 174,141,714.80\n";

    echo "</pre>";
  }

  public function TambahRequest()
  {
    $id_suplier = $this->input->post('id_suplier');

    $this->template->set('id_suplier', $id_suplier);
    $this->template->title('List Request');
    $this->template->render('request');
  }

  public function server_side_request()
  {
    $id_suplier = $this->input->post('id_suplier');
    $draw       = (int) $this->input->post('draw');
    $start      = (int) $this->input->post('start');
    $length     = (int) $this->input->post('length');
    $search     = $this->input->post('search');

    $list_id_incoming = [];
    if (!empty($search['value'])) {
      $search_value = $this->db->escape_like_str($search['value']);

      // Step 1: Cari no_po yang nomor suratnya cocok di tr_purchase_order
      $this->db->select('no_po');
      $this->db->from('tr_purchase_order');
      $this->db->like('no_surat', $search_value, 'both');
      $get_pos = $this->db->get()->result_array();
      $po_numbers = array_filter(array_unique(array_column($get_pos, 'no_po')));

      if (!empty($po_numbers)) {
        // Step 2: Cari id_dt_po di dt_trans_po berdasarkan no_po
        $this->db->select('id_dt_po');
        $this->db->from('dt_trans_po');
        $this->db->where_in('no_po', $po_numbers);
        $get_dt_pos = $this->db->get()->result_array();
        $dt_po_ids = array_filter(array_unique(array_column($get_dt_pos, 'id_dt_po')));

        if (!empty($dt_po_ids)) {
          // Step 3: Cari id_incoming di dt_incoming berdasarkan id_dt_po
          $this->db->select('id_incoming');
          $this->db->from('dt_incoming');
          $this->db->where_in('id_dt_po', $dt_po_ids);
          $this->db->group_by('id_incoming');
          $get_inc = $this->db->get()->result_array();
          $list_id_incoming = array_filter(array_unique(array_column($get_inc, 'id_incoming')));
        }
      }
    }

    // Build the query to get total filtered records
    $this->db->select('a.*, b.name_suplier');
    $this->db->from('tr_incoming a');
    $this->db->join('master_supplier b', 'b.id_suplier = a.id_suplier', 'left');
    $this->db->where('a.id_suplier', $id_suplier);
    $this->db->group_start();
    $this->db->where('a.no_invoice_rec_ap', '');
    $this->db->or_where('a.no_invoice_rec_ap', null);
    $this->db->group_end();

    if (!empty($search['value'])) {
      // Main Query filtering
      $this->db->group_start();
      $this->db->like('a.id_incoming', $search['value'], 'both');
      $this->db->or_like('a.sj_supplier', $search['value'], 'both');
      $this->db->or_like('b.name_suplier', $search['value'], 'both');
      
      if (!empty($list_id_incoming)) {
        $this->db->or_where_in('a.id_incoming', $list_id_incoming);
      }
      $this->db->group_end();
    }

    // clone db for total filtered
    $db_filtered = clone $this->db;
    $recordsFiltered = $db_filtered->count_all_results();

    // apply limit
    $this->db->order_by('a.tanggal', 'desc');
    $this->db->limit($length, $start);
    $get_data_supplier = $this->db->get()->result();

    // get total records without filter
    $this->db->from('tr_incoming a');
    $this->db->where('a.id_suplier', $id_suplier);
    $this->db->group_start();
    $this->db->where('a.no_invoice_rec_ap', '');
    $this->db->or_where('a.no_invoice_rec_ap', null);
    $this->db->group_end();
    $recordsTotal = $this->db->count_all_results();

    $data = [];
    $no   = $start + 1;

    if (!empty($get_data_supplier)) {
      $incoming_ids = [];
      foreach ($get_data_supplier as $item) {
        $incoming_ids[] = $item->id_incoming;
      }

      // Batch query 1: Get PO numbers for all current page incoming IDs
      $map_no_po = [];
      if (!empty($incoming_ids)) {
        $this->db->select('a.id_incoming, c.no_surat');
        $this->db->from('dt_incoming a');
        $this->db->join('dt_trans_po b', 'b.id_dt_po = a.id_dt_po', 'left');
        $this->db->join('tr_purchase_order c', 'c.no_po = b.no_po', 'left');
        $this->db->where_in('a.id_incoming', $incoming_ids);
        $this->db->group_by(['a.id_incoming', 'c.no_surat']);
        $res_no_po = $this->db->get()->result_array();

        foreach ($res_no_po as $r) {
          if (!empty($r['no_surat'])) {
            $map_no_po[$r['id_incoming']][] = $r['no_surat'];
          }
        }
      }

      // Batch query 2: Get total incoming for all current page incoming IDs
      $map_total_incoming = [];
      if (!empty($incoming_ids)) {
        $this->db->select('b.id_incoming, b.width_recive, b.qty_sheet, a.hargasatuan, mic.id_bentuk, mic.total_weight');
        $this->db->from('dt_trans_po a');
        $this->db->join('dt_incoming b', 'b.id_dt_po = a.id_dt_po AND b.id_material = a.idmaterial');
        $this->db->join('ms_inventory_category3 mic', 'mic.id_category3 = a.idmaterial', 'left');
        $this->db->where_in('b.id_incoming', $incoming_ids);
        $res_total_incoming = $this->db->get()->result();

        foreach ($res_total_incoming as $item_incoming) {
          $inc_id = $item_incoming->id_incoming;
          if (!isset($map_total_incoming[$inc_id])) {
            $map_total_incoming[$inc_id] = 0;
          }
          if ($item_incoming->id_bentuk == 'B2000002') { // Material Sheet
            $harga_per_sheet = $item_incoming->hargasatuan * $item_incoming->total_weight;
            $map_total_incoming[$inc_id] += ($item_incoming->qty_sheet * $harga_per_sheet);
          } else { // Material Coil
            $map_total_incoming[$inc_id] += ($item_incoming->hargasatuan * $item_incoming->width_recive);
          }
        }
      }

      foreach ($get_data_supplier as $item) {
        $list_no_po = isset($map_no_po[$item->id_incoming]) ? $map_no_po[$item->id_incoming] : [];
        $no_po = implode(',', array_filter(array_unique($list_no_po)));
        $total_incoming = isset($map_total_incoming[$item->id_incoming]) ? $map_total_incoming[$item->id_incoming] : 0;

        $action = '<button type="button" class="btn btn-sm btn-warning add_incoming add_incoming_' . $no . '" data-id_incoming="' . $item->id_incoming . '" data-no_po="' . $no_po . '" data-sj_supplier="' . (isset($item->sj_supplier) ? $item->sj_supplier : '') . '" data-id_suplier="' . $item->id_suplier . '" data-name_suplier="' . $item->name_suplier . '" data-nilai="' . $total_incoming . '" data-tanggal_incoming="' . $item->tanggal . '" data-no="' . $no . '"><i class="fa fa-plus"></i> Add</button>';

        $data[] = [
          '<div class="text-center">' . htmlspecialchars($item->id_incoming) . '</div>',
          '<div class="text-center">' . htmlspecialchars($no_po) . '</div>',
          '<div class="text-center">' . htmlspecialchars(isset($item->sj_supplier) ? $item->sj_supplier : '') . '</div>',
          '<div class="text-center">' . date('d F Y', strtotime($item->tanggal)) . '</div>',
          '<div class="text-center">' . htmlspecialchars($item->name_suplier) . '</div>',
          '<div class="text-right">' . number_format($total_incoming, 2) . '</div>',
          '<div class="text-center">' . $action . '</div>'
        ];
        $no++;
      }
    }

    echo json_encode([
      'draw'            => $draw,
      'recordsTotal'    => $recordsTotal,
      'recordsFiltered' => $recordsFiltered,
      'data'            => $data
    ]);
  }

  public function save_receive_invoice_ap()
  {
    $post = $this->input->post();

    $this->db->trans_begin();

    $id_rec_inv_ap = $this->Receive_invoice_ap_model->generate_id_invoice_ap();

    $get_supplier = $this->db->get_where('master_supplier', ['id_suplier' => $post['supplier']])->row();

    $data_header = [
      'id_rec_inv_ap' => $id_rec_inv_ap,
      'tgl_bayar' => $post['tgl_bayar'],
      'no_invoice' => $post['no_invoice'],
      'id_suplier' => $post['supplier'],
      'nm_suplier' => $get_supplier->name_suplier,
      'ppn' => str_replace(',', '', (isset($post['ppn_global']) ? $post['ppn_global'] : 0)),
      'ppn_persen' => str_replace(',', '', (isset($post['ppn_persen']) ? $post['ppn_persen'] : 0)),
      'no_faktur_pajak' => $post['no_faktur_pajak'],
      'created_by' => $this->auth->user_id(),
      'created_date' => date('Y-m-d H:i:s')
    ];

    $data_detail = [];
    if (isset($post['kp'])) {
      foreach ($post['kp'] as $item) {
        $data_detail[] = [
          'id_rec_inv_ap' => $id_rec_inv_ap,
          'id_incoming' => $item['id_incoming'],
          'no_po' => $item['no_po'],
          'tanggal_incoming' => $item['tanggal_incoming'],
          'id_suplier' => $item['id_suplier'],
          'nm_suplier' => $item['nm_suplier'],
          'nilai' => str_replace(',', '', $item['nilai']),
          'no_faktur_pajak' => '',
          'ppn' => 0,
          'total_nilai' => str_replace(',', '', $item['total']),
          'created_by' => $this->auth->user_id(),
          'created_date' => date('Y-m-d')
        ];

        $data_update_incoming = [
          'id_incoming' => $item['id_incoming'],
          'no_invoice_rec_ap' => $post['no_invoice'],
          'nilai_invoice' => str_replace(',', '', $item['total']),
          'nilai_ppn' => str_replace(',', '', (isset($post['ppn_global']) ? $post['ppn_global'] : 0)),
          'no_faktur_pajak' => $post['no_faktur_pajak'],
          'rec_ap' => 1
        ];

        $update_incoming = $this->db->update('tr_incoming', $data_update_incoming, ['id_incoming' => $item['id_incoming'], 'id_suplier' => $item['id_suplier']]);
        if (!$update_incoming) {
          $this->db->trans_rollback();

          print_r($this->db->error($update_incoming));
          exit;
        }
      }
    }

    $insert_header = $this->db->insert('tr_receive_invoice_ap_header', $data_header);
    if (!$insert_header) {
      $this->db->trans_rollback();

      print_r($this->db->error($insert_header));
      exit;
    }

    $insert_detail = $this->db->insert_batch('tr_receive_invoice_ap_detail', $data_detail);
    if (!$insert_detail) {
      $this->db->trans_rollback();

      print_r($this->db->error($insert_detail));
      exit;
    }

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
      $pesan = 'Please try again later !';
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $pesan = 'Data has been saved !';
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);
  }

  public function update_receive_invoice_ap()
  {
    $post = $this->input->post();

    $this->db->trans_begin();

    $get_supplier = $this->db->get_where('master_supplier', ['id_suplier' => $post['supplier']])->row();

    $this->db->select('a.*');
    $this->db->from('tr_receive_invoice_ap_detail a');
    $this->db->where('a.id_rec_inv_ap', $post['id_rec_inv_ap']);
    $get_detail = $this->db->get()->result();

    foreach ($get_detail as $item) {
      $this->db->update('tr_incoming', [
        'no_invoice_rec_ap' => null,
        'nilai_invoice' => null,
        'nilai_ppn' => null,
        'no_faktur_pajak' => null,
        'rec_ap' => 0
      ], [
        'id_incoming' => $item->id_incoming,
        'id_suplier' => $item->id_suplier
      ]);
    }

    $this->db->delete('tr_receive_invoice_ap_detail', ['id_rec_inv_ap' => $post['id_rec_inv_ap']]);

    $data_header = [
      'tgl_bayar' => $post['tgl_bayar'],
      'no_invoice' => $post['no_invoice'],
      'id_suplier' => $post['supplier'],
      'nm_suplier' => $get_supplier->name_suplier,
      'ppn' => str_replace(',', '', (isset($post['ppn_global']) ? $post['ppn_global'] : 0)),
      'ppn_persen' => str_replace(',', '', (isset($post['ppn_persen']) ? $post['ppn_persen'] : 0)),
      'no_faktur_pajak' => $post['no_faktur_pajak'],
      'created_by' => $this->auth->user_id(),
      'created_date' => date('Y-m-d H:i:s')
    ];

    $data_detail = [];
    if (isset($post['kp'])) {
      foreach ($post['kp'] as $item) {
        $data_detail[] = [
          'id_rec_inv_ap' => $post['id_rec_inv_ap'],
          'id_incoming' => $item['id_incoming'],
          'no_po' => $item['no_po'],
          'tanggal_incoming' => $item['tanggal_incoming'],
          'id_suplier' => $item['id_suplier'],
          'nm_suplier' => $item['nm_suplier'],
          'nilai' => str_replace(',', '', $item['nilai']),
          'no_faktur_pajak' => '',
          'ppn' => 0,
          'total_nilai' => str_replace(',', '', $item['total']),
          'created_by' => $this->auth->user_id(),
          'created_date' => date('Y-m-d')
        ];

        $data_update_incoming = [
          'id_incoming' => $item['id_incoming'],
          'no_invoice_rec_ap' => $post['no_invoice'],
          'nilai_invoice' => str_replace(',', '', $item['total']),
          'nilai_ppn' => str_replace(',', '', (isset($post['ppn_global']) ? $post['ppn_global'] : 0)),
          'no_faktur_pajak' => $post['no_faktur_pajak'],
          'rec_ap' => 1
        ];

        $update_incoming = $this->db->update('tr_incoming', $data_update_incoming, ['id_incoming' => $item['id_incoming'], 'id_suplier' => $item['id_suplier']]);
        if (!$update_incoming) {
          $this->db->trans_rollback();

          print_r($this->db->error($update_incoming));
          exit;
        }
      }
    }

    $insert_header = $this->db->update('tr_receive_invoice_ap_header', $data_header, ['id_rec_inv_ap' => $post['id_rec_inv_ap']]);
    if (!$insert_header) {
      $this->db->trans_rollback();

      print_r($this->db->error($insert_header));
      exit;
    }

    $insert_detail = $this->db->insert_batch('tr_receive_invoice_ap_detail', $data_detail);
    if (!$insert_detail) {
      $this->db->trans_rollback();

      print_r($this->db->error($insert_detail));
      exit;
    }

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
      $pesan = 'Please try again later !';
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $pesan = 'Data has been updated !';
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);
  }

  public function del_rec_inv_ap()
  {
    $id = $this->input->post('id');

    $this->db->trans_begin();

    $get_detail = $this->db->get_where('tr_receive_invoice_ap_detail', ['id_rec_inv_ap' => $id])->result();
    foreach ($get_detail as $item) {
      $this->db->update('tr_incoming', [
        'no_invoice_rec_ap' => null,
        'nilai_invoice' => 0,
        'nilai_ppn' => 0,
        'no_faktur_pajak' => null,
        'rec_ap' => 0
      ], ['id_incoming' => $item->id_incoming, 'id_suplier' => $item->id_suplier]);
    }

    $this->db->delete('tr_receive_invoice_ap_detail', ['id_rec_inv_ap' => $id]);
    $this->db->delete('tr_receive_invoice_ap_header', ['id_rec_inv_ap' => $id]);

    if ($this->db->trans_status() === false) {
      $this->db->trans_rollback();

      $valid = 0;
      $pesan = 'Please try again later !';
    } else {
      $this->db->trans_commit();

      $valid = 1;
      $pesan = 'Data has been deleted !';
    }

    echo json_encode([
      'status' => $valid,
      'pesan' => $pesan
    ]);
  }

  public function testing()
  {
    $id_incoming = 'IC-001/MP-06/2026';

    $get_incoming_detail = $this->db->select('a.lotno, a.qty_sheet, b.nama, b.total_weight, c.hargasatuan')
      ->from('dt_incoming a')
      ->join('ms_inventory_category3 b', 'b.id_category3 = a.id_material')
      ->join('dt_trans_po c', 'c.id_dt_po = a.id_dt_po')
      ->where('a.id_incoming', $id_incoming)
      ->get()
      ->result();

    $this->load->view('testing', ['detail' => $get_incoming_detail]);
  }
}
