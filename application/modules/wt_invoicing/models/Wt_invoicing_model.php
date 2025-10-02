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

    $romawiToString = [
      'I' => 1,
      'II' => 2,
      'III' => 3,
      'IV' => 4,
      'V' => 5,
      'VI' => 6,
      'VII' => 7,
      'VIII' => 8,
      'IX' => 9,
      'X' => 10,
      'XI' => 11,
      'XII' => 12
    ];

    $bulan_kode = explode('/', $kode);
    $bulan_kode2 = $bulan_kode[2];
    $bulan_kode3 = $romawiToString[$bulan_kode2];

    $blnthn = date('Y-m');
    $query = $this->db->query("SELECT MAX(RIGHT(no_surat, 4)) as max_id FROM tr_invoice WHERE no_surat LIKE '%/" . date('y', strtotime($th)) . "/" . $bulan_kode2 . "/%'");
    $row = $query->row_array();
    $thn = date('T');
    $max_id = $row['max_id'];
    $max_id1 = (int) $max_id;
    $counter = $max_id1 + 1;
    $idcust = "INV-MP/" . $tahun . "/" . $bulan_kode2 . "/" . sprintf("%04s", $counter);
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
    $this->db->order_by('a.id', 'desc');
    $this->db->limit($length, $start);

    $get_data = $this->db->get();

    $this->db->select('a.*, b.name_customer as name_customer');
    $this->db->from('tr_invoice a');
    $this->db->join('master_customers b', 'b.id_customer=a.id_customer');
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
    $this->db->order_by('a.id', 'desc');

    $get_data_all = $this->db->get();

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

      $hasil[] = [
        'no' => $no,
        'no_invoice' => $item['no_surat'],
        'nama_customer' => strtoupper($item['name_customer']),
        'term' => $item['note'],
        'nomor_do' => $item['no_do'],
        'nilai_invoice' => number_format($item['nilai_invoice']),
        'tanggal_invoice' => date('d-F-Y', strtotime($item['tgl_invoice'])),
        'action' => $action
      ];
    }

    echo json_encode([
      'draw' => intval($draw),
      'recordsTotal' => $get_data_all->num_rows(),
      'recordsFiltered' => $get_data_all->num_rows(),
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
}
