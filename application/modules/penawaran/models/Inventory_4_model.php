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
	protected $viewPermission 	= 'Penawaran.View';
	protected $addPermission  	= 'Penawaran.Add';
	protected $managePermission = 'Penawaran.Manage';
	protected $deletePermission = 'Penawaran.Delete';

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
		$query = $this->db->query("SELECT MAX(no_penawaran) as max_id FROM tr_penawaran");
		$row = $query->row_array();
		$thn = date('y');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 3, 5);
		$counter = $max_id1 + 1;
		$idcust = "P" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
		return $idcust;
	}
	function BuatNomorOld($kode = '')
	{
		$bulan = date('m');
		$tahun = date('Y');
		$thn = date('Y');
		//$tahun = date('y');
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
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_penawaran WHERE month(tgl_penawaran)='$bulan' and Year(tgl_penawaran)='$tahun'");
		// $query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_penawaran WHERE Year(tgl_penawaran)='$thn'");
		$row = $query->row_array();
		$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 0, 3);
		//$max_id1 =(int) substr($max_id,-4);
		$counter = $max_id1 + 1;
		$idcust = sprintf("%03s", $counter) . "/MP/Q/" . $romawi . "/" . $tahun;
		//$idcust = "Q-MP/".$tahun."/".$romawi."/".sprintf("%04s",$counter).;
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
		$query = $this->db->query("SELECT MAX(RIGHT(no_surat, 4)) as max_id FROM tr_penawaran WHERE no_surat LIKE '%/" . date('y', strtotime($thn)) . "/" . $romawi . "/%'");
		$row = $query->row_array();
		// $thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) $max_id;
		$counter = $max_id1 + 1;
		$idcust = "Q-MP/" . $tahun . "/" . $romawi . "/" . sprintf("%04s", $counter);
		return $idcust;
	}



	public function CariPenawaran()
	{
		$this->db->select('a.*, b.name_customer as name_customer, c.id_spkmarketing AS spkmarketing');
		$this->db->from('tr_penawaran a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->join('tr_spk_marketing c', 'a.no_penawaran=c.no_penawaran', 'left');
		$this->db->where('a.type', 'reguler');
		$this->db->group_by('a.no_penawaran');
		$this->db->order_by('a.no_penawaran', 'DESC');
		$query = $this->db->get();
		return $query->result();
	}

	public function CariPenawaranApproval()
	{
		$this->db->select('a.*, b.name_customer as name_customer, c.id_spkmarketing AS spkmarketing');
		$this->db->from('tr_penawaran a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->join('tr_spk_marketing c', 'a.no_penawaran=c.no_penawaran', 'left');
		$this->db->where('a.status_revisi', 1);
		$this->db->group_by('a.no_penawaran');
		$this->db->order_by('a.no_penawaran', 'DESC');
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
	public function PrintDetail($id)
	{
		$this->db->select('a.*, b.nama as nama3, b.spek, b.hardness as hardness, c.nama as nama2, e.nm_surface');
		$this->db->from('child_penawaran a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3=a.id_category3');
		$this->db->join('ms_inventory_category2 c', 'c.id_category2=b.id_category2');
		$this->db->join('ms_surface e', 'e.id_surface=b.id_surface');
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


	public function get_data_material()
	{
		$search = "a.deleted='0'";
		$this->db->select('a.*');
		$this->db->from('ms_inventory_category3 a');
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();

		print_r($query);
		exit;
	}

	public function get_data_category()
	{
		$search = "a.deleted='0'";
		$this->db->select('a.*, b.nama as nama_category2, c.nilai_dimensi as nilai_dimensi,d.nm_bentuk as nm_bentuk, e.nm_surface');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_inventory_category2 b', 'b.id_category2 =a.id_category2');
		$this->db->join('child_inven_dimensi c', 'c.id_category3 =a.id_category3');
		$this->db->join('ms_bentuk d', 'd.id_bentuk =a.id_bentuk');
		$this->db->join('ms_surface e', 'a.id_surface =e.id_surface');
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();
	}

	public function getpenawaran($id)
	{
		// print_r($id);
		// exit;
		$search = "a.no_penawaran='$id' ";
		$this->db->select('a.*, b.nama as nama_category3, b.maker as maker, b.hardness as hardness, c.nama as nama_category2,  d.nilai_dimensi as thickness, e.nm_surface');
		$this->db->from('child_penawaran a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3 =a.id_category3');
		$this->db->join('ms_inventory_category2 c', 'c.id_category2 =b.id_category2');
		$this->db->join('child_inven_dimensi d', 'd.id_category3 =b.id_category3');
		$this->db->join('ms_surface e', 'b.id_surface =e.id_surface');
		$this->db->where('a.no_penawaran', $id);
		$this->db->group_by('a.id_child_penawaran');
		$query = $this->db->get();
		return $query->result();

		// print_r($query);
		// exit;
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

	public function get_data_penawaran()
	{
		$draw = $this->input->post('draw');
		$length = $this->input->post('length');
		$start = $this->input->post('start');
		$search = $this->input->post('search');

		$this->db->select('a.*, b.name_customer as name_customer, c.id_spkmarketing AS spkmarketing');
		$this->db->from('tr_penawaran a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->join('tr_spk_marketing c', 'a.no_penawaran=c.no_penawaran', 'left');
		$this->db->where('a.type', 'reguler');
		if (!empty($search['value'])) {
			$this->db->like('a.no_surat', $search['value']);
			$this->db->or_like('b.name_customer', $search['value']);
			$this->db->or_like('a.tgl_penawaran', $search['value']);
		}
		$this->db->group_by('a.no_penawaran');
		$this->db->order_by('a.no_penawaran', 'desc');

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->limit($length, $start);

		$get_data = $this->db->get();

		$hasil = [];
		$no = (0 + $start);
		foreach ($get_data->result() as $item) :
			$no++;

			$get_spk = $this->db->select('no_surat')->get_where('tr_spk_marketing', array('no_penawaran' => $item->no_penawaran))->result_array();
			$arrImp = [];
			foreach ($get_spk as $key => $value) {
				$arrImp[] = $value['no_surat'];
			}

			$Status = "<span class='badge bg-yellow'>Open</span>";
			$keterangan = ucfirst(strtolower($item->keterangan));
			if ($item->spkmarketing != NULL) {
				$Status = "<span class='badge bg-green'>Closed</span>";
				$keterangan = implode("<br>", $arrImp);
			}
			if ($item->spkmarketing == NULL and $item->status == 'Y') {
				$Status = "<span class='badge bg-red'>Closed</span>";
			}

			$revisi = '';
			if ($item->status_revisi == 0) {
				$revisi = "<span class='badge bg-purple'>Tidak Ada Revisi</span>";
			} elseif ($item->status_revisi == 1) {
				$revisi = "<span class='badge bg-orange'>Menunggu Approval Revisi</span>";
			} elseif ($item->status_revisi == 2) {
				$revisi = "<span class='badge bg-green'>Revisi Disetujui</span>";
			} elseif ($item->status_revisi == 3) {
				$revisi = "<span class='badge bg-red'>Revisi Ditolak</span>";
			}

			$option = '';

			if (has_permission($this->viewPermission)) {
				$option .= ' <a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" data-no_penawaran="' . $item->no_penawaran . '"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($this->managePermission) && $item->status == 'N') {
				$option .= ' <a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" data-no_penawaran="' . $item->no_penawaran . '"><i class="fa fa-edit"></i></a>';
			}

			if (has_permission($this->viewPermission) && $item->spkmarketing == null && $item->status == 'N') {
				$option .= ' <a class="btn btn-primary btn-sm" href="' . base_url('/penawaran/detail/' . $item->no_penawaran) . '" title="Detail" data-no_inquiry="' . $item->no_inquiry . '"><i class="fa fa-table"></i></a>';
			}

			if (has_permission($this->viewPermission)) {
				$option .= ' <a class="btn btn-info btn-sm" href="' . base_url('/penawaran/PrintHeader/' . $item->no_penawaran) . '" target="_blank" title="Detail" data-no_inquiry="' . $item->no_inquiry . '"><i class="fa fa-print"></i></a>';
			}

			if (has_permission($this->managePermission) && $item->spkmarketing == null && $item->status == 'N') {
				$option .= ' <a class="btn bg-purple btn-sm close_penawaran" href="javascript:void(0)" title="Close Penawaran" data-no_penawaran="' . $item->no_penawaran . '"><i class="fa fa-check"></i></a>';
			}

			if (has_permission($this->managePermission)) {
				$option .= ' <a class="btn btn-success btn-sm copy" href="javascript:void(0)" title="Copy" data-no_penawaran="' . $item->no_penawaran . '"><i class="fa fa-copy"></i></a>';
			}

			if (has_permission($this->managePermission) && $item->spkmarketing != null && $item->status_revisi == 0) {
				$option .= ' <a class="btn btn-primary btn-sm revisi" href="javascript:void(0)" title="Ajukan Revisi" data-no_penawaran="' . $item->no_penawaran . '"><i class="fa fa-history"></i></a>';
			}

			if (has_permission($this->managePermission) && $item->spkmarketing != null && $item->status_revisi == '2') {
				$option .= '
				<a class="btn btn-primary btn-sm" href="' . base_url('/penawaran/detailrevisi/' . $item->no_penawaran) . '" title="Revisi" data-no_inquiry="' . $item->no_inquiry . '"><i class="fa fa-list"></i></a>
				';
			}

			$hasil[] = [
				'no' => $no,
				'no_surat' => $item->no_surat,
				'name_customer' => $item->name_customer,
				'tgl_penawaran' => $item->tgl_penawaran,
				'status' => $Status,
				'status_revisi' => $revisi,
				'keterangan' => $keterangan,
				'aksi' => $option
			];
		endforeach;

		echo json_encode([
			'draw' => $draw,
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		]);
	}

	public function get_category3($id_category3)
	{
		$this->db->select('a.*');
		$this->db->from('ms_inventory_category_3 a');
		$this->db->where('a.id_category3', $id_category3);
		return $this->db->get()->row_array();
	}

	public function get_last_price_sheet($id_category3)
	{
		$this->db->select('a.price_sheet');
		$this->db->from('child_penawaran a');
		$this->db->where('a.id_category3', $id_category3);
		$this->db->where('a.price_sheet >', 0);
		$this->db->order_by('a.id_child_penawaran', 'DESC');
		$this->db->limit(1);
		return $this->db->get()->row_array();
	}

	public function get_penawaran_detail($no_penawaran)
	{
		$this->db->select('a.*');
		$this->db->from('child_penawaran a');
		$this->db->where('a.no_penawaran', $no_penawaran);
		return $this->db->get()->result_array();
	}
}
