<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @Author Syamsudin
 * @Copyright (c) 2022, Syamsudin
 *
 * This is model class for table "Wt_penawaran"
 */

class Wt_invoicing_model extends BF_Model
{
  /**
   * @var string  User Table Name
   */
  protected $viewPermission   = 'Invoicing.View';
  protected $addPermission    = 'Invoicing.Add';
  protected $managePermission = 'Invoicing.Manage';
  protected $deletePermission = 'Invoicing.Delete';

  protected $table_name = 'tr_invoicing';
  protected $key        = 'id';

  /**
   * @var string Field name to use for the created time column in the DB table
   * if $set_created is enabled.
   */
  protected $created_field = 'created_on';

  /**
   * @var string Field name to use for the modified time column in the DB
   * table if $set_modified is enabled.
   */
  protected $modified_field = 'modified_on';

  /**
   * @var bool Set the created time automatically on a new record (if true)
   */
  protected $set_created = true;

  /**
   * @var bool Set the modified time automatically on editing a record (if true)
   */
  protected $set_modified = true;
  /**
   * @var string The type of date/time field used for $created_field and $modified_field.
   * Valid values are 'int', 'datetime', 'date'.
   */
  /**
   * @var bool Enable/Disable soft deletes.
   * If false, the delete() method will perform a delete of that row.
   * If true, the value in $deleted_field will be set to 1.
   */
  protected $soft_deletes = true;

  protected $date_format = 'datetime';

  /**
   * @var bool If true, will log user id in $created_by_field, $modified_by_field,
   * and $deleted_by_field.
   */
  protected $log_user = true;

  /**
   * Function construct used to load some library, do some actions, etc.
   */
  public function __construct()
  {
    parent::__construct();
  }

  function generate_id($kode = '')
  {
    $query = $this->db->query("SELECT MAX(id_invoice) as max_id FROM tr_invoice");
    $row = $query->row_array();
    $thn = date('y');
    $max_id = $row['max_id'];
    $max_id1 = (int) substr($max_id, 3, 5);
    $counter = $max_id + 1;
    $idcust = "I" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
    return $counter;
  }

  function generate_id_proforma($kode = '')
  {
    $query = $this->db->query("SELECT MAX(id_invoice) as max_id FROM tr_invoice_proforma");
    $row = $query->row_array();
    $thn = date('y');
    $max_id = $row['max_id'];
    $max_id1 = (int) substr($max_id, 3, 5);
    $counter = $max_id + 1;
    $idcust = "F" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
    return $counter;
  }

