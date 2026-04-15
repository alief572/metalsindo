<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Inventory_4_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $viewPermission 	= 'Material_Planing.View';
	protected $addPermission  	= 'Material_Planing.Add';
	protected $managePermission = 'Material_Planing.Manage';
	protected $deletePermission = 'Material_Planing.Delete';

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
		$query = $this->db->query("SELECT MAX(id_spkmarketing) as max_id FROM tr_spk_marketing");
		$row = $query->row_array();
		$thn = date('y');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 3, 5);
		$counter = $max_id1 + 1;
		$idcust = "P" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
		return $idcust;
	}
	function BuatNomor($kode = '')
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
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_spk_marketing WHERE month(tgl_spk_marketing)='$bulan' and Year(tgl_spk_marketing)='$tahun'");
		$row = $query->row_array();
		$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 0, 3);
		$counter = $max_id1 + 1;
		$idcust = sprintf("%03s", $counter) . "/MP/SPK/" . $romawi . "/" . $tahun;
		return $idcust;
	}
	public function CariMaterial($id_crcl)
	{
		$this->db->select('a.*, b.nama as nama3, b.hardness as hardness, c.nama as nama2');
		$this->db->from('dt_inquery_transaksi a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3=a.id_category3');
		$this->db->join('ms_inventory_category2 c', 'c.id_category2=b.id_category2');
		$this->db->order_by('a.id_dt_inquery', DESC);
		$this->db->where('a.no_inquery', $id_crcl);
		$query = $this->db->get();
		return $query->result();
	}

	public function CariSPK()
	{
		// $search = "a.deal='1' and b.status_approve='1' and a.status_lanjutan!='3'";
		$search = "a.deal='1' and a.status_close_planning ='OPN' and a.approved='1'";
		$this->db->select('a.*, c.name_customer as name_customer, b.no_surat as no_surat, c.id_customer');
		$this->db->from('dt_spkmarketing a');
		$this->db->join('tr_spk_marketing b', 'b.id_spkmarketing=a.id_spkmarketing');
		$this->db->join('master_customers c', 'c.id_customer=b.id_customer');
		// $this->db->where('a.delivery <>','');
		$this->db->order_by('a.delivery', ASC);
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();
	}


	public function getHeaderPenawaran($id)
	{
		$this->db->select('a.*, b.name_customer as name_customer, b.address_office as address_office, b.telephone as telephone,b.fax as fax');
		$this->db->from('tr_penawaran a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->where('a.no_penawaran', $id);
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
		$this->db->select('a.*, b.nama as nama3,b.hardness as hardness, c.nama as nama2 , d.nilai_dimensi as nilai');
		$this->db->from('child_penawaran a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3=a.id_category3');
		$this->db->join('ms_inventory_category2 c', 'c.id_category2=b.id_category2');
		$this->db->join('child_inven_dimensi d', 'd.id_category3=a.id_category3');
		$this->db->where('a.no_penawaran', $id);
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


	public function get_data_material_planning()
	{
		$draw   = $this->input->post('draw');
		$start  = (int) $this->input->post('start');
		$length = (int) $this->input->post('length');
		$search = $this->input->post('search');

		$can_access = has_permission($this->viewPermission);

		// Apply search filter
		$this->db->from('view_material_planning');
		// Total tanpa filter
		$count_all = $this->db->count_all_results('', false);

		if (!empty($search['value'])) {
			$keyword = $search['value'];
			$this->db->group_start();
			$this->db->like('no_spk', $keyword);
			$this->db->or_like('customer', $keyword);
			$this->db->or_like('id_material', $keyword);
			$this->db->or_like('no_alloy', $keyword);
			$this->db->group_end();
		}

		$count_filtered = $this->db->count_all_results('', false);

		$rows = $this->db
			->order_by('id_dt_spkmarketing', 'ASC')
			->limit($length, $start)
			->get()
			->result();

		$no   = $start;
		$data = array_map(function ($item) use (&$no, $can_access) {
			$no++;
			return [
				'no'            => $no,
				'no_spk'        => $item->no_spk,
				'customer'      => $item->customer,
				'kode_material' => $item->id_material,
				'no_aloy'       => $item->no_alloy,
				'thickness'     => $item->thickness,
				'width'         => $item->width,
				'length'        => number_format($item->length_val, 2),
				'delivery_date' => date('d-M-Y', strtotime($item->delivery_date)),
				'total_weight'  => number_format($item->total_weight),
				'total_sheet'   => number_format($item->total_sheet),
				'total_spk'     => '-',
				'fg'            => number_format($item->fg_booking, 2),
				'action'        => $this->_material_planning_action_buttons($item, $can_access),
			];
		}, $rows);

		return [
			'draw'            => (int) $draw,
			'recordsTotal'    => $count_all,
			'recordsFiltered' => $count_filtered,
			'data'            => $data,
		];
	}

	private function _material_planning_action_buttons($item, $can_access)
	{
		if (!$can_access) {
			return '';
		}

		$id  = $item->id_dt_spkmarketing;
		$mat = $item->id_material;

		$buttons = array(
			'<a class="btn btn-warning btn-sm edit" href="javascript:void(0)" title="Lihat Stock"'
				. ' data-id_dt_spkmarketing="' . $id . '"'
				. ' data-id_material="' . $mat . '"'
				. ' data-width="' . $item->width . '"'
				. ' data-view="edit"><i class="fa fa-bars"></i></a>',

			'<a class="btn btn-primary btn-sm" href="' . base_url('/purchase_request/add_pr/' . $id) . '" title="Create PR">'
				. '<i class="fa fa-table"></i></a>',

			'<a class="btn btn-success btn-sm delete" href="javascript:void(0)" title="Approve"'
				. ' data-id_dt_spkmarketing="' . $id . '"'
				. ' data-id_material="' . $mat . '"><i class="fa fa-check"></i></a>',
		);

		if ($item->status_lanjutan == '2') {
			$buttons[] = '<a class="btn btn-warning btn-sm tutup" href="javascript:void(0)" title="Close"'
				. ' data-id_dt_spkmarketing="' . $id . '"'
				. ' data-id_material="' . $mat . '"><i class="fa fa-times"></i></a>';
		}

		return implode(' ', $buttons);
	}
}
