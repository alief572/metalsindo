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
	protected $viewPermission 	= 'Spk_produksi.View';
	protected $addPermission  	= 'Spk_produksi.Add';
	protected $managePermission = 'Spk_produksi.Manage';
	protected $deletePermission = 'Spk_produksi.Delete';

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
		$query = $this->db->query("SELECT MAX(id_spkproduksi) as max_id FROM tr_spk_produksi");
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
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_spk_produksi WHERE month(tgl_spk_produksi)='$bulan' and Year(tgl_spk_produksi)='$tahun'");
		$row = $query->row_array();
		$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 0, 3);
		$counter = $max_id1 + 1;
		$idcust = sprintf("%03s", $counter) . "/MP/SPK/" . $romawi . "/" . $tahun;
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
		$query = $this->db->query("SELECT MAX(no_surat) as max_id FROM tr_spk_produksi WHERE Year(tahun)='$thn' AND month(tahun)='$bulan'");
		$row = $query->row_array();
		//$thn = date('T');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, -4);
		$counter = $max_id1 + 1;
		$idcust = "PROD-SPK/" . $tahun . "/" . $romawi . "/" . sprintf("%04s", $counter);
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

	public function get_data_category3()
	{
		$search = "a.deleted='0' and z.status_lanjutan='2'";
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1, d.nama as nama_category2, e.nilai_dimensi as thickness');
		$this->db->from('dt_spkmarketing z');
		$this->db->join('ms_inventory_category3 a', 'z.id_material=a.id_category3');
		$this->db->join('ms_inventory_type b', 'b.id_type=a.id_type');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 =a.id_category1');
		$this->db->join('ms_inventory_category2 d', 'd.id_category2 =a.id_category2');
		$this->db->join('child_inven_dimensi e', 'e.id_category3 =a.id_category3');
		$this->db->group_by('z.id_material');
		$this->db->where($search);

		$query = $this->db->get();

		return $query->result();
	}

	public function get_data_category3_booking()
	{
		$search = "a.deleted='0' and z.status_lanjutan='2'";
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1, d.nama as nama_category2, e.nilai_dimensi as thickness');
		$this->db->from('dt_spkmarketing z');
		$this->db->join('ms_inventory_category3 a', 'z.id_material=a.id_category3');
		$this->db->join('ms_inventory_type b', 'b.id_type=a.id_type');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 =a.id_category1');
		$this->db->join('ms_inventory_category2 d', 'd.id_category2 =a.id_category2');
		$this->db->join('child_inven_dimensi e', 'e.id_category3 =a.id_category3');
		$this->db->where($search);
		$query = $this->db->get();
		return $query->result();
	}

	public function CariSPK()
	{
		$this->db->select('a.*, z.nama');
		$this->db->from('tr_spk_produksi a');
		$this->db->join('ms_inventory_category3 z', 'z.id_category3=a.id_material');
		$this->db->where('a.sts_over', NULL);
		$this->db->order_by('a.id_spkproduksi', DESC);
		$query = $this->db->get();
		return $query->result();
	}

	public function CariSPK_Reguler($id)
	{
		$this->db->select('a.*, z.nama');
		$this->db->from('dt_tr_spk_produksi a');
		$this->db->join('ms_inventory_category3 z', 'z.id_category3=a.id_material');
		$this->db->where('a.sts_over', NULL);
		$this->db->where('a.id_spkproduksi', $id);
		$this->db->order_by('a.id_spkproduksi', DESC);
		$query = $this->db->get();
		return $query->result();
	}

	public function CariSPK2()
	{
		$this->db->select('a.*');
		$this->db->from('tr_spk_produksi a');
		$this->db->where('a.sts_over', '1');
		$this->db->order_by('a.id_spkproduksi', DESC);
		$query = $this->db->get();
		return $query->result();
	}

	public function CariSPK3($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_spk_produksi a');
		$this->db->where('a.sts_over', '1');
		$this->db->where('a.id_spkproduksi', $id);
		$this->db->order_by('a.id_spkproduksi', DESC);
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

	public function get_data_spk_produksi_reguler()
	{
		$draw = $this->input->post('draw');
		$length = $this->input->post('length');
		$start = $this->input->post('start');
		$search = $this->input->post('search');

		$this->db->select('a.*, z.nama');
		$this->db->from('tr_spk_produksi a');
		$this->db->join('ms_inventory_category3 z', 'z.id_category3=a.id_material');
		$this->db->where('a.sts_over', NULL);

		if (!empty($search['value'])) {
			$this->db->like('a.no_surat', $search['value']);
			$this->db->or_like('z.nama', $search['value']);
		}
		$this->db->order_by('a.id_spkproduksi', 'desc');
		$this->db->limit($length, $start);

		$get_data = $this->db->get();

		$this->db->select('a.*, z.nama');
		$this->db->from('tr_spk_produksi a');
		$this->db->join('ms_inventory_category3 z', 'z.id_category3=a.id_material');
		$this->db->where('a.sts_over', NULL);

		if (!empty($search['value'])) {
			$this->db->like('a.no_surat', $search['value']);
			$this->db->or_like('z.nama', $search['value']);
		}
		$this->db->order_by('a.id_spkproduksi', 'desc');

		$get_data_all = $this->db->get();

		$hasil = [];

		$no = (0 + $start);

		foreach ($get_data->result() as $item) :
			$no++;

			$this->db->select('a.*, b.name_customer as name_customer');
			$this->db->from('dt_spk_produksi a');
			$this->db->join('master_customers b', 'b.id_customer = a.idcustomer', 'left');
			$this->db->where('a.id_spkproduksi', $item->id_spkproduksi);
			$this->db->group_by('b.name_customer');
			$get_customers = $this->db->get()->result();

			$list_customer = '';
			foreach ($get_customers as $item_customers) :
				$list_customer .= '- ' . $item_customers->name_customer . '<br>';
			endforeach;

			$status = '';

			if ($item->status_approve == '1') {
				$status = "Menunggu";
			} elseif ($item->status_approve == '2') {
				$status = "Diturunkan";
			} elseif ($item->status_approve == '3') {
				$status = "Selesai Diproduksi";
			}

			$action = '';

			if ($item->status_approve == '1') {
				if (has_permission($this->viewPermission)) {
					$action .= ' <a class="btn btn-primary btn-sm view" href="' . base_url('/spk_produksi/addHeader_view/' . $item->id_spkproduksi . '/view') . '" title="View"><i class="fa fa-eye"></i></a>';
				}

				if (has_permission($this->managePermission)) {
					$action .= '<a class="btn btn-warning btn-sm" href="' . base_url('/spk_produksi/addHeaderproses/' . $item->id_spkproduksi) . '"  title="Add Proses"><i class="fa fa-plus"></i></a>';
				}

				if (has_permission($this->viewPermission)) {
					$action .= ' <a class="btn btn-success btn-sm view" href="' . base_url('/spk_produksi/index2/' . $item->id_spkproduksi) . '" title="Detail"><i class="fa fa-list"></i></a>';
				}
			} else {
				if (has_permission($this->viewPermission)) {
					$action .= ' <a class="btn btn-primary btn-sm view" href="' . base_url('/spk_produksi/addHeader_view/' . $item->id_spkproduksi . '/view') . '" title="View"><i class="fa fa-eye"></i></a>';
				}
			}

			$hasil[] = [
				'no' => $no,
				'no_spk_produksi' => $item->no_surat,
				'customer' => $list_customer,
				'nama_material' => $item->nama,
				'action' => $action
			];
		endforeach;

		echo json_encode([
			'draw' => $draw,
			'recordsTotal' => $get_data_all->num_rows(),
			'recordsFiltered' => $get_data_all->num_rows(),
			'data' => $hasil
		]);
	}

	public function get_data_spk_produksi_booking() {
		$draw = $this->input->post('draw');
		$length = $this->input->post('length');
		$start = $this->input->post('start');
		$search = $this->input->post('search');

		$this->db->select('a.*, z.nama');
		$this->db->from('tr_spk_produksi a');
		$this->db->join('ms_inventory_category3 z', 'z.id_category3 = a.id_material');
		$this->db->where('a.sts_over', '1');

		if (!empty($search['value'])) {
			$this->db->like('a.no_surat', $search['value']);
			$this->db->or_like('z.nama', $search['value']);
		}
		$this->db->order_by('a.id_spkproduksi', 'desc');
		$this->db->limit($length, $start);

		$get_data = $this->db->get();

		$this->db->select('a.*, z.nama');
		$this->db->from('tr_spk_produksi a');
		$this->db->join('ms_inventory_category3 z', 'z.id_category3 = a.id_material');
		$this->db->where('a.sts_over', '1');

		if (!empty($search['value'])) {
			$this->db->like('a.no_surat', $search['value']);
			$this->db->or_like('z.nama', $search['value']);
		}
		$this->db->order_by('a.id_spkproduksi', 'desc');

		$get_data_all = $this->db->get();

		$hasil = [];

		$no = (0 + $start);

		foreach ($get_data->result() as $item) :
			$no++;

			$this->db->select('a.*, b.name_customer as name_customer');
			$this->db->from('dt_spk_produksi a');
			$this->db->join('master_customers b', 'b.id_customer = a.idcustomer', 'left');
			$this->db->where('a.id_spkproduksi', $item->id_spkproduksi);
			$get_customers = $this->db->get()->result();

			$list_customer = '';
			foreach ($get_customers as $item_customers) :
				$list_customer .= '- ' . $item_customers->name_customer . '<br>';
			endforeach;

			$status = '';

			if ($item->status_approve == '1') {
				$status = "Menunggu";
			} elseif ($item->status_approve == '2') {
				$status = "Diturunkan";
			} elseif ($item->status_approve == '3') {
				$status = "Selesai Diproduksi";
			}

			$action = '';

			if ($item->status_approve == '1') {
				if (has_permission($this->viewPermission)) {
					$action .= ' <a class="btn btn-primary btn-sm view" href="' . base_url('/spk_produksi/addHeader_view/' . $item->id_spkproduksi . '/view') . '" title="View"><i class="fa fa-eye"></i></a>';
				}

				if (has_permission($this->managePermission)) {
					$action .= '<a class="btn btn-warning btn-sm" href="' . base_url('/spk_produksi/addHeaderproses/' . $item->id_spkproduksi) . '"  title="Add Proses"><i class="fa fa-plus"></i></a>';
				}

				if (has_permission($this->viewPermission)) {
					$action .= ' <a class="btn btn-success btn-sm view" href="' . base_url('/spk_produksi/index2/' . $item->id_spkproduksi) . '" title="Detail"><i class="fa fa-list"></i></a>';
				}
			} else {
				if (has_permission($this->viewPermission)) {
					$action .= ' <a class="btn btn-primary btn-sm view" href="' . base_url('/spk_produksi/addHeader_view/' . $item->id_spkproduksi . '/view') . '" title="View"><i class="fa fa-eye"></i></a>';
				}
			}

			$hasil[] = [
				'no' => $no,
				'no_spk_produksi' => $item->no_surat,
				'customer' => $list_customer,
				'nama_material' => $item->nama,
				'status' => $status,
				'alasan' => $item->reason,
				'action' => $action
			];
		endforeach;

		echo json_encode([
			'draw' => $draw,
			'recordsTotal' => $get_data_all->num_rows(),
			'recordsFiltered' => $get_data_all->num_rows(),
			'data' => $hasil
		]);
	}
}