  function generate_code($kode = '')
  {
    $query = $this->db->query("SELECT MAX(no_invoice) as max_id FROM tr_invoice");
    $row = $query->row_array();
    $thn = date('y');
    $max_id = $row['max_id'];
    $max_id1 = (int) substr($max_id, 3, 5);
    $counter = $max_id1 + 1;
    $idcust = "I" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
    return $idcust;
  }
  function generate_code_proforma($kode = '')
  {
    $query = $this->db->query("SELECT MAX(no_invoice) as max_id FROM tr_invoice_proforma");
    $row = $query->row_array();
    $thn = date('y');
    $max_id = $row['max_id'];
    $max_id1 = (int) substr($max_id, 3, 5);
    $counter = $max_id1 + 1;
    $idcust = "F" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
    return $idcust;
  }
  function BuatNomor($kode = '')
  {
    $bulan = date('m');
    $tahun_full = date('Y'); // 2024
    $tahun_short = date('y'); // 24

    // 1. Konversi Bulan ke Romawi (Lebih ringkas)
    $array_romawi = [
      '01' => 'I',
      '02' => 'II',
      '03' => 'III',
      '04' => 'IV',
      '05' => 'V',
      '06' => 'VI',
      '07' => 'VII',
      '08' => 'VIII',
      '09' => 'IX',
      '10' => 'X',
      '11' => 'XI',
      '12' => 'XII'
    ];
    $romawi = $array_romawi[$bulan];

    // 2. Query cari nomor terakhir di TAHUN ini saja
    // Kita cari yang formatnya .../24/... (sesuai tahun jalan)
    $query = $this->db->query("SELECT MAX(RIGHT(no_surat, 4)) as max_id 
                               FROM tr_invoice 
                               WHERE no_surat LIKE '%/" . $tahun_short . "/%'");

    $row = $query->row_array();

    // 3. Logika Counter
    $max_id = $row['max_id'];
    $counter = (int)$max_id + 1;

    // 4. Generate Format Akhir: INV-MP/24/II/0001
    // Pakai $tahun_short agar formatnya 24, bukan 2024
    $idcust = "INV-MP/" . $tahun_short . "/" . $romawi . "/" . sprintf("%04s", $counter);

    return $idcust;
  }

  function BuatNomorProforma($kode = '')
  {
    $bulan = date('m');
    $th = date('Y');
    $tahun = date('y');
    if ($bulan == '01') {
      $romawi = 'I';
    } elseif ($bulan == '02') {
      $romawi = 'II';
    } elseif ($bulan == '03') {
      $romawi = 'III';
    } elseif ($bulan == '04') {
      $romawi = 'IV';
    } elseif ($bulan == '05') {
      $romawi = 'V';
    } elseif ($bulan == '06') {
      $romawi = 'VI';
    } elseif ($bulan == '07') {
      $romawi = 'VII';
    } elseif ($bulan == '08') {
      $romawi = 'VIII';
    } elseif ($bulan == '09') {
      $romawi = 'IX';
    } elseif ($bulan == '10') {
      $romawi = 'X';
    } elseif ($bulan == '11') {
      $romawi = 'XI';
    } elseif ($bulan == '12') {
      $romawi = 'XII';
    }
    $blnthn = date('Y-m');
    $query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_invoice_proforma WHERE Year(tahun)='$th'");
    $row = $query->row_array();
    $thn = date('T');
    $max_id = $row['max_id'];
    $max_id1 = (int) substr($max_id, -4);
    $counter = $max_id1 + 1;
    $idcust = "PROF-INV-MP/" . $tahun . "/" . $romawi . "/" . sprintf("%04s", $counter);
    return $idcust;
  }


  function BuatNomorProforma2($kode = '')
  {
    $bulan = date('m');
    $tahun = date('Y');
    if ($bulan == '01') {
      $romawi = 'I';
    } elseif ($bulan == '02') {
      $romawi = 'II';
    } elseif ($bulan == '03') {
      $romawi = 'III';
    } elseif ($bulan == '04') {
      $romawi = 'IV';
    } elseif ($bulan == '05') {
      $romawi = 'V';
    } elseif ($bulan == '06') {
      $romawi = 'VI';
    } elseif ($bulan == '07') {
      $romawi = 'VII';
    } elseif ($bulan == '08') {
      $romawi = 'VIII';
    } elseif ($bulan == '09') {
      $romawi = 'IX';
    } elseif ($bulan == '10') {
      $romawi = 'X';
    } elseif ($bulan == '11') {
      $romawi = 'XI';
    } elseif ($bulan == '12') {
      $romawi = 'XII';
    }
    $blnthn = date('Y-m');
    $query = $this->db->query("SELECT MAX(no_proforma_invoice) as max_id FROM tr_invoice WHERE Year(tgl_invoice)='$tahun'");
    $row = $query->row_array();
    $thn = date('T');
    $max_id = $row['max_id'];
    $max_id1 = (int) substr($max_id, 0, 3);
    $counter = $max_id1 + 1;
    $idcust = sprintf("%03s", $counter) . "/PR-WI/" . $romawi . "/" . $tahun;
    return $idcust;
  }

  public function get_data($table, $where_field = '', $where_value = '')
  {
    if ($where_field != '' && $where_value != '') {
      $query = $this->db->get_where($table, array($where_field => $where_value));
    } else {
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  public function cariPlantagih()
  {
    $this->db->select('a.*,a.keterangan as ket_tagih,b.*, c.name_customer as name_customer, d.nama_top');
    $this->db->from('wt_plan_tagih a');
    $this->db->join('tr_sales_order b', 'b.no_so=a.no_so');
    $this->db->join('master_customers c', 'c.id_customer=b.id_customer');
    $this->db->join('ms_top d', 'd.id_top=a.id_top');
    $where = "a.status_invoice <>'1'";
    // $where2 = "a.status<>'7'";
    $this->db->where($where);
    // $this->db->where($where2);
    // $this->db->order_by('a.no_penawaran', DESC);
    $query = $this->db->get();
    return $query->result();
  }

  public function CariSPK($id)
  {
    $this->db->select('a.*');
    $this->db->from('dt_spkmarketing a');
    $where = "a.id_spkmarketing ='$id'";
    $where2 = "a.deal ='1'";
    $this->db->where($where);
    $this->db->where($where2);
    $this->db->where('a.id_material IS NOT NULL');
    $query = $this->db->get();

    return $query->result();
  }

  public function CariSPKdo($id)
  {
    $this->db->select('a.*');
    $this->db->from('view_detail_delivery_order a');
    $where = "a.id_delivery_order ='$id'";
    //$where2 = "a.deal ='1'";
    $this->db->where($where);
    //$this->db->where($where2);
    $query = $this->db->get();
    return $query->result();
  }

  public function CariSPKdoscrap($id)
  {
    $this->db->select('a.id_material, a.thickness, SUM(a.total_kirim) AS total_kirim');
    $this->db->from('view_detail_delivery_order_scrap a');
    $where = "a.id_delivery_order ='$id'";
    //$where2 = "a.deal ='1'";
    $this->db->where($where);
    $this->db->group_by('a.thickness');
    $query = $this->db->get();
    return $query->result();
  }




  public function CariInvoice()
  {
    $this->db->select('a.*, b.name_customer as name_customer');
    $this->db->from('tr_invoice a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
    // $this->db->join('ms_top c','c.id_top=a.top');

    // $where = "a.status_approve ='1'";
    // $where2 = "a.status<>'7'";
    // $this->db->where($where);
    // $this->db->where($where2);
    $this->db->order_by('a.id', 'DESC');
    $query = $this->db->get();
    return $query->result();
  }

  public function CariInvoiceDeal()
  {
    $this->db->select('a.*, b.name_customer as name_customer');
    $this->db->from('tr_invoice a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
    $where = "a.no_invoice !=''";
    $where2 = "a.status_close ='0'";
    $this->db->where($where);
    $this->db->where($where2);
    $this->db->order_by('a.no_invoice', 'DESC');
    $query = $this->db->get();
    return $query->result();
  }
  public function CariInvoiceClose()
  {
    $this->db->select('a.*, b.name_customer as name_customer');
    $this->db->from('tr_invoice a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
    $where = "a.no_invoice !=''";
    $where2 = "a.status_close ='1'";
    $this->db->where($where);
    $this->db->where($where2);
    $this->db->order_by('a.no_invoice', 'DESC');
    $query = $this->db->get();
    return $query->result();
  }

  public function getAlamatSO($so)
  {
    $this->db->select('a.no_so, b.address_office');
    $this->db->from('tr_sales_order a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
    $where = "a.no_so ='$so'";
    $this->db->where($where);
    $query = $this->db->get();
    return $query->result();
  }

  public function CariInvoiceJurnal()
  {
    $this->db->select('a.*, b.name_customer as name_customer');
    $this->db->from('tr_invoice a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
    $where = "a.status_jurnal ='OPN'";
    $this->db->where($where);
    $this->db->order_by('a.no_invoice', 'DESC');
    $query = $this->db->get();
    return $query->result();
  }

  public function PrintDetail($id)
  {
    $this->db->select('a.*');
    $this->db->from('dt_delivery_order_child a');
    // $this->db->join('ms_inventory_category3 b','b.id_category3=a.id_material');
    // $this->db->join('ms_inventory_category2 c','c.id_category2=b.id_category2');
    // $this->db->join('child_inven_dimensi d','d.id_category3=a.id_category3');
    $this->db->where('a.id_delivery_order', $id);
    $this->db->group_by('a.id_material');
    $this->db->group_by('a.width');
    $this->db->group_by('a.length');
    $this->db->group_by('a.kode_gabung');

    $query = $this->db->get();
    return $query->result();
  }

  public function PrintDetail2($id)
  {
    $this->db->select('a.*');
    $this->db->from('dt_delivery_order_child_scrap a');
    // $this->db->join('ms_inventory_category3 b','b.id_category3=a.id_material');
    // $this->db->join('ms_inventory_category2 c','c.id_category2=b.id_category2');
    // $this->db->join('child_inven_dimensi d','d.id_category3=a.id_category3');
    $this->db->where('a.id_delivery_order', $id);
    $this->db->group_by('a.id_material');
    $this->db->group_by('a.width');
    $this->db->group_by('a.thickness');
    $this->db->group_by('a.kode_gabung');
    $query = $this->db->get();
    return $query->result();
  }


  public function CariProformaInvoice()
  {
    $this->db->select('a.*, b.name_customer as name_customer');
    $this->db->from('tr_invoice_proforma a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
    // $this->db->join('ms_top c','c.id_top=a.top');

    // $where = "a.status_approve ='1'";
    // $where2 = "a.status<>'7'";
    // $this->db->where($where);
    // $this->db->where($where2);
    $this->db->order_by('a.no_invoice', 'DESC');
    $query = $this->db->get();
    return $query->result();
  }

  public function get_invoicing()
  {
    $draw = $this->input->post('draw');
    $start = $this->input->post('start');
    $length = $this->input->post('length');
    $search = $this->input->post('search');

    $this->db->select('a.*, b.name_customer as name_customer');
    $this->db->from('tr_invoice a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');

    $db_clone1 = clone $this->db;
    $count_all = $db_clone1->count_all_results();

    if (!empty($search['value'])) {
      $this->db->group_start();
      $this->db->like('a.no_surat', $search['value'], 'both');
      $this->db->or_like('b.name_customer', $search['value'], 'both');
      $this->db->or_like('a.note', $search['value'], 'both');
      $this->db->or_like('a.no_do', $search['value'], 'both');
      $this->db->or_like('a.nilai_invoice', $search['value'], 'both');
      $this->db->or_like('a.tgl_invoice', $search['value'], 'both');
      $this->db->group_end();
    }

    $db_clone2 = clone $this->db;
    $count_filtered = $db_clone2->count_all_results();

    $this->db->order_by('a.id', 'desc');
    $this->db->limit($length, $start);

    $get_data = $this->db->get();

    $hasil = [];

    $no = (0 + $start);

    foreach ($get_data->result_array() as $item) {
      $no++;

      $action = '';

      if (has_permission($this->managePermission) && $item['no_proforma_invoice'] != '') :
        $action .= ' <a class="btn btn-primary btn-sm" href="' . base_url('/wt_invoicing/PrintProformaInvoice/' . $item['id_invoice']) . '" target="_blank" title="Cetak Proforma Invoice" data-no_inquiry="' . $item['no_inquiry'] . '"><i class="fa fa-print"></i></a>';
      endif;

      if (has_permission($this->managePermission) && $item['no_invoice'] == '') :
        $action .= ' <a class="btn btn-warning btn-sm" href="' . base_url('/wt_invoicing/createDealInvoice/' . $item['id_invoice']) . '" target="_blank" title="Create Invoice" data-no_inquiry="' . $item['no_inquiry'] . '"><i class="fa fa-plus"></i></a> ';
      endif;

      if (has_permission($this->managePermission) && $item['no_invoice'] != '') :
        $action .= ' <a class="btn btn-success btn-sm" href="' . base_url('/wt_invoicing/PrintInvoice/' . $item['no_invoice']) . '" target="_blank" title="Cetak Invoice" data-no_inquiry="' . $item['no_inquiry'] . '"><i class="fa fa-print"></i></a> ';
      endif;

      if (has_permission($this->managePermission) && $item['no_invoice'] != '') :
        $action .= ' <a class="btn btn-primary btn-sm" href="' . base_url('/wt_invoicing/PrintPackinglist/' . $item['no_invoice']) . '" target="_blank" title="Cetak Packinglist" data-no_inquiry="' . $item['no_inquiry'] . '"><i class="fa fa-print"></i> ';
      endif;

      if (has_permission($this->managePermission) && $item['no_invoice'] != '') :
        $action .= ' <a class="btn btn-warning btn-sm" href="' . base_url('/wt_invoicing/PrintPackinglistSlitting/' . $item['no_invoice']) . '" target="_blank" title="Packinglist Slitting" data-no_inquiry="' . $item['no_inquiry'] . '"><i class="fa fa-print"></i></a> ';
      endif;



      $this->db->select('a.*');
      $this->db->from('tr_invoice_detail a');
      $this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_category3');
      $this->db->where('a.no_invoice', $item['no_invoice']);
      $this->db->where('b.id_bentuk', 'B2000002');
      $get_detail_sheet = $this->db->get()->result();

      $tipe_sheet = (count($get_detail_sheet) > 0) ? '1' : '0';

      if ($tipe_sheet == '1') {
        $nilai_invoice = 0;

        foreach ($get_detail_sheet as $item_sheet) {
          $this->db->select('a.qty_sheet');
          $this->db->from('stock_material a');
          $this->db->join('dt_delivery_order_child b', 'b.lotno = a.lotno');
          $this->db->join('tr_delivery_order c', 'c.id_delivery_order = b.id_delivery_order');
          $this->db->where('c.no_surat', $item['no_do']);
          $this->db->where('b.id_material', $item_sheet->id_category3);
          $this->db->where('a.no_kirim', $item['id_do']);
          $this->db->group_by('a.id_stock');
          $get_qty_sheet = $this->db->get()->result();

          $qty_sheet = 0;
          foreach ($get_qty_sheet as $item_qty_sheet) {
            $qty_sheet += $item_qty_sheet->qty_sheet;
          }

          $nilai_invoice += ($item_sheet->harga_satuan * $qty_sheet) + (($item_sheet->harga_satuan * $qty_sheet) * 11 / 100);
        }
      } else {
        $this->db->select('SUM(ROUND(a.qty_invoice) * a.harga_satuan) as ttl_harga');
        $this->db->from('tr_invoice_detail a');
        $this->db->where('a.no_invoice', $item['no_invoice']);
        $get_total_invoice = $this->db->get()->row();

        $ttl_harga = $get_total_invoice->ttl_harga;

        $dpp_nilai_lain = ceil(11 / 12 * $ttl_harga);
        $ppn = ($dpp_nilai_lain * 12 / 100);
        $grand_total = ($ttl_harga + $ppn);

        $nilai_invoice = $grand_total;
      }


      $hasil[] = [
        'no' => $no,
        'no_invoice' => $item['no_surat'],
        'nama_customer' => strtoupper($item['name_customer']),
        'term' => $item['note'],
        'nomor_do' => $item['no_do'],
        'nilai_invoice' => number_format($nilai_invoice),
        'tanggal_invoice' => date('d-F-Y', strtotime($item['tgl_invoice'])),
        'action' => $action
      ];
    }

    echo json_encode([
      'draw' => intval($draw),
      'recordsTotal' => $count_all,
      'recordsFiltered' => $count_filtered,
      'data' => $hasil
    ]);
  }

  public function get_monitoring_invoice()
  {
    $draw = $this->input->post('draw');
    $length = $this->input->post('length');
    $start = $this->input->post('start');
    $search = $this->input->post('search');

    $this->db->select('a.*, b.name_customer as name_customer');
    $this->db->from('tr_invoice a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
    $this->db->where("a.no_invoice <>", '');
    $this->db->where('a.status_close', '0');
    if (!empty($search['value'])) {
      $this->db->group_start();
      $this->db->like('a.no_surat', $search['value'], 'both');
      $this->db->or_like('b.name_customer', $search['value'], 'both');
      $this->db->or_like('a.nama_sales', $search['value'], 'both');
      $this->db->group_end();
    }
    $this->db->order_by('a.no_invoice', 'desc');
    $query = $this->db->get();
  }

  public function get_data_spk_marketing()
  {
    $draw = $this->input->post('draw');
    $length = $this->input->post('length');
    $start = $this->input->post('start');
    $search = $this->input->post('search');

    $this->db->select('a.*, b.name_customer as name_customer, SUM(c.total_harga) as total');
    $this->db->from('tr_spk_marketing a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
    $this->db->join('dt_spkmarketing c', 'c.id_spkmarketing = a.id_spkmarketing');

    if (!empty($search['value'])) {
      $this->db->group_start();
      $this->db->like('a.tgl_spk_marketing',  $search['value'], 'both');
      $this->db->or_like('a.no_surat', $search['value'], 'both');
      $this->db->or_like('b.nm_customer', $search['value'], 'both');
      $this->db->group_end();
    }

    $this->db->group_by('a.no_surat');

    $db_clone_filtered = clone $this->db;
    $count_filtered = $db_clone_filtered->count_all_results();

    $this->db->order_by('a.id_spkmarketing', 'DESC');
    $this->db->limit($length, $start);

    $get_data = $this->db->get()->result();

    $no = (0 + $start);
    $hasil = [];
    foreach ($get_data as $row) {
      $no++;

      $sts = '';

      if ($row->status_approve == '1') {
        $sts = '<label class="label label-success">Approved</label>';
      } else {
        $sts = '<label class="label label-danger">Belum di Approve</label>';
      }

      if (has_permission($this->managePermission)) {
        $action = '
          <a class="btn btn-success btn-sm" href="' . base_url('/wt_invoicing/createInvoice/' . $row->id_spkmarketing) . '" title="Create Invoice" data-no_inquiry="' . $row->no_inquiry . '"><i class="fa fa-check">&nbsp;Create Invoice</i></a>
        ';

        $action .= '
          <a class="btn btn-warning btn-sm" href="' . base_url('/wt_invoicing/createProformaInvoice/' . $row->id_spkmarketing) . '" title="Create Proforma Invoice" data-no_inquiry="' . $row->no_inquiry . '"><i class="fa fa-check">&nbsp;Create Proforma Invoice</i></a>
        ';
      }

      $hasil[] = [
        'no' => $no,
        'tanggal_spk_terbit' => date('d F Y', strtotime($row->tgl_spk_marketing)),
        'no_spk' => $row->no_surat,
        'customer' => $row->name_customer,
        'nilai_spk' => number_format($row->total, 2),
        'status' => $sts,
        'action' => $action
      ];
    }

    $response = [
      'draw' => intval($draw),
      'recordsTotal' => $count_filtered,
      'recordsFiltered' => $count_filtered,
      'data' => $hasil
    ];

    echo json_encode($response);
  }
}
