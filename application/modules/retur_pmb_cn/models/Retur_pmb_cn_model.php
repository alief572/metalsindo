<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Retur_pmb_cn_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $viewPermission 	= 'Retur_Credit_Note.View';
	protected $addPermission  	= 'Retur_Credit_Note.Add';
	protected $managePermission = 'Retur_Credit_Note.Manage';
	protected $deletePermission = 'Retur_Credit_Note.Delete';

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

	public function get_retur_header($id = null)
	{
		$this->db->select('a.*');
		$this->db->from('tr_retur_pembelian a');
		if (!empty($id)) {
			$this->db->where('a.id', $id);
			$get_data = $this->db->get()->row();
		} else {
			$get_data = $this->db->get()->result();
		}

		return $get_data;
	}

	public function get_retur_detail($id_header)
	{
		$this->db->select('a.*');
		$this->db->from('dt_retur_pembelian a');
		$this->db->where('a.id_header', $id_header);
		$get_data = $this->db->get()->result();

		return $get_data;
	}

	public function get_dn_header($id_dn = null)
	{
		$this->db->select('a.*');
		$this->db->from('tr_dn_retur_pmb a');
		if (!empty($id_dn)) {
			$this->db->where('a.id', $id_dn);
			$get_data = $this->db->get()->row();
		} else {
			$get_data = $this->db->get()->result();
		}

		return $get_data;
	}

	public function get_dn_detail($id_dn = null)
	{
		$this->db->select('a.*');
		$this->db->from('dt_dn_retur_pmb a');
		if (!empty($id_dn)) {
			$this->db->where('a.id_cn', $id_dn);
		}
		$get_data = $this->db->get()->result();

		return $get_data;
	}

	public function get_supplier($id_supplier = null) {
		$this->db->select('a.*');
		$this->db->from('master_supplier a');
		if(!empty($id_supplier)) {
			$this->db->where('a.id_suplier', $id_supplier);
			$get_data = $this->db->get()->row();
		} else {
			$get_data = $this->db->get()->result();
		}

		return $get_data;
	}

	public function get_po($id_po = null) {
		$this->db->select('a.*');
		$this->db->from('tr_purchase_order a');
		if(!empty($id_po)) {
			if(strpos($id_po, ',') !== false) {
				$this->db->where_in('a.no_po', explode(',', $id_po));
				$get_data = $this->db->get()->result();
			} else {
				$this->db->where('a.no_po', $id_po);
				$get_data = $this->db->get()->row();
			}
		} else {
			$get_data = $this->db->get()->result();
		}

		return $get_data;
	}

	public function generate_no_dn()
	{
		$this->db->select('MAX(RIGHT(no_surat, 4)) as max');
		$this->db->from('tr_dn_retur_pmb a');
		$this->db->like('a.no_surat', 'DN-PMB-' . date('Ym'), 'both');
		$get_data = $this->db->get()->row();

		$max = (!empty($get_data->max)) ? $get_data->max : 0;

		$counter = (int)$max + 1;

		$id = 'DN-PMB-' . date('Ym') . '-' . sprintf("%04s", $counter);

		return $id;
	}
}
