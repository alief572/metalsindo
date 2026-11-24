<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Model Master Spesifikasi (tabel ms_spesifikasi).
 * "Dipakai" dihitung dari ms_inventory_category3 (spek + id_category2).
 */

class Spesifikasi_model extends BF_Model
{
	protected $table_name = 'ms_spesifikasi';
	protected $key        = 'id_spesifikasi';

	public function __construct()
	{
		parent::__construct();
	}

	public function normalize($nama)
	{
		return mb_strtoupper(trim((string) $nama));
	}

	/** ID berurutan: I20 + 5 digit (contoh I2000001). */
	public function generate_id()
	{
		$row = $this->db->query("SELECT MAX(id_spesifikasi) AS max_id FROM ms_spesifikasi")->row_array();
		$counter = ((int) substr((string) $row['max_id'], 3, 5)) + 1;
		return 'I20' . str_pad($counter, 5, '0', STR_PAD_LEFT);
	}

	/** Daftar spesifikasi + nama alloy + jumlah material yang memakai. */
	public function get_all()
	{
		return $this->db->query(
			"SELECT s.*, c.nama AS nama_alloy,
				(SELECT COUNT(*) FROM ms_inventory_category3 m
				  WHERE m.deleted = 0 AND (m.id_spesifikasi = s.id_spesifikasi
				        OR (m.id_category2 = s.id_category2 AND m.spek = s.nama))) AS dipakai
			 FROM ms_spesifikasi s
			 LEFT JOIN ms_inventory_category2 c ON c.id_category2 = s.id_category2
			 WHERE s.deleted = 0
			 ORDER BY c.nama ASC, s.nama ASC"
		)->result();
	}

	public function get_one($id)
	{
		return $this->db->query(
			"SELECT s.*, c.nama AS nama_alloy
			 FROM ms_spesifikasi s
			 LEFT JOIN ms_inventory_category2 c ON c.id_category2 = s.id_category2
			 WHERE s.deleted = 0 AND s.id_spesifikasi = ?",
			array($id)
		)->row();
	}

	/**
	 * Daftar Type Alloy. $only_active = true hanya yang aktif;
	 * $keep = id alloy yang tetap ikut walau nonaktif (saat edit).
	 */
	public function get_alloy($only_active = false, $keep = '')
	{
		$this->db->select('id_category2, nama, aktif')->from('ms_inventory_category2')->where('deleted', 0);
		if ($only_active) {
			$this->db->group_start()->where('aktif', 'aktif');
			if ($keep !== '') {
				$this->db->or_where('id_category2', $keep);
			}
			$this->db->group_end();
		}
		return $this->db->order_by('nama', 'ASC')->get()->result();
	}

	public function alloy_valid($id_category2, $keep = '')
	{
		$this->db->where('deleted', 0)->where('id_category2', $id_category2);
		if ($id_category2 !== $keep) {
			$this->db->where('aktif', 'aktif');
		}
		return $this->db->count_all_results('ms_inventory_category2') > 0;
	}

	public function is_duplicate($id_category2, $nama, $exclude_id = '')
	{
		$this->db->where('deleted', 0)
			->where('id_category2', $id_category2)
			->where('UPPER(nama) = ' . $this->db->escape($nama), null, false);
		if ($exclude_id !== '') {
			$this->db->where('id_spesifikasi !=', $exclude_id);
		}
		return $this->db->count_all_results('ms_spesifikasi') > 0;
	}

	/** Material yang memakai spesifikasi: lewat id_spesifikasi, atau teks spek + alloy (data lama). */
	private function where_used($row)
	{
		$this->db->where('deleted', 0)
			->group_start()
				->where('id_spesifikasi', $row->id_spesifikasi)
				->or_group_start()
					->where('id_category2', $row->id_category2)
					->where('spek', $row->nama)
				->group_end()
			->group_end();
	}

	public function count_used($row)
	{
		$this->where_used($row);
		return (int) $this->db->count_all_results('ms_inventory_category3');
	}

	public function get_material($row)
	{
		$this->db->select('id_category3, nama');
		$this->where_used($row);
		return $this->db->order_by('id_category3', 'ASC')->get('ms_inventory_category3')->result();
	}
}
