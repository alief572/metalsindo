<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Retur_penjualan_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
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
		$query = $this->db->query("SELECT MAX(id_retur) as max_id FROM tr_retur_penjualan");
		$row = $query->row_array();
		$thn = date('y');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 3, 5);
		$counter = $max_id1 + 1;
		$idcust = "R" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
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
		$blnthn = date('Y-m');
		$query = $this->db->query("SELECT MAX(no_retur) as max_id FROM tr_retur_penjualan WHERE Year(tahun)='$thn' AND month(tahun)='$bulan'");
		$row = $query->row_array();
		// $thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, -4);
		$counter = $max_id1 + 1;
		$idcust = "RTR-SPK/" . $tahun . "/" . $romawi . "/" . sprintf("%04s", $counter);
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
		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_spk_marketing a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->order_by('a.id_spkmarketing', DESC);
		$query = $this->db->get();
		return $query->result();
	}
	public function CariRetur()
	{
		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_retur_penjualan a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->where('a.sts <>', '1');
		$this->db->or_where('a.sts', null);
		$this->db->order_by('a.id_retur', DESC);
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

	public function CariSPKRetur()
	{
		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_spk_marketing_retur a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->order_by('a.id_spkmarketing', DESC);
		$query = $this->db->get();
		return $query->result();
	}


	public function get_retur_incoming()
	{
		$ENABLE_MANAGE = has_permission('Retur_Penjualan.Manage');

		$post   = $this->input->post();
		$draw   = intval($post['draw']);
		$length = intval($post['length']);
		$start  = intval($post['start']);
		$search = $post['search']['value'];

		$this->db->from('v_retur_incoming');

		// Filter Search
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('no_spk', $search, 'both');
			$this->db->or_like('name_customer', $search, 'both');
			$this->db->or_like('all_no_do', $search, 'both');
			$this->db->or_like('tgl_spk_marketing', $search, 'both');
			$this->db->group_end();
		}
<<<<<<< HEAD

		// Hitung total setelah filter
		$count_filter = $this->db->count_all_results('', false);

		// Ordering
		$this->db->order_by('id_spkmarketing', 'DESC');

		// Limit & Offset
		$this->db->limit($length, $start);
		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = $start;
=======

		// Hitung total setelah filter
		$count_filter = $this->db->count_all_results('', false);
>>>>>>> 1dc243b37598ac4ab7f585bf1418d1729f2a6872

		// Ordering
		$this->db->order_by('id_spkmarketing', 'DESC');

<<<<<<< HEAD
=======
		// Limit & Offset
		$this->db->limit($length, $start);
		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = $start;

		foreach ($get_data as $item) {
			$no++;

>>>>>>> 1dc243b37598ac4ab7f585bf1418d1729f2a6872
			// Status Badge logic
			$status = ($item->status_approve == '1')
				? '<span class="badge bg-green">Approve</span>'
				: '<span class="badge bg-red">Belum di Approve</span>';

			// Action logic
			$action = '';
			if ($ENABLE_MANAGE) {
				$action = '<a class="btn btn-info btn-sm" href="' . base_url('/retur_penjualan/proses_incoming/' . $item->id_spkmarketing) . '" title="Edit"><i class="fa fa-edit"></i></a>';
			}

			$hasil[] = [
				'no'                 => $no,
				'tanggal_spk_terbit' => date('d F Y', strtotime($item->tgl_spk_marketing)),
				'no_spk'             => $item->no_spk,
				'customer'           => $item->name_customer,
				'no_do'              => $item->all_no_do, // Sudah dapet string dari View
				'status'             => $status,
				'action'             => $action
			];
		}

		$response = [
			'draw'            => $draw,
			'recordsTotal'    => $count_filter, // Sesuaikan jika ingin real count_all tanpa filter
			'recordsFiltered' => $count_filter,
			'data'            => $hasil
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
<<<<<<< HEAD
	}

	public function get_last_stock($lotno)
	{
		$this->db->select('a.*');
		$this->db->from('stock_material a');
		$this->db->where('a.lotno', $lotno);
		$this->db->order_by('a.created_on', 'desc');
		$this->db->limit(1);
		$get_data = $this->db->get()->row();

		return $get_data;
	}

	public function get_data_nota_retur($post)
	{
		$this->db->from('v_nota_retur');

		// Search
		if (!empty($post['search']['value'])) {
			$search = $post['search']['value'];
			$this->db->group_start();
			$this->db->like('no_retur', $search);
			$this->db->or_like('name_customer', $search);
			$this->db->or_like('tgl_retur', $search);
			$this->db->group_end();
		}

		// Return object dengan data dan jumlah filter sekaligus
		$temp_db = clone $this->db;
		$count_filter = $temp_db->count_all_results();

		$this->db->order_by('id_retur', 'desc');
		$this->db->limit($post['length'], $post['start']);
		$query = $this->db->get();

		return [
			'data' => $query->result(),
			'count_filter' => $count_filter
		];
	}

	public function get_datatables_retur($length, $start, $search)
	{
		$this->db->select('a.*, b.name_customer');
		$this->db->select('(SELECT COALESCE(SUM(total_harga), 0) FROM dt_spkmarketing_retur WHERE id_spkmarketing = a.id_spkmarketing) AS nilai_spk');
		$this->db->from('tr_spk_marketing_retur a');
		$this->db->join('master_customers b', 'b.id_customer = a.id_customer');

		$this->db->group_start()
			->where('a.sts <>', '1')
			->or_where('a.sts IS NULL', null, false)
			->group_end();

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_surat', $search);
			$this->db->or_like('b.name_customer', $search);
			$this->db->group_end();
		}

		$this->db->order_by('a.id_spkmarketing', 'desc');
		if ($length != -1) $this->db->limit($length, $start);

		return $this->db->get()->result();
	}

	/* ------------------------- Di dalam M_retur.php ------------------------- */

	/**
	 * Base Query untuk menghindari pengulangan kode
	 * (Private function agar konsisten antara get data & count filtered)
	 */
	private function _get_main_query()
	{
		$this->db->from('tr_spk_marketing_retur a');
		$this->db->join('master_customers b', 'b.id_customer = a.id_customer');

		// Filter Status: sts tidak sama dengan 1 atau NULL
		$this->db->group_start()
			->where('a.sts <>', '1')
			->or_where('a.sts IS NULL', null, false)
			->group_end();
	}

	/**
	 * Menghitung SEMUA data tanpa filter search
	 */
	public function count_all_retur()
	{
		$this->_get_main_query();
		return $this->db->count_all_results();
	}

	/**
	 * Menghitung data setelah diterapkan filter SEARCH
	 */
	public function count_filtered_retur($search = null)
	{
		$this->_get_main_query();

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_surat', $search);
			$this->db->or_like('b.name_customer', $search);
			// Sesuaikan format tgl jika ingin bisa disearch (opsional)
			$this->db->or_like('DATE_FORMAT(a.tgl_spk_marketing, "%d %M %Y")', $search);
			$this->db->group_end();
		}

		return $this->db->count_all_results();
