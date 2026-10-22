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

	public function get_sj_print_data($dn_header, $retur_header)
	{
		$this->db->select('a.*');
		$this->db->from('dt_retur_pembelian a');
		$this->db->where('a.id_header', $retur_header->no_surat);
		$this->db->order_by('a.id', 'asc');
		$detail = $this->db->get()->result();
		$supplier = !empty($retur_header->id_supplier) ? $this->get_supplier($retur_header->id_supplier) : null;

		$po_numbers = array_values(array_filter(array_map('trim', explode(',', (string) $retur_header->no_po)), 'strlen'));
		$po = array();
		if (!empty($po_numbers)) {
			$this->db->select('a.no_po, a.no_surat, a.tanggal, a.matauang');
			$this->db->from('tr_purchase_order a');
			$this->db->where_in('a.no_po', array_unique($po_numbers));
			$this->db->order_by('a.no_po', 'asc');
			$po = $this->db->get()->result();
		}
		$po_by_number = array();
		foreach ($po as $item) $po_by_number[$item->no_po] = $item;

		$material_ids = array();
		foreach ($detail as $item) {
			if (!empty($item->id_material)) $material_ids[] = $item->id_material;
		}
		$material_shapes = array();
		if (!empty($material_ids)) {
			$this->db->select('a.id_category3, a.id_bentuk');
			$this->db->from('ms_inventory_category3 a');
			$this->db->where_in('a.id_category3', array_unique($material_ids));
			foreach ($this->db->get()->result() as $material) {
				$material_shapes[$material->id_category3] = $material->id_bentuk;
			}
		}

		$currency = !empty($retur_header->matauang) ? $retur_header->matauang : (!empty($po[0]->matauang) ? $po[0]->matauang : 'IDR');
		$total_receive = 0.0;
		$total_retur = 0.0;
		$calculated_subtotal = 0.0;
		$rows = array();
		foreach ($detail as $item) {
			$row = clone $item;
			$is_sheet = isset($material_shapes[$item->id_material]) && $material_shapes[$item->id_material] === 'B2000002';
			$receive_field = $is_sheet ? 'qty_sheet' : 'qty_receive';
			$retur_field = $is_sheet ? 'qty_sheet_retur' : 'jumlah_retur';
			$row->print_receive = isset($item->$receive_field) ? (float) $item->$receive_field : null;
			$row->print_retur = isset($item->$retur_field) ? (float) $item->$retur_field : null;
			$row->print_unit = $is_sheet ? 'Sheet' : 'KGS';
			$row->print_price_unit = $is_sheet ? '/Sheet' : '/Kg';
			$row->print_currency = !empty($item->matauang) ? $item->matauang : $currency;
			$row->print_total = isset($item->grand_total) ? (float) $item->grand_total : (float) $row->print_retur * (isset($item->harga_satuan) ? (float) $item->harga_satuan : 0);
			// Match the existing return detail screen: invoice-based returns use the return date; legacy returns use the PO date.
			$row->print_date = !empty($retur_header->id_rec_inv_ap) ? $retur_header->tgl_retur : (isset($po_by_number[$item->no_po]->tanggal) ? $po_by_number[$item->no_po]->tanggal : null);
			$total_receive += $row->print_receive;
			$total_retur += $row->print_retur;
			$calculated_subtotal += $row->print_total;
			$rows[] = $row;
		}
		$subtotal = isset($retur_header->subtotal) ? (float) $retur_header->subtotal : $calculated_subtotal;
		$ppn_persen = isset($retur_header->ppn_persen) ? (float) $retur_header->ppn_persen : 11;
		$nilai_ppn = isset($retur_header->nilai_ppn) ? (float) $retur_header->nilai_ppn : $subtotal * $ppn_persen / 100;

		return array(
			'header' => $dn_header, 'retur_header' => $retur_header, 'supplier' => $supplier,
			'po' => $po, 'detail' => $rows, 'currency' => $currency,
			'total_receive' => $total_receive, 'total_retur' => $total_retur,
			'subtotal' => $subtotal, 'ppn_persen' => $ppn_persen, 'nilai_ppn' => $nilai_ppn,
			'grand_total' => isset($retur_header->grand_total) ? (float) $retur_header->grand_total : $subtotal + $nilai_ppn
		);
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
