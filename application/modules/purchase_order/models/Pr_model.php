<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Pr_model extends BF_Model
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
		$query = $this->db->query("SELECT MAX(no_po) as max_id FROM tr_purchase_order");
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
		$blnthn = date('Y-m');
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_purchase_order WHERE month(tanggal)='$bulan' and Year(tanggal)='$tahun'");
		$row = $query->row_array();
		$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, -13, 3);
		$counter = $max_id1 + 1;
		$idcust = "PO-" . sprintf("%03s", $counter) . "/MP/" . $bulan . "/" . $tahun;
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
		$blnthn = date('Y-m');
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_purchase_order WHERE Year(tahun)='$th'");
		$row = $query->row_array();
		$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, -4);
		$counter = $max_id1 + 1;
		$idcust = "PO-MP/" . $tahun . "/" . $romawi . "/" . sprintf("%04s", $counter);
		return $idcust;
	}


	public function CariPenawaran()
	{
		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_penawaran a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->order_by('a.no_penawaran', DESC);
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

	function generate_request($kode = '')
	{
		$query = $this->db->query("SELECT MAX(no_request) as max_id FROM tr_request");
		$row = $query->row_array();
		$thn = date('y');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 3, 5);
		$counter = $max_id1 + 1;
		$idcust = "R" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
		return $idcust;
	}

	public function get_data_po()
	{
		$ENABLE_ADD     = has_permission('Purchase_Order.Add');
		$ENABLE_MANAGE  = has_permission('Purchase_Order.Manage');
		$ENABLE_VIEW    = has_permission('Purchase_Order.View');
		$ENABLE_DELETE  = has_permission('Purchase_Order.Delete');

		$draw = $this->input->post('draw');
		$length = $this->input->post('length');
		$start = $this->input->post('start');
		$search = $this->input->post('search');

		$this->db->select('a.no_po, a.no_surat, a.id_suplier, a.loi, a.nominal_kurs, a.tanggal, a.expect_tanggal, a.term, a.cif, a.hargatotal, a.diskontotal, a.taxtotal, a.subtotal, a.status, a.no_pr, b.name_suplier as namesup');
		$this->db->from('tr_purchase_order a');
		$this->db->join('master_supplier b', 'b.id_suplier = a.id_suplier');
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_surat', $search['value'], 'both');
			$this->db->or_like('a.tanggal', $search['value'], 'both');
			$this->db->or_like('b.name_suplier', $search['value'], 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->order_by('a.no_po', 'desc');
		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result_array();

		$hasil = [];

		$no = (0 + $start);
		foreach ($get_data as $item) {
			$no++;

			if ($item['status'] == '1') {
				$status = '<span class="badge bg-blue">Waiting</span>';
			} else if ($item['status'] == '2') {
				$status = '<span class="badge bg-green">Approved</span>';
			} else {
				$status = '<span class="badge bg-red">Closed</span>';
			}

			$option = '';
			// if ($ENABLE_MANAGE) {
			if ($ENABLE_VIEW) {
				$option .= ' <a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" data-no_po="' . $item['no_po'] . '"><i class="fa fa-eye"></i></a>';

				$option .= ' <a class="btn btn-primary btn-sm" href="' . base_url('/purchase_order/PrintH2/' . $item['no_po']) . '" target="_blank" title="Print"><i class="fa fa-print"></i></a>';
			}

			if ($ENABLE_MANAGE && $item['status'] == '1') {
				$this->db->select('a.*');
				$this->db->from('dt_trans_po a');
				$this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.idmaterial');
				$this->db->where('a.no_po', $item['no_po']);
				$this->db->where('b.id_bentuk', 'B2000002');
				$check_detail_sheet = $this->db->get()->result_array();

				$tipe_sheet = 1;
				if (count($check_detail_sheet) < 1) {
					$tipe_sheet = 0;
				}

				if ($tipe_sheet == 1) {
					$option .= ' <a class="btn btn-info btn-sm" href="' . base_url('/purchase_order/edit_sheet/' . $item['no_po']) . '" title="Edit"><i class="fa fa-edit"></i></a>';
				} else {
					$option .= ' <a class="btn btn-info btn-sm" href="' . base_url('/purchase_order/edit/' . $item['no_po']) . '" title="Edit"><i class="fa fa-edit"></i></a>';
				}

				$option .= ' <a class="btn btn-success btn-sm Approve" href="javascript:void(0)" title="Approval PO" data-no_po="' . $item['no_po'] . '"><i class="fa fa-check"></i></a>';
			}
			// }

			$hasil[] = [
				'no' => $no,
				'no_po' => $item['no_surat'],
				'tanggal_po' => date('d-M-Y', strtotime($item['tanggal'])),
				'supplier' => strtoupper($item['namesup']),
				'progress_pr' => $status,
				'action' => $option
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}
}