=======
>>>>>>> 1dc243b37598ac4ab7f585bf1418d1729f2a6872
	}

	public function get_last_stock($lotno)
	{
		$this->db->select('a.*');
		$this->db->from('stock_material a');
		$this->db->where('a.lotno', $lotno);
		$this->db->order_by('a.created_on', 'desc');
		$this->db->limit(1);
		$get_data = $this->db->get()->row();

		return $get_data;
	}

	public function get_data_nota_retur($post)
	{
		$this->db->from('v_nota_retur');

		// Search
		if (!empty($post['search']['value'])) {
			$search = $post['search']['value'];
			$this->db->group_start();
			$this->db->like('no_retur', $search);
			$this->db->or_like('name_customer', $search);
			$this->db->or_like('tgl_retur', $search);
			$this->db->group_end();
		}

		// Return object dengan data dan jumlah filter sekaligus
		$temp_db = clone $this->db;
		$count_filter = $temp_db->count_all_results();

		$this->db->order_by('id_retur', 'desc');
		$this->db->limit($post['length'], $post['start']);
		$query = $this->db->get();

		return [
			'data' => $query->result(),
			'count_filter' => $count_filter
		];
	}

	public function get_datatables_retur($length, $start, $search)
	{
		$this->db->select('a.*, b.name_customer');
		$this->db->select('(SELECT COALESCE(SUM(total_harga), 0) FROM dt_spkmarketing_retur WHERE id_spkmarketing = a.id_spkmarketing) AS nilai_spk');
		$this->db->from('tr_spk_marketing_retur a');
		$this->db->join('master_customers b', 'b.id_customer = a.id_customer');

		$this->db->group_start()
			->where('a.sts <>', '1')
			->or_where('a.sts IS NULL', null, false)
			->group_end();

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_surat', $search);
			$this->db->or_like('b.name_customer', $search);
			$this->db->group_end();
		}

		$this->db->order_by('a.id_spkmarketing', 'desc');
		if ($length != -1) $this->db->limit($length, $start);

		return $this->db->get()->result();
	}

	/* ------------------------- Di dalam M_retur.php ------------------------- */

	/**
	 * Base Query untuk menghindari pengulangan kode
	 * (Private function agar konsisten antara get data & count filtered)
	 */
	private function _get_main_query()
	{
		$this->db->from('tr_spk_marketing_retur a');
		$this->db->join('master_customers b', 'b.id_customer = a.id_customer');

		// Filter Status: sts tidak sama dengan 1 atau NULL
		$this->db->group_start()
			->where('a.sts <>', '1')
			->or_where('a.sts IS NULL', null, false)
			->group_end();
	}

	/**
	 * Menghitung SEMUA data tanpa filter search
	 */
	public function count_all_retur()
	{
		$this->_get_main_query();
		return $this->db->count_all_results();
	}

	/**
	 * Menghitung data setelah diterapkan filter SEARCH
	 */
	public function count_filtered_retur($search = null)
	{
		$this->_get_main_query();

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_surat', $search);
			$this->db->or_like('b.name_customer', $search);
			// Sesuaikan format tgl jika ingin bisa disearch (opsional)
			$this->db->or_like('DATE_FORMAT(a.tgl_spk_marketing, "%d %M %Y")', $search);
			$this->db->group_end();
		}

		return $this->db->count_all_results();
	}

	// public function get_last_stock($lotno) {
	// 	$this->db->select('a.*');
	// 	$this->db->from('stock_material a');
	// 	$this->db->where('a.lotno', $lotno);
	// 	$this->db->order_by('a.created_on', 'desc');
	// 	$this->db->limit(1);
	// 	$get_data = $this->db->get()->row();

	// 	return $get_data;
	// }
}
