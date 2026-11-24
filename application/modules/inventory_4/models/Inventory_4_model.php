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

	function generate_id($kode = '')
	{
		$query = $this->db->query("SELECT MAX(id_category3) as max_id FROM ms_inventory_category3");
		$row = $query->row_array();
		$thn = date('y');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 3, 5);
		$counter = $max_id1 + 1;
		$idcust = "I" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
		return $idcust;
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

	public function get_data_category3($id_bentuk)
	{
		$search = "a.deleted='0' and a.id_bentuk='$id_bentuk'";
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1, d.nama as nama_category2');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_inventory_type b', 'b.id_type=a.id_type');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 =a.id_category1');
		$this->db->join('ms_inventory_category2 d', 'd.id_category2 =a.id_category2');
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();
	}

	public function dimensy($id)
	{
		$search = "a.id_category3='$id'";
		$this->db->select('a.*, b.nm_dimensi as nm_dimensi');
		$this->db->from('child_inven_dimensi a');
		$this->db->join('ms_dimensi b', 'b.id_dimensi=a.id_dimensi');
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();
	}

	public function supl($id)
	{
		$search = "a.id_category3='$id'";
		$this->db->select('a.*, b.name_suplier as name_suplier');
		$this->db->from('child_inven_suplier a');
		$this->db->join('master_supplier b', 'b.id_suplier=a.id_suplier');
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();
	}

	public function kompos($id)
	{
		$search = "a.deleted='0' and a.id_category3='$id'";
		$this->db->select('a.*, b.name_compotition as name_compotition');
		$this->db->from('child_inven_compotition a');
		$this->db->join('ms_compotition b', 'b.id_compotition=a.id_compotition');
		$this->db->where($search);
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

	public function getedit($id)
	{
		$this->db->select('a.*, b.nm_bentuk as nm_bentuk');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_bentuk b', 'b.id_bentuk=a.id_bentuk');
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

	// Server Side Processing
	private function _get_datatables_query($id_bentuk)
	{
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1, d.nama as nama_category2');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_inventory_type b', 'b.id_type=a.id_type', 'left');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 =a.id_category1', 'left');
		$this->db->join('ms_inventory_category2 d', 'd.id_category2 =a.id_category2', 'left');
		$this->db->where('a.deleted', '0');
		$this->db->where('a.id_bentuk', $id_bentuk);

		$i = 0;

		if ($id_bentuk == 'B2000001') {
			$column_search = array('a.id_category3', 'b.nama', 'c.nama', 'd.nama', 'a.nama', 'a.spek', 'a.thickness', 'a.hardness', 'a.density', 'a.maker', 'a.negara');
			$column_order = array(null, 'a.id_category3', 'b.nama', 'c.nama', 'd.nama', 'a.nama', 'a.nama', 'a.spek', 'a.thickness', 'a.hardness', 'a.density', 'a.maker', 'a.negara', 'a.aktif', null);
		} else {
			$column_search = array('a.id_category3', 'b.nama', 'c.nama', 'd.nama', 'a.nama', 'a.hardness');
			$column_order = array(null, 'a.id_category3', 'b.nama', 'c.nama', 'd.nama', 'a.nama', 'a.nama', null, 'a.aktif', null);
		}

		foreach ($column_search as $item) {
			if ($_POST['search']['value']) {
				if ($i === 0) {
					$this->db->group_start();
					$this->db->like($item, $_POST['search']['value']);
				} else {
					$this->db->or_like($item, $_POST['search']['value']);
				}
				if (count($column_search) - 1 == $i)
					$this->db->group_end();
			}
			$i++;
		}

		if (isset($_POST['order'])) {
			$this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables($id_bentuk)
	{
		$this->_get_datatables_query($id_bentuk);
		if ($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered($id_bentuk)
	{
		$this->_get_datatables_query($id_bentuk);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_alls($id_bentuk)
	{
		$this->db->from('ms_inventory_category3');
		$this->db->where('deleted', '0');
		$this->db->where('id_bentuk', $id_bentuk);
		return $this->db->count_all_results();
	}

	/* ------------------------------------------------------------------
	 * Sheet (B2000002) & Material Ekuivalen
	 * ----------------------------------------------------------------*/

	const BENTUK_SHEET = 'B2000002';

	/** Opsi berjenjang untuk form Sheet. $keep = id yang tetap ikut walau nonaktif. */
	public function opsi($level, $parent, $keep = '')
	{
		if ($level === 'c1') {
			$tbl = 'ms_inventory_category1';
			$id = 'id_category1';
			$par = 'id_type';
		} elseif ($level === 'c2') {
			$tbl = 'ms_inventory_category2';
			$id = 'id_category2';
			$par = 'id_category1';
		} else {
			$tbl = 'ms_spesifikasi';
			$id = 'id_spesifikasi';
			$par = 'id_category2';
		}
		$this->db->select("$id AS id, nama", false)->from($tbl)
			->where('deleted', 0)->where($par, $parent)
			->group_start()->where('aktif', 'aktif');
		if ($keep !== '') {
			$this->db->or_where($id, $keep);
		}
		return $this->db->group_end()->order_by('nama', 'ASC')->get()->result();
	}

	/** Cek rantai Type > C1 > C2 > Spesifikasi. Mengembalikan pesan error atau ''. */
	public function validasi_hierarki($type, $c1, $c2, $spek, $old = null)
	{
		$ok = function ($tbl, $id_col, $id, $par_col, $par, $old_id) {
			$this->db->where('deleted', 0)->where($id_col, $id)->where($par_col, $par);
			if ($id !== $old_id) {
				$this->db->where('aktif', 'aktif');
			}
			return $this->db->count_all_results($tbl) > 0;
		};
		$o = $old ?: (object) array('id_type' => '', 'id_category1' => '', 'id_category2' => '', 'id_spesifikasi' => '');
		if (!$ok('ms_inventory_type', 'id_type', $type, 'id_type', $type, $o->id_type)) {
			return 'Type Inventory tidak valid.';
		}
		if (!$ok('ms_inventory_category1', 'id_category1', $c1, 'id_type', $type, $o->id_category1)) {
			return 'Ferous / Non Ferous tidak valid.';
		}
		if (!$ok('ms_inventory_category2', 'id_category2', $c2, 'id_category1', $c1, $o->id_category2)) {
			return 'Type Alloy tidak valid.';
		}
		if (!$ok('ms_spesifikasi', 'id_spesifikasi', $spek, 'id_category2', $c2, $o->id_spesifikasi)) {
			return 'Spesifikasi tidak valid untuk Type Alloy tersebut.';
		}
		return '';
	}

	public function nama_alloy($id_category2)
	{
		$r = $this->db->select('nama')->get_where('ms_inventory_category2', array('id_category2' => $id_category2))->row();
		return $r ? $r->nama : '';
	}

	public function get_spesifikasi($id)
	{
		return $this->db->get_where('ms_spesifikasi', array('id_spesifikasi' => $id, 'deleted' => 0))->row();
	}

	/** Nama Sheet: SPEK-HARDNESS-THICKNESS-WIDTH-LENGTH-ALLOY, bagian kosong dilewati. */
	public function build_nama_sheet($spek, $hardness, $t, $w, $l, $alloy)
	{
		$parts = array($spek, $hardness, $t, $w, $l, $alloy);
		return implode('-', array_filter(array_map('trim', array_map('strval', $parts)), function ($x) {
			return $x !== '';
		}));
	}

	/** Berat/Sheet (kg) = T x W x L x berat jenis / 1.000.000. */
	public function berat_sheet($t, $w, $l, $bj)
	{
		return round((float) $t * (float) $w * (float) $l * (float) $bj / 1000000, 2);
	}

	/** Nilai dimensi material: array nm_dimensi => nilai. */
	public function dim_map($id)
	{
		$map = array();
		foreach ($this->dimensy($id) as $d) {
			$map[strtoupper($d->nm_dimensi)] = $d->nilai_dimensi;
		}
		return $map;
	}

	public function dimensi_sheet()
	{
		return $this->db->where('id_bentuk', self::BENTUK_SHEET)->where('deleted', 0)->order_by('id_dimensi', 'ASC')->get('ms_dimensi')->result();
	}

	/** Pasangan ekuivalen milik satu material: ['manual' => [ids], 'exclude' => [ids]]. */
	public function eq_saved($id)
	{
		$out = array('manual' => array(), 'exclude' => array());
		if ($id === '') {
			return $out;
		}
		$rows = $this->db->query(
			"SELECT id_category3_a a, id_category3_b b, tipe FROM ms_material_ekuivalen WHERE id_category3_a = ? OR id_category3_b = ?",
			array($id, $id)
		)->result();
		foreach ($rows as $r) {
			$other = $r->a === $id ? $r->b : $r->a;
			if (isset($out[$r->tipe])) {
				$out[$r->tipe][] = $other;
			}
		}
		return $out;
	}

	/** Ganti seluruh pasangan milik $id dengan daftar baru. Dipanggil di dalam transaksi pemanggil. */
	public function eq_save($id, $manual, $exclude, $user)
	{
		$this->db->query("DELETE FROM ms_material_ekuivalen WHERE id_category3_a = ? OR id_category3_b = ?", array($id, $id));
		$now = date('Y-m-d H:i:s');
		foreach (array('manual' => $manual, 'exclude' => $exclude) as $tipe => $list) {
			foreach (array_unique($list) as $o) {
				if ($o === '' || $o === $id) {
					continue;
				}
				$pair = strcmp($id, $o) < 0 ? array($id, $o) : array($o, $id);
				$this->db->query(
					"INSERT IGNORE INTO ms_material_ekuivalen (id_category3_a, id_category3_b, tipe, created_by, created_on) VALUES (?,?,?,?,?)",
					array($pair[0], $pair[1], $tipe, $user, $now)
				);
			}
		}
	}

	/** Hanya id yang benar-benar ada (non-deleted), tanpa $self. */
	public function filter_ids($ids, $self = '')
	{
		$ids = array_values(array_unique(array_filter(array_map('strval', (array) $ids))));
		if (!$ids) {
			return array();
		}
		$rows = $this->db->select('id_category3')->where('deleted', 0)->where_in('id_category3', $ids)->get('ms_inventory_category3')->result();
		$ok = array();
		foreach ($rows as $r) {
			if ($r->id_category3 !== $self) {
				$ok[] = $r->id_category3;
			}
		}
		return $ok;
	}

	private function same_thick($a, $b)
	{
		if ($a === '' || $a === null || $b === '' || $b === null || !is_numeric($a) || !is_numeric($b)) {
			return false;
		}
		return round((float) $a, 2) === round((float) $b, 2);
	}

	/** Semua material (kolom ringan) berkunci id_category3, dalam urutan id. */
	private function all_material()
	{
		$rows = $this->db->query(
			"SELECT m.id_category3, m.nama, m.spek, m.hardness, m.thickness, m.maker, m.aktif, m.id_bentuk,
					m.id_category2, m.id_spesifikasi, b.nm_bentuk
			 FROM ms_inventory_category3 m
			 LEFT JOIN ms_bentuk b ON b.id_bentuk = m.id_bentuk
			 WHERE m.deleted = 0 ORDER BY m.id_category3"
		)->result();
		$out = array();
		foreach ($rows as $r) {
			$out[$r->id_category3] = $r;
		}
		return $out;
	}

	/**
	 * Material Ekuivalen. Otomatis: (1) spesifikasi sama, (2) thickness sama.
	 * Sheet juga mendapat Roll; Roll tidak mendapat Sheet. Manual dua arah dan berantai.
	 * @return array ['sp' => row|null, 'list' => [row + ->rel], 'ids' => [id]]
	 */
	public function eq_calc($self, $id_spek, $thickness, $is_sheet, $manual, $exclude)
	{
		$sp  = $id_spek !== '' ? $this->get_spesifikasi($id_spek) : null;
		$all = $this->all_material();
		$manual  = array_flip((array) $manual);
		$exclude = array_flip((array) $exclude);

		// Graf manual milik material lain (pasangan milik $self diganti oleh $manual dari form)
		$adj = array();
		$pairs = $this->db->query("SELECT id_category3_a a, id_category3_b b FROM ms_material_ekuivalen WHERE tipe = 'manual'")->result();
		foreach ($pairs as $p) {
			if ($p->a === $self || $p->b === $self) {
				continue;
			}
			$adj[$p->a][] = $p->b;
			$adj[$p->b][] = $p->a;
		}

		$direct = array();
		$rel = array();
		foreach ($all as $id => $m) {
			if ($id === $self) {
				continue;
			}
			if (isset($manual[$id])) {
				$direct[] = $id;
				$rel[$id] = 'manual';
				continue;
			}
			if (!$sp || isset($exclude[$id])) {
				continue;
			}
			$spek_ok = $m->id_spesifikasi === $sp->id_spesifikasi
				|| ($m->id_category2 === $sp->id_category2 && strcasecmp(trim((string) $m->spek), $sp->nama) === 0);
			$auto_ok = $is_sheet ? true : $m->id_bentuk !== self::BENTUK_SHEET;
			if ($spek_ok && $auto_ok && $this->same_thick($m->thickness, $thickness)) {
				$direct[] = $id;
				$rel[$id] = ($is_sheet && $m->id_bentuk !== self::BENTUK_SHEET) ? 'roll_sheet' : 'sama';
			}
		}

		$seen = array($self => 1);
		foreach ($direct as $id) {
			$seen[$id] = 1;
		}
		$queue = $direct;
		$order = array();
		while ($queue) {
			$cur = array_shift($queue);
			$order[] = $cur;
			foreach (isset($adj[$cur]) ? $adj[$cur] : array() as $x) {
				if (isset($seen[$x]) || !isset($all[$x])) {
					continue;
				}
				$seen[$x] = 1;
				$rel[$x] = 'berantai';
				$queue[] = $x;
			}
		}

		$list = array();
		foreach ($order as $id) {
			$row = clone $all[$id];
			$row->rel = $rel[$id];
			$list[] = $row;
		}
		return array('sp' => $sp, 'list' => $list, 'ids' => $order);
	}

	/** Pencarian material untuk ditambah manual (maks 30). */
	public function eq_cari($self, $shown, $q)
	{
		$q = strtolower(trim($q));
		$shown = array_flip((array) $shown);
		$out = array();
		$total = 0;
		foreach ($this->all_material() as $id => $m) {
			if ($id === $self || isset($shown[$id])) {
				continue;
			}
			if ($q !== '' && strpos(strtolower(implode(' ', array($m->id_category3, $m->nama, $m->spek, $m->maker))), $q) === false) {
				continue;
			}
			$total++;
			if (count($out) < 30) {
				$out[] = $m;
			}
		}
		return array('list' => $out, 'total' => $total);
	}
}
