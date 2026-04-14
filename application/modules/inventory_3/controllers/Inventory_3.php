<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
 * @author Ichsan
 * @copyright Copyright (c) 2019, Ichsan
 *
 * This is controller for Master Supplier
 */

class Inventory_3 extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Level_3.View';
	protected $addPermission  	= 'Level_3.Add';
	protected $managePermission = 'Level_3.Manage';
	protected $deletePermission = 'Level_3.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Inventory_3/Inventory_3_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$this->template->page_icon('fa fa-users');
		$this->template->title('Inventory');
		$this->template->render('index');
	}

	public function get_serverside()
	{
		$this->auth->restrict($this->viewPermission);

		$draw     = intval($this->input->post('draw'));
		$start    = intval($this->input->post('start'));
		$length   = intval($this->input->post('length'));
		$search   = $this->input->post('search')['value'];
		$orderCol = intval($this->input->post('order')[0]['column']);
		$orderDir = $this->input->post('order')[0]['dir'] === 'asc' ? 'ASC' : 'DESC';

		$columns = ['a.id_category2', 'b.nama', 'c.nama', 'a.nama', 'a.kode_coretax', 'a.aktif'];

		// Total records
		$this->db->select('a.id_category2');
		$this->db->from('ms_inventory_category2 a');
		$this->db->join('ms_inventory_type b', 'b.id_type = a.id_type');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 = a.id_category1');
		$this->db->where('a.deleted', 0);
		$totalRecords = $this->db->count_all_results();

		// Filtered records
		$this->db->select('a.id_category2, b.nama as nama_type, c.nama as nama_category1, a.nama, a.kode_coretax, a.aktif');
		$this->db->from('ms_inventory_category2 a');
		$this->db->join('ms_inventory_type b', 'b.id_type = a.id_type');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 = a.id_category1');
		$this->db->where('a.deleted', 0);
		if ($search) {
			$this->db->group_start();
			$this->db->like('a.id_category2', $search);
			$this->db->or_like('b.nama', $search);
			$this->db->or_like('c.nama', $search);
			$this->db->or_like('a.nama', $search);
			$this->db->or_like('a.kode_coretax', $search);
			$this->db->or_like('a.aktif', $search);
			$this->db->group_end();
		}
		$filteredRecords = $this->db->count_all_results('', false);

		$orderColName = isset($columns[$orderCol]) ? $columns[$orderCol] : 'a.id_category2';
		$this->db->order_by($orderColName, $orderDir);
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result();

		$data = [];
		$no   = $start + 1;
		foreach ($rows as $row) {
			$action = '';
			if (has_permission($this->viewPermission)) {
				$action .= '<a class="btn btn-primary btn-sm view" href="javascript:void(0)" title="View" data-id_inventory3="' . $row->id_category2 . '"><i class="fa fa-eye"></i></a> ';
			}
			if (has_permission($this->managePermission)) {
				$action .= '<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" data-id_inventory3="' . $row->id_category2 . '"><i class="fa fa-edit"></i></a> ';
			}
			if (has_permission($this->deletePermission)) {
				$action .= '<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id_inventory3="' . $row->id_category2 . '"><i class="fa fa-trash"></i></a>';
			}

			$status = $row->aktif == 'aktif'
				? '<label class="label label-success">Aktif</label>'
				: '<label class="label label-danger">Non Aktif</label>';

			$data[] = [
				$no++,
				$row->id_category2,
				$row->nama_type,
				$row->nama_category1,
				$row->nama,
				$row->kode_coretax,
				$status,
				$action
			];
		}

		echo json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $totalRecords,
			'recordsFiltered' => $filteredRecords,
			'data'            => $data
		]);
	}
	public function editInventory($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-edit');
		$inven = $this->db->get_where('ms_inventory_category2', array('id_category2' => $id))->result();
		$lvl1 = $this->Inventory_3_model->get_data('ms_inventory_type');
		$lvl2 = $this->Inventory_3_model->get_data('ms_inventory_category1');
		$data = [
			'inven' => $inven,
			'lvl1' => $lvl1,
			'lvl2' => $lvl2
		];
		$this->template->set('results', $data);
		$this->template->title('Inventory');
		$this->template->render('edit_inventory');
	}
	public function viewInventory()
	{
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$cust 	= $this->db->get_where('ms_inventory_category2', array('id_category2' => $id))->row_array();
		$this->template->set('result', $cust);
		$this->template->render('view_inventory');
	}
	public function saveEditInventory()
	{
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		// print_r($post);
		// exit();
		$this->db->trans_begin();
		$data = [
			'id_type'		    => $post['inventory_1'],
			'id_category1'		=> $post['inventory_2'],
			'nama'      		=> $post['nm_inventory'],
			'kode_coretax'		=> $post['kode_coretax'],
			'aktif'				=> $post['status'],
			'modified_on'		=> date('Y-m-d H:i:s'),
			'modified_by'		=> $this->auth->user_id()
		];

		$this->db->where('id_category2', $post['id_inventory'])->update("ms_inventory_category2", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function addInventory()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$inventory_1 = $this->Inventory_3_model->get_data('ms_inventory_type');
		$inventory_2 = $this->Inventory_3_model->get_data('ms_inventory_category1');
		$data = [
			'inventory_1' => $inventory_1,
			'inventory_2' => $inventory_2
		];
		$this->template->set('results', $data);
		$this->template->title('Add Inventory');
		$this->template->render('add_inventory');
	}
	public function deleteInventory()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_category2', $id)->update("ms_inventory_category2", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'status'	=> 1
			);
		}
		echo json_encode($status);
	}

	function get_inven2()
	{
		$inventory_1 = $_GET['inventory_1'];
		$data = $this->Inventory_3_model->level_2($inventory_1);

		// print_r($data);
		// exit();
		echo "<select id='inventory_2' name='inventory_2' class='form-control input-sm select2'>";
		echo "<option value=''>--Pilih Category--</option>";
		foreach ($data as $key => $st) :
			echo "<option value='$st->id_category1' set_select('inventory_2', $st->id_category1, isset($data->id_category1) && $data->id_category1 == $st->id_category1)>$st->nama
                    </option>";
		endforeach;
		echo "</select>";
	}

	public function saveNewinventory()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Inventory_3_model->generate_id();
		$this->db->trans_begin();
		$data = [
			'id_category2'		=> $code,
			'id_type'		    => $post['inventory_1'],
			'id_category1'		=> $post['inventory_2'],
			'nama'      		=> $post['nm_inventory'],
			'kode_coretax'		=> $post['kode_coretax'],
			'aktif'				=> 'aktif',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id(),
			'deleted'			=> '0'
		];

		$insert = $this->db->insert("ms_inventory_category2", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
}
