<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Kartu_stock_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	public function get_material()
	{
		$this->db->select('a.*');
		$this->db->from('ms_inventory_category3 a');
		$this->db->where('a.deleted', '0');
		$this->db->where('a.aktif', 'aktif');
		$get_data = $this->db->get()->result();

		return $get_data;
	}

	public function get_list_warehouse()
	{
		$this->db->select('a.id, a.wh_code, a.wh_name');
		$this->db->from('ms_warehouse a');
		$this->db->where('a.deleted IS NULL');
		return $this->db->get()->result_array();
	}

	public function get_warehouse($id_gudang)
	{
		$this->db->select('a.*');
		$this->db->from('ms_warehouse a');
		$this->db->where('a.id', $id_gudang);
		$get_data = $this->db->get()->row();

		return $get_data;
	}

	public function get_stock($id_material, $id_gudang, $tgl_filter = null)
	{
		if ($tgl_filter == date('Y-m-d')) {
			$this->db->select('a.qty_stock, a.qty_booking, (a.qty_stock - a.qty_booking) as qty_free');
			$this->db->from('warehouse_stock a');
			$this->db->where('a.id_material', $id_material);
			$this->db->where('a.id_gudang', $id_gudang);
		} else {
			$this->db->select('a.qty_stock, a.qty_booking, (a.qty_stock - a.qty_booking) as qty_free');
			$this->db->from('warehouse_stock_backup a');
			$this->db->where('a.id_material', $id_material);
			$this->db->where('a.id_gudang', $id_gudang);
			$this->db->where('DATE_FORMAT(a.tgl, "%Y-%m-%d") = ', $tgl_filter);
		}
		$get_data = $this->db->get()->row();

		return $get_data;
	}

	public function get_stock_by_tgl($id_gudang, $tgl_filter)
	{
		if ($tgl_filter == date('Y-m-d')) {
			$this->db->select('a.id_material, a.nm_material, a.qty_stock, a.qty_booking, (a.qty_stock - a.qty_booking) as qty_free, b.maker as nm_supplier');
			$this->db->from('warehouse_stock a');
			$this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_material');
			$this->db->where('a.id_gudang', $id_gudang);
		} else {
			$this->db->select('a.id_material, a.nm_material, a.qty_stock, a.qty_booking, (a.qty_stock - a.qty_booking) as qty_free, b.maker as nm_supplier');
			$this->db->from('warehouse_stock_backup a');
			$this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_material');
			$this->db->where('a.id_gudang', $id_gudang);
			$this->db->where('DATE_FORMAT(a.tgl, "%Y-%m-%d") = ', $tgl_filter);
		}
		$get_data = $this->db->get()->result();

		return $get_data;
	}

	public function get_history_stock($id_material, $id_gudang)
	{
		$this->db->select('a.*');
		$this->db->from('warehouse_history a');
		$this->db->where('a.id_material', $id_material);
		// $this->db->where('DATE_FORMAT(a.update_date, "%Y-%m-%d")', $date_filter);
		// $this->db->group_start();
		$this->db->where('a.id_gudang', $id_gudang);
		// $this->db->group_end();
		$this->db->order_by('a.update_date', 'desc');
		$get_data = $this->db->get()->result_array();

		return $get_data;
	}
}
