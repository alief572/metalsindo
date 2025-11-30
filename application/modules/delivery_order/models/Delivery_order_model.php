<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Delivery_order_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $viewPermission 	= 'Delivery_Order.View';
	protected $addPermission  	= 'Delivery_Order.Add';
	protected $managePermission = 'Delivery_Order.Manage';
	protected $deletePermission = 'Delivery_Order.Delete';

	protected $table_name = 'ms_inventory_category3';
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

	function generate_code($kode = '')
	{
		$query = $this->db->query("SELECT MAX(id_delivery_order) as max_id FROM tr_delivery_order");
		$row = $query->row_array();
		$thn = date('y');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 4, 5);
		$counter = $max_id1 + 1;
		$idcust = "DO" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
		return $idcust;
	}
	function BuatNomorOld($kode = '')
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
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_delivery_order WHERE month(tgl_delivery_order)='$bulan' and Year(tgl_delivery_order)='$tahun'");
		$row = $query->row_array();
		$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 0, 3);
		$counter = $max_id1 + 1;
		$idcust = sprintf("%03s", $counter) . "/DO-MP/" . $romawi . "/" . $tahun;
		return $idcust;
	}

	function BuatNomor($kode = '')
	{
		$bulan = date('m');
		$thn = date('Y');
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

		$bulan_kode = substr($kode, 11, 3);
		$bulan_kode2 = str_replace('/', '', $bulan_kode);
		$bulan_kode3 = $romawiToString[$bulan_kode2];

		$blnthn = date('Y-m');
		$query = $this->db->query("SELECT MAX(RIGHT(no_surat, 4)) as max_id FROM tr_delivery_order WHERE no_surat LIKE '%/" . date('y', strtotime($thn)) . "%'");
		$row = $query->row_array();
		//$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) $max_id;
		$counter = $max_id1 + 1;
		$idcust = "DO-MP/" . $tahun . "/" . $romawi . "/" . sprintf("%04s", $counter);

		return $idcust;
	}
	public function CariMaterial($id_crcl)
	{
		$this->db->select('a.*, b.nama as nama3, b.hardness as hardness, c.nama as nama2');
		$this->db->from('dt_inquery_transaksi a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3=a.id_category3');
		$this->db->join('ms_inventory_category2 c', 'c.id_category2=b.id_category2');
		$this->db->order_by('a.id_dt_inquery', 'DESC');
		$this->db->where('a.no_inquery', $id_crcl);
		$query = $this->db->get();
		return $query->result();
	}

	public function CariDO()
	{
		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_delivery_order a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->order_by('a.id_delivery_order', 'DESC');
		$query = $this->db->get();
		return $query->result();
	}
	public function CariDOopen()
	{
		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_delivery_order a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->order_by('a.id_delivery_order', 'DESC');
		$this->db->where('a.status_approve', '1');
		$this->db->where('a.status_invoice', 'OPN');
		$query = $this->db->get();
		return $query->result();
	}


	public function getHeaderPenawaran($id)
	{
		$this->db->select('a.*, b.name_customer as name_customer, b.address_office as address_office, b.telephone as telephone,b.fax as fax');
		$this->db->from('tr_delivery_order a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->where('a.id_delivery_order', $id);
		$query = $this->db->get();
		return $query->result();
	}
	public function caristok($id_crcl)
	{
		$this->db->select('a.*, b.density as density');
		$this->db->from('stock_material a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3=a.id_category3');
		$this->db->where('a.id_category3', $id_crcl);
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
		$query = $this->db->get();
		return $query->result();
	}
	function level_2($inventory_1)
	{
		$search = "deleted='0' and id_type='$inventory_1'";
		$this->db->where($search);
		$this->db->order_by('id_category1', 'ASC');
		return $this->db->from('ms_inventory_category1')
			->get()
			->result();
	}
	function level_3($id_inventory2)
	{
		$search = "deleted='0' and id_category1='$id_inventory2'";
		$this->db->where($search);
		$this->db->order_by('id_category2', 'ASC');
		return $this->db->from('ms_inventory_category2')
			->get()
			->result();
	}
	function compotition($id_inventory2)
	{
		$search = "deleted='0' and id_category1='$id_inventory2'";
		$this->db->where($search);
		$this->db->order_by('id_compotition', 'ASC');
		return $this->db->from('ms_compotition')
			->get()
			->result();
	}
	function bentuk($id_bentuk)
	{
		$search = "deleted='0' and id_bentuk='$id_bentuk'";
		$this->db->where($search);
		$this->db->order_by('id_dimensi', 'ASC');
		return $this->db->from('ms_dimensi')
			->get()
			->result();
	}
	function level_4($id_inventory3)
	{
		$this->db->where('id_category2', $id_inventory3);
		$this->db->order_by('id_category3', 'ASC');
		return $this->db->from('ms_inventory_category3')
			->get()
			->result();
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

	function get_produksi()
	{
		$this->db->order_by('no_surat', 'DESC');
		return $this->db->from('dt_tr_spk_produksi')
			->get()
			->result();
	}

	function getById($id)
	{
		return $this->db->get_where('inven_lvl2', array('id_inventory2' => $id))->row_array();
	}

	public function get_data_category()
	{
		$search = "a.deleted='0'";
		$this->db->select('a.*, b.nama as nama_category2, c.nilai_dimensi as nilai_dimensi,d.nm_bentuk as nm_bentuk');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_inventory_category2 b', 'b.id_category2 =a.id_category2');
		$this->db->join('child_inven_dimensi c', 'c.id_category3 =a.id_category3');
		$this->db->join('ms_bentuk d', 'd.id_bentuk =a.id_bentuk');
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();
	}

	public function getpenawaran($id)
	{
		$search = "a.no_penawaran='$id' ";
		$this->db->select('a.*, b.nama as nama_category3, b.hardness as hardness, c.nama as nama_category2, d.nilai_dimensi as thickness');
		$this->db->from('child_penawaran a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3 =a.id_category3');
		$this->db->join('ms_inventory_category2 c', 'c.id_category2 =b.id_category2');
		$this->db->join('child_inven_dimensi d', 'd.id_category3 =b.id_category3');
		$this->db->where('a.no_penawaran', $id);
		$query = $this->db->get();
		return $query->result();
	}

	public function getview($id)
	{
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1, d.nama as nama_category2');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_inventory_type b', 'b.id_type=a.id_type');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 =a.id_category1');
		$this->db->join('ms_inventory_category2 d', 'd.id_category2 =a.id_category2');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_child_compotition($id)
	{
		$this->db->select('a.*, b.name_compotition as name_compotition');
		$this->db->from('dt_material_compotition a');
		$this->db->join('ms_material_compotition b', 'b.id_compotition=a.id_compotition');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}
	public function get_child_dimention($id)
	{
		$this->db->select('a.*, b.dimensi_bentuk as dimensi_bentuk');
		$this->db->from('dt_material_dimensi a');
		$this->db->join('child_dimensi_bentuk b', 'b.id_dimensi_bentuk=a.id_dimensi_bentuk');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}
	public function get_child_suplier($id)
	{
		$this->db->select('a.*, b.name_supplier as name_supplier');
		$this->db->from('dt_material_supplier a');
		$this->db->join('master_supplier b', 'b.id_supplier=a.id_supplier');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}

	public function getSpek($id)
	{
		$this->db->select('a.*, b.name_compotition as name_compotition');
		$this->db->from('dt_material_compotition a');
		$this->db->join('ms_material_compotition b', 'b.id_compotition = a.id_compotition');
		$this->db->where('a.id_category3', $id);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_delivery_order()
	{
		$draw = $this->input->post('draw');
		$length = $this->input->post('length');
		$start = $this->input->post('start');
		$search = $this->input->post('search');

		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_delivery_order a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('DATE_FORMAT(a.tgl_delivery_order, "%d-%M-%Y")', $search['value'], 'both');
			$this->db->or_like('a.no_surat', $search['value'], 'both');
			$this->db->or_like('a.no_spk_marketing', $search['value'], 'both');
			$this->db->or_like('b.name_customer', $search['value'], 'both');
			$this->db->or_like('a.type', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.id_delivery_order');
		$this->db->order_by('a.id_delivery_order', 'desc');

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->limit($length, $start);
		$query = $this->db->get();

		$hasil = [];

		$no = (0 + $start);
		foreach ($query->result() as $item) {
			$no++;

			$button = '';

			if ($item->status_approve == 1) {
				if (has_permission($this->viewPermission)) {
					$button = '
						<a class="btn btn-primary btn-sm" href="' . base_url('/delivery_order/PrintHeader/' . $item->id_delivery_order) . '" target="_blank" title="Print"><i class="fa fa-print"></i>
						</a>
						<a class="btn btn-success btn-sm" href="' . base_url('/delivery_order/PrintHeaderWord/' . $item->id_delivery_order) . '" target="_blank" title="Print Word"><i class="fa fa-file-word-o"></i>
						</a>
						<a class="btn btn-success btn-sm" href="' . base_url('/delivery_order/PrintHeaderHtml/' . $item->id_delivery_order) . '" target="_blank" title="Ke Printer"><i class="fa fa-print"></i>
						</a>
						<a class="btn btn-warning btn-sm" href="' . base_url('/delivery_order/PrintHeaderSlitting/' . $item->id_delivery_order) . '" target="_blank" title="Print"><i class="fa fa-print"></i>
						</a>
						<a class="btn btn-primary btn-sm" href="' . base_url('/delivery_order/PrintHeaderWordSlitting/' . $item->id_delivery_order) . '" target="_blank" title="Print Word Slitting"><i class="fa fa-file-word-o"></i>
						</a>
					';
				}
			} else {
				if (has_permission($this->viewPermission)) {
					$button .= '
						<a class="btn btn-primary btn-sm" href="' . base_url('/delivery_order/PrintHeaderHtml/' . $item->id_delivery_order) . '" target="_blank" title="Print"><i class="fa fa-print"></i>
						</a>
						<a class="btn btn-success btn-sm" href="' . base_url('/delivery_order/PrintHeaderWord/' . $item->id_delivery_order) . '" target="_blank" title="Print Word"><i class="fa fa-file-word-o"></i>
						</a>
						<a class="btn btn-warning btn-sm" href="' . base_url('/delivery_order/PrintHeaderSlitting/' . $item->id_delivery_order) . '" target="_blank" title="Print"><i class="fa fa-print"></i>
						</a>
						<a class="btn btn-primary btn-sm" href="' . base_url('/delivery_order/PrintHeaderWordSlitting/' . $item->id_delivery_order) . '" target="_blank" title="Print Word Slitting"><i class="fa fa-file-word-o"></i>
						</a>
					';
				}
				if (has_permission($this->managePermission)) {
					$button .= '
						<a class="btn btn-info btn-sm" href="' . base_url('/delivery_order/editHeader/' . $item->id_delivery_order) . '" title="Edit"><i class="fa fa-edit"></i></i></a>
					';
				}
				// if (has_permission($this->managePermission)) {
				// 	$button .= '
				// 		<button type="text" class="btn btn-success btn-sm release" title="Release" data-id="' . $item->id_delivery_order . '"><i class="fa fa-check"></i></button>
				// 	';
				// }
			}

			$this->db->select('SUM(a.weight_mat) as total_fg');
			$this->db->from('dt_delivery_order_child a');
			$this->db->where('a.id_delivery_order', $item->id_delivery_order);
			$get_total_fg = $this->db->get()->row();

			$this->db->select('SUM(a.weight) as total_scrap');
			$this->db->from('dt_delivery_order_child_scrap a');
			$this->db->where('a.id_delivery_order', $item->id_delivery_order);
			$get_total_scrap = $this->db->get()->row();


			$this->db->select('a.width, a.weight_mat, b.id_bentuk, b.total_weight');
			$this->db->from('dt_delivery_order_child a');
			$this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_material', 'left');
			$this->db->where('a.id_delivery_order', $item->id_delivery_order);
			$get_do_item = $this->db->get()->result();

			$total_sheet = 0;
			foreach ($get_do_item as $item_do) :
				if ($item_do->id_bentuk == 'B2000002') {
					$total_sheet += ($item_do->width / $item_do->total_weight);
				}
			endforeach;

			$hasil[] = [
				'no' => $no,
				'tanggal_do' => date('d-M-Y', strtotime($item->tgl_delivery_order)),
				'no_do' => $item->no_surat,
				'no_spk_marketing' => $item->no_spk_marketing,
				'nm_customer' => strtoupper($item->name_customer),
				'total_fg' => number_format($get_total_fg->total_fg, 2),
				'total_scrap' => number_format($get_total_scrap->total_scrap, 2),
				'total_berat' => number_format($get_total_fg->total_fg + $get_total_scrap->total_scrap, 2),
				'total_sheet' => number_format($total_sheet, 2),
				'tipe' => strtoupper($item->type),
				'action' => $button
			];
		}

		echo json_encode([
			"draw" => $draw,
			"recordsTotal" => $count_all,
			"recordsFiltered" => $count_all,
			"data" => $hasil
		]);
	}
}
