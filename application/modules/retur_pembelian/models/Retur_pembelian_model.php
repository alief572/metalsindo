<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Retur_pembelian_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $viewPermission 	= 'Retur_pembelian.View';
	protected $addPermission  	= 'Retur_pembelian.Add';
	protected $managePermission = 'Retur_pembelian.Manage';
	protected $deletePermission = 'Retur_pembelian.Delete';

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

	public function get_supplier($id_supplier = null)
	{
		$this->db->select('a.id_suplier, a.name_suplier');
		$this->db->from('master_supplier a');
		$this->db->where('a.deleted', '0');
		if(!empty($id_supplier)) {
			$this->db->where('a.id_suplier', $id_supplier);
			$get_data = $this->db->get()->row();
		} else {
			$this->db->order_by('a.name_suplier', 'asc');
			$get_data = $this->db->get()->result();
		}

		return $get_data;
	}

	public function get_no_po($id_supplier = null, $arr_val = null)
	{
		$this->db->select('a.*');
		$this->db->from('tr_purchase_order a');
		if (!empty($id_supplier)) {
			$this->db->where('a.id_suplier', $id_supplier);
		}
		if (!empty($arr_val)) {
			$target_values = is_array($arr_val[1][0]) ? $arr_val[1][0] : $arr_val[1];
			$this->db->where_in($arr_val[0], $target_values);
		}
		$this->db->where('a.sts_retur', 'N');
		$this->db->order_by('a.created_on', 'desc');

		$get_data = $this->db->get()->result();

		return $get_data;
	}

	public function get_po_detail($no_po)
	{
		$this->db->select('a.*');
		$this->db->from('dt_trans_po a');
		$this->db->where('a.no_po', $no_po);
		$this->db->order_by('a.id', 'asc');

		$get_data = $this->db->get()->result();

		return $get_data;
	}

	public function get_po_check_sheet($no_po)
	{
		$this->db->select('a.id');
		$this->db->from('dt_trans_po a');
		$this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.idmaterial', 'left');
		$this->db->where('a.no_po', $no_po);
		$this->db->where('b.id_bentuk', 'B2000002');
		$get_data = $this->db->get()->num_rows();

		return $get_data;
	}

	public function BuatNomor($kode = '')
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
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_retur_pembelian WHERE Year(tgl_retur)='$thn' AND month(tgl_retur)='$bulan'");
		$row = $query->row_array();
		// $thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, -4);
		$counter = $max_id1 + 1;
		$idcust = "RTR-PMB/" . $tahun . "/" . $romawi . "/" . sprintf("%04s", $counter);
		return $idcust;
	}

	public function get_retur_header($id = null) {
		$this->db->select('a.*');
		$this->db->from('tr_retur_pembelian a');
		if(!empty($id)) {
			$this->db->where('a.id', $id);
			
			$get_data = $this->db->get()->row();
		} else {
			$get_data = $this->db->get()->result();
		}

		return $get_data;
	}

	public function get_retur_detail($id_header) {
		$this->db->select('a.*');
		$this->db->from('dt_retur_pembelian a');
		$this->db->where('a.id_header', $id_header);
		$get_data = $this->db->get()->result();

		return $get_data;
	}
}
