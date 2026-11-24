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

class Inventory_4 extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Level_4.View';
	protected $addPermission  	= 'Level_4.Add';
	protected $managePermission = 'Level_4.Manage';
	protected $deletePermission = 'Level_4.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Inventory_4/Inventory_4_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}
	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		$data = $this->Inventory_4_model->get_data('ms_bentuk', 'deleted', $deleted);
		$this->template->set('results', $data);
		$this->template->title('Material');
		$this->template->render('index');
	}
	public function detail()
	{
		$id_bentuk = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		$data = $this->Inventory_4_model->get_data_category3($id_bentuk);
		$this->template->set('results', $data);
		$this->template->title('Material');
		$this->template->render('list');
	}
	public function editInventory($id)
	{
		$this->auth->restrict($this->viewPermission);
		if ($this->is_sheet($id)) {return $this->sheet_form('edit', $id);}
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$deleted = '0';
		$inven = $this->Inventory_4_model->getedit($id);
		$all   = $this->db->query("SELECT id_category2 FROM ms_inventory_category3 WHERE id_category3='$id'")->row();
		$alloy = $this->db->query("SELECT nama FROM ms_inventory_category2 WHERE id_category2='$all->id_category2'")->row();
		$komposisiold = $this->Inventory_4_model->get_data('child_inven_compotition', 'id_category3', $id);
		$komposisi = $this->Inventory_4_model->kompos($id);
		$dimensiold = $this->Inventory_4_model->get_data('child_inven_dimensi', 'id_category3', $id);
		$dimensi = $this->Inventory_4_model->dimensy($id);
		$supl = $this->Inventory_4_model->supl($id);
		$inventory_1 = $this->Inventory_4_model->get_data('ms_inventory_type', 'deleted', $deleted);
		$inventory_2 = $this->Inventory_4_model->get_data('ms_inventory_category1', 'deleted', $deleted);
		$inventory_3 = $this->Inventory_4_model->get_data('ms_inventory_category2', 'deleted', $deleted);
		$maker = $this->Inventory_4_model->get_data('negara');
		$id_bentuk = $this->Inventory_4_model->get_data('ms_bentuk');
		$id_supplier = $this->Inventory_4_model->get_data('master_supplier');
		$id_surface = $this->Inventory_4_model->get_data('ms_surface');
		$dt_suplier = $this->Inventory_4_model->get_data('child_inven_suplier', 'id_category3', $id);
		$row0        = $inven[0];
		$is_roll     = ($row0->id_bentuk === 'B2000001');
		$eq          = $is_roll ? $this->Inventory_4_model->eq_saved($id) : array('manual' => array(), 'exclude' => array());

		$data = [
			'inventory_1' => $inventory_1,
			'inventory_2' => $inventory_2,
			'inventory_3' => $inventory_3,
			'komposisi' => $komposisi,
			'dimensi' => $dimensi,
			'id_bentuk' => $id_bentuk,
			'inven' => $inven,
			'maker' => $maker,
			'supl' => $supl,
			'alloy' => $alloy->nama,
			'id_surface' => $id_surface,
			'id_supplier' => $id_supplier,
			'dt_suplier' => $dt_suplier,
			'eq'             => $eq,
			'id_spesifikasi' => (string)($row0->id_spesifikasi ?? ''),
			'thickness'      => (string)($row0->thickness ?? ''),
		];
		$this->template->set('results', $data);
		$this->template->title('Add Inventory');
		$this->template->render('edit_inventory');
	}
	public function copyInventory($id)
	{
		$this->auth->restrict($this->viewPermission);
		if ($this->is_sheet($id)) {return $this->sheet_form('copy', $id);}
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$deleted = '0';
		$inven = $this->Inventory_4_model->getedit($id);
		$komposisiold = $this->Inventory_4_model->get_data('child_inven_compotition', 'id_category3', $id);
		$komposisi = $this->Inventory_4_model->kompos($id);
		$dimensiold = $this->Inventory_4_model->get_data('child_inven_dimensi', 'id_category3', $id);
		$dimensi = $this->Inventory_4_model->dimensy($id);
		$supl = $this->Inventory_4_model->supl($id);
		$inventory_1 = $this->Inventory_4_model->get_data('ms_inventory_type', 'deleted', $deleted);
		$inventory_2 = $this->Inventory_4_model->get_data('ms_inventory_category1', 'deleted', $deleted);
		$inventory_3 = $this->Inventory_4_model->get_data('ms_inventory_category2', 'deleted', $deleted);
		$maker = $this->Inventory_4_model->get_data('negara');
		$id_bentuk = $this->Inventory_4_model->get_data('ms_bentuk');
		$id_supplier = $this->Inventory_4_model->get_data('master_supplier');
		$id_surface = $this->Inventory_4_model->get_data('ms_surface');
		$dt_suplier = $this->Inventory_4_model->get_data('child_inven_suplier', 'id_category3', $id);
		$data = [
			'inventory_1' => $inventory_1,
			'inventory_2' => $inventory_2,
			'inventory_3' => $inventory_3,
			'komposisi' => $komposisi,
			'dimensi' => $dimensi,
			'id_bentuk' => $id_bentuk,
			'inven' => $inven,
			'maker' => $maker,
			'supl' => $supl,
			'id_surface' => $id_surface,
			'id_supplier' => $id_supplier,
			'dt_suplier' => $dt_suplier
		];
		$this->template->set('results', $data);
		$this->template->title('Add Inventory');
		$this->template->render('copy_inventory');
	}
	public function viewInventory($id)
	{
		$this->auth->restrict($this->viewPermission);
		if ($this->is_sheet($id)) {return $this->sheet_view($id);}
		if ($this->is_roll($id)) {return $this->roll_view($id);}
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$deleted = '0';
		$inven = $this->Inventory_4_model->getedit($id);
		$komposisiold = $this->Inventory_4_model->get_data('child_inven_compotition', 'id_category3', $id);
		$komposisi = $this->Inventory_4_model->kompos($id);
		$dimensiold = $this->Inventory_4_model->get_data('child_inven_dimensi', 'id_category3', $id);
		$dimensi = $this->Inventory_4_model->dimensy($id);
		$supl = $this->Inventory_4_model->supl($id);
		$inventory_1 = $this->Inventory_4_model->get_data('ms_inventory_type', 'deleted', $deleted);
		$inventory_2 = $this->Inventory_4_model->get_data('ms_inventory_category1', 'deleted', $deleted);
		$inventory_3 = $this->Inventory_4_model->get_data('ms_inventory_category2', 'deleted', $deleted);
		$maker = $this->Inventory_4_model->get_data('negara');
		$id_bentuk = $this->Inventory_4_model->get_data('ms_bentuk');
		$id_supplier = $this->Inventory_4_model->get_data('master_supplier');
		$id_surface = $this->Inventory_4_model->get_data('ms_surface');
		$dt_suplier = $this->Inventory_4_model->get_data('child_inven_suplier', 'id_category3', $id);
		$id_bentuks = $this->Inventory_4_model->get_data('ms_bentuk', 'id_bentuk', $inventory_3[0]->id_bentuk);
		$data = [
			'inventory_1' => $inventory_1,
			'inventory_2' => $inventory_2,
			'inventory_3' => $inventory_3,
			'komposisi' => $komposisi,
			'dimensi' => $dimensi,
			'id_bentuk' => $id_bentuk,
			'id_bentuks' => $id_bentuks,
			'inven' => $inven,
			'maker' => $maker,
			'supl' => $supl,
			'id_surface' => $id_surface,
			'id_supplier' => $id_supplier,
			'dt_suplier' => $dt_suplier
		];
		$this->template->set('results', $data);
		$this->template->title('Add Inventory');
		$this->template->render('view_inventory');
	}
	public function viewBentuk($id)
	{
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$bentuk = $this->db->get_where('ms_bentuk', array('id_bentuk' => $id))->result();
		$dimensi = $this->Bentuk_model->getDimensi($id);
		$data = [
			'bentuk' => $bentuk,
			'dimensi' => $dimensi,
		];
		$this->template->set('results', $data);
		$this->template->render('view_bentuk');
	}


	public function addInventory($id)
	{
		if ($id === 'B2000002') {
			$this->auth->restrict($this->viewPermission);
			return $this->sheet_form('add', '');
		}
		$id_data 	= $this->input->post('id');
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$deleted = '0';
		$inventory_1 = $this->Inventory_4_model->get_data('ms_inventory_type', 'deleted', $deleted);
		$maker = $this->Inventory_4_model->get_data('negara');
		$dimensi = $this->Inventory_4_model->get_data('ms_dimensi', 'id_bentuk', $id_data);
		$id_bentuk = $this->Inventory_4_model->get_data('ms_bentuk', 'id_bentuk', $id_data);
		$id_supplier = $this->Inventory_4_model->get_data('master_supplier', 'deleted', $deleted);
		$id_surface = $this->Inventory_4_model->get_data('ms_surface');
		$data = [
			'inventory_1' => $inventory_1,
			'id_bentuk' => $id_bentuk,
			'dimensi' => $dimensi,
			'maker' => $maker,
			'id_surface' => $id_surface,
			'id_supplier' => $id_supplier,
			'eq'             => array('manual' => array(), 'exclude' => array()),
			'id_spesifikasi' => '',
			'thickness'      => '',
			'is_roll'        => ($id === 'B2000001'),
		];
		$this->template->set('results', $data);
		$this->template->title('Add Inventory');
		$this->template->render('add_inventory');
	}

	public function delDetail()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		// print_r($id);
		// exit();
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_dimensi', $id)->update("ms_dimensi", $data);

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

	public function deleteInventory()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_category3', $id)->update("ms_inventory_category3", $data);

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
		$data = $this->Inventory_4_model->level_2($inventory_1);
		$hasil = "<select id='inventory_2' name='hd1[1][inventory_2]' class='form-control input-sm select' onchange='get_inv3()' required>";
		$hasil .= "<option>--Pilih--</option>";
		foreach ($data as $key => $st) :
			$selected = '';
			if ($st->id_category1 == $data->id_category1) {
				$selected = 'selected';
			}
			$hasil .= "<option value='" . $st->id_category1 . "' " . $selected . ">" . $st->nama . "</option>";
		endforeach;
		$hasil .= "</select>";

		print_r($hasil);
		exit;
	}

	function get_namainven2()
	{
		$inventory_1 = $_GET['inventory_1'];

		$data = $this->db->query("SELECT * from ms_inventory_category2 WHERE id_category2='$inventory_1'")->row();

		echo "$data->nama";
	}
	function get_inven3()
	{
		$inventory_2 = $_GET['inventory_2'];
		$data = $this->Inventory_4_model->level_3($inventory_2);

		// print_r($data);
		// exit();
		echo "<select id='inventory_3' name='hd1[1][inventory_3]' class='form-control input-sm select2'>";
		echo "<option>--Pilih--</option>";
		foreach ($data as $key => $st) :
			echo "<option value='$st->id_category2' set_select('inventory_3', $st->id_category2, isset($data->id_category2) && $data->id_category2 == $st->id_category2)>$st->nama
                    </option>";
		endforeach;
		echo "</select>";
	}
	public function saveNewInventory()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$session = $this->session->userdata('app_session');
		$code = $this->Inventory_4_model->generate_id();
		$this->db->trans_begin();
		$id_bentuk = $_POST['hd1']['1']['id_bentuk'];
		$numb1 = 0;
		$weight_kg = 0;
		if (isset($post['weight_kg'])) {
			$weight_kg = $post['weight_kg'];
		}

		//$head = $_POST['hd1'];
		foreach ($_POST['hd1'] as $h1) {
			$numb1++;
			$id_category2	= $h1['inventory_3'];
			$carialloy	= $this->db->query("SELECT * FROM ms_inventory_category2 WHERE id_category2 = '$id_category2' ")->result();
			$alloy = $carialloy[0]->nama;
			$id_bentuk	= $h1['id_bentuk'];
			$id_supplier  = $h1['id_bentuk'];
			$caribentuk	= $this->db->query("SELECT * FROM ms_bentuk WHERE id_bentuk = '$id_bentuk' ")->result();
			$bentuk = $caribentuk[0]->nm_bentuk;



			foreach ($_POST['data1'] as $sp) {
				$id_supplier = $sp['id_supplier'];
			}


			$carisupplier	= $this->db->query("SELECT * FROM master_supplier WHERE id_suplier = '$id_supplier' ")->result();
			$supplier		= $carisupplier[0]->name_suplier;



			$header1 =  array(
				'id_category3'	 		=> $code,
				'id_type'		        => $h1['inventory_1'],
				'id_category1'		    => $h1['inventory_2'],
				'id_category2'		    => $h1['inventory_3'],
				'spek'		        	=> $h1['nm_inventory'],
				'maker'		        	=> $supplier,
				'density'		        => $h1['density'],
				'hardness'		        => $h1['hardness'],
				'id_bentuk'		        => $h1['id_bentuk'],
				'id_surface'		    => $h1['id_surface'],
				'mountly_forecast'		=> $h1['mountly_forecast'],
				'safety_stock'		    => $h1['safety_stock'],
				'order_point'		    => $h1['order_point'],
				'maksimum'		    	=> $h1['maksimum'],
				'aktif'					=> 'aktif',
				'created_on'			=> date('Y-m-d H:i:s'),
				'created_by'			=> $this->auth->user_id(),
				'deleted'				=> '0',
				'thickness'				=> $_POST['dimens']['1']['nilai_dimensi'],
				'nama'		        	=> $h1['nama'],
				'alloy'		        	=> $alloy,
				'negara'		        => $h1['maker'],
				'total_weight'				=> $weight_kg
			);
			//Add Data
			$this->db->insert('ms_inventory_category3', $header1);
			$bookp =  array(
				'id_category3'	 		=> $code,
				'bentuk'		        => $bentuk,
				'alloy'		        	=> $alloy,
				'material'		        => $h1['nama'],
				'hardness'		        => $h1['hardness'],
				'thickness'				=> $_POST['dimens']['1']['nilai_dimensi']

			);
			//Add Data
			$this->db->insert('ms_bookprice_material', $bookp);
		}

		if (empty($_POST['data1'])) {
		} else {
			$numb2 = 0;
			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data1 =  array(
					'id_category3' => $code,
					'id_suplier' => $d1['id_supplier'],
					'lead' => $d1['lead'],
					'minimum' => $d1['minimum'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_suplier', $data1);
			}
		}
		if (empty($_POST['compo'])) {
		} else {
			$numb3 = 0;
			foreach ($_POST['compo'] as $c1) {
				$numb3++;
				$comp =  array(
					'id_category3' => $code,
					'id_compotition' => $c1['id_compotition'],
					'nilai_compotition' => $c1['jumlah_kandungan'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_compotition', $comp);
			}
		}
		if (empty($_POST['dimens'])) {
		} else {
			$numb4 = 0;
			foreach ($_POST['dimens'] as $dm) {
				$numb4++;
				$dms =  array(
					'id_category3' => $code,
					'id_dimensi' => $dm['id_dimensi'],
					'nilai_dimensi' => $dm['nilai_dimensi'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_dimensi', $dms);
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $id_bentuk,
				'status'	=> 0
			);
		} else {
			$M = $this->Inventory_4_model;
			$manual = $M->filter_ids($this->post_ids('eq_manual'), $code);
			$excl   = $M->filter_ids($this->post_ids('eq_excl'),   $code);
			if (!empty($manual) || !empty($excl)) {
				$M->eq_save($code, $manual, $excl, $this->auth->user_id());
			}
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $id_bentuk,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	public function saveEditInventory()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$session = $this->session->userdata('app_session');
		$code = $this->Inventory_4_model->generate_id();
		$this->db->trans_begin();
		$id = $_POST['hd1']['1']['id_category3'];
		$id_bentuk = $_POST['hd1']['1']['id_bentuk'];
		$weight_kg = 0;
		if (isset($post['weight_kg'])) {
			$weight_kg = $post['weight_kg'];
		}
		$numb1 = 0;
		foreach ($_POST['hd1'] as $h1) {
			$numb1++;
			$id_category2	= $h1['inventory_3'];
			$carialloy	= $this->db->query("SELECT * FROM ms_inventory_category2 WHERE id_category2 = '$id_category2' ")->result();

			$alloy = $carialloy[0]->nama;
			$id_bentuk	= $h1['id_bentuk'];
			$caribentuk	= $this->db->query("SELECT * FROM ms_bentuk WHERE id_bentuk = '$id_bentuk' ")->result();
			$bentuk = $caribentuk[0]->nm_bentuk;
			$header1 =  array(
				'id_type'		        => $h1['inventory_1'],
				'id_category1'		    => $h1['inventory_2'],
				'id_category2'		    => $h1['inventory_3'],
				'spek'		        	=> $h1['nm_inventory'],
				'maker'		        	=> $h1['maker'],
				'negara'		        => $h1['maker'],
				'density'		        => $h1['density'],
				'hardness'		        => $h1['hardness'],
				'id_bentuk'		        => $h1['id_bentuk'],
				'id_surface'		    => $h1['id_surface'],
				'mountly_forecast'		=> $h1['mountly_forecast'],
				'safety_stock'		    => $h1['safety_stock'],
				'order_point'		    => $h1['order_point'],
				'maksimum'		    	=> $h1['maksimum'],
				'aktif'					=> 'aktif',
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'deleted'			=> '0',
				'thickness'				=> $_POST['dimens']['1']['nilai_dimensi'],
				'nama'		        	=> $h1['nama'],
				'alloy'		        	=> $alloy,
				'total_weight'				=> $weight_kg
			);
			$this->db->where('id_category3', $id)->update("ms_inventory_category3", $header1);
			$bookp =  array(
				'alloy'		        	=> $alloy,
				'bentuk'		        	=> $bentuk,
				'material'		        => $h1['nm_inventory'],
				'hardness'		        => $h1['hardness'],
				'thickness'				=> $_POST['dimens']['1']['nilai_dimensi']

			);
			//Add Data
			$this->db->where('id_category3', $id)->update("ms_bookprice_material", $bookp);
		}

		if (empty($_POST['data1'])) {
		} else {
			$this->db->delete('child_inven_suplier', array('id_category3' => $id));
			$numb2 = 0;

			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data1 =  array(
					'id_category3' => $id,
					'id_suplier' => $d1['id_supplier'],
					'lead' => $d1['lead'],
					'minimum' => $d1['minimum'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_suplier', $data1);
			}
		}

		if (empty($_POST['compo'])) {
		} else {
			$this->db->delete('child_inven_compotition', array('id_category3' => $id));
			$numb3 = 0;
			foreach ($_POST['compo'] as $c1) {
				$numb3++;
				$comp =  array(
					'id_category3' => $id,
					'id_compotition' => $c1['id_compotition'],
					'nilai_compotition' => $c1['jumlah_kandungan'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_compotition', $comp);
			}
		}

		if (empty($_POST['dimens'])) {
		} else {
			$this->db->delete('child_inven_dimensi', array('id_category3' => $id));
			$numb4 = 0;
			foreach ($_POST['dimens'] as $dm) {
				$numb4++;
				$dms =  array(
					'id_category3' => $id,
					'id_dimensi' => $dm['id_dimensi'],
					'nilai_dimensi' => $dm['nilai_dimensi'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_dimensi', $dms);
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $id_bentuk,
				'status'	=> 0
			);
		} else {
			$M = $this->Inventory_4_model;
			$manual = $M->filter_ids($this->post_ids('eq_manual'), $id);
			$excl   = $M->filter_ids($this->post_ids('eq_excl'),   $id);
			if (!empty($manual) || !empty($excl)) {
				$M->eq_save($id, $manual, $excl, $this->auth->user_id());
			}
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $id_bentuk,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	function get_compotition_new()
	{
		$inventory_2 = $_GET['inventory_2'];
		$comp = $this->Inventory_4_model->compotition($inventory_2);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($comp as $key => $cmp): $numb++;
			echo "<tr>
					  <td hidden align='left'>
					  <input type='text' name='compo[$numb][id_compotition]' readonly class='form-control'  value='$cmp->id_compotition'>
					  </td>
					  <td align='left'>
					  $cmp->name_compotition
					  </td>
					  <td align='left'>
					  <input type='text' name='compo[$numb][jumlah_kandungan]' class='form-control'>
					  </td>
					  <td align='left'>%</td>
                    </tr>";
		endforeach;
		echo "</select>";
	}
	function get_dimensi()
	{
		$id_bentuk = $_GET['id_bentuk'];
		$dim = $this->Inventory_4_model->bentuk($id_bentuk);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($dim as $key => $ensi): $numb++;
			echo "<tr>
					  <td align='left' hidden>
					  <input type='text' name='dimens[$numb][id_dimensi]' readonly class='form-control'  value='$ensi->id_dimensi'>
					  </td>
					  <td align='left'>
					  $ensi->nm_dimensi
					  </td>
					  <td align='left'>
					  <input type='text' name='dimens[$numb][nilai_dimensi]' class='form-control'>
					  </td>
                    </tr>";
		endforeach;
		echo "</select>";
	}
	function get_compotition_old()
	{
		$inventory_2 = $_GET['inventory_2'];
		$comp = $this->Inventory_4_model->compotition_edit($inventory_2);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($comp as $key => $cmp): $numb++;
			echo "<tr>
					  <td hidden align='left'>
					  <input type='text' name='compo[$numb][id_compotition]' readonly class='form-control'  value='$cmp->id_compotition'>
					  </td>
					  <td align='left'>
					  $cmp->name_compotition
					  </td>
					  <td align='left'>
					  <input type='text' name='compo[$numb][jumlah_kandungan]' class='form-control'>
					  </td>
					  <td align='left'>%</td>
                    </tr>";
		endforeach;
		echo "</select>";
	}
	function get_dimensi_old()
	{
		$id_bentuk = $_GET['id_bentuk'];
		$dim = $this->Inventory_4_model->bentuk_edit($id_bentuk);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($dim as $key => $ensi): $numb++;
			echo "<tr>
					  <td hidden align='left'>
					  <input type='text' name='dimens[$numb][id_dimensi]' readonly class='form-control'  value='$cmp->id_dimensi'>
					  </td>
					  <td align='left'>
					  $ensi->nm_dimensi
					  </td>
					  <td align='left'>
					  <input type='text' name='dimens[$numb][nilai_dimensi]' class='form-control'>
					  </td>
                    </tr>";
		endforeach;
		echo "</select>";
	}
	public function saveEditInventorylama()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$code = $this->Inventory_4_model->generate_id();
		$this->db->trans_begin();
		$id = $_POST['hd1']['1']['id_category3'];
		$numb1 = 0;
		//$head = $_POST['hd1'];
		foreach ($_POST['hd1'] as $h1) {
			$numb1++;

			$header1 =  array(
				'id_type'		        => $h1['inventory_1'],
				'id_category1'		    => $h1['inventory_2'],
				'id_category2'		    => $h1['inventory_3'],
				'nama'		        	=> $h1['nm_inventory'],
				'maker'		        	=> $h1['maker'],
				'density'		        => $h1['density'],
				'hardness'		        => $h1['hardness'],
				'id_bentuk'		        => $h1['id_bentuk'],
				'id_surface'		    => $h1['id_surface'],
				'mountly_forecast'		=> $h1['mountly_forecast'],
				'safety_stock'		    => $h1['safety_stock'],
				'order_point'		    => $h1['order_point'],
				'maksimum'		    	=> $h1['maksimum'],
				'aktif'					=> 'aktif',
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'deleted'			=> '0'
			);
			//Add Data
			$this->db->where('id_category3', $id)->update("ms_inventory_category3", $data);
		}
		$this->db->delete('child_inven_suplier', array('id_category3' => $id));
		if (empty($_POST['data1'])) {
		} else {
			$numb2 = 0;
			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data1 =  array(
					'id_category3' => $code,
					'id_suplier' => $d1['id_supplier'],
					'lead' => $d1['lead'],
					'minimum' => $d1['minimum'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_suplier', $data1);
			}
		}
		if (empty($_POST['compo'])) {
		} else {
			$numb3 = 0;
			foreach ($_POST['compo'] as $c1) {
				$numb3++;
				$comp =  array(
					'id_category3' => $code,
					'id_compotition' => $c1['id_compotition'],
					'nilai_compotition' => $c1['jumlah_kandungan'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_compotition', $comp);
			}
		}
		if (empty($_POST['dimens'])) {
		} else {
			$numb4 = 0;
			foreach ($_POST['dimens'] as $dm) {
				$numb4++;
				$dms =  array(
					'id_category3' => $code,
					'id_dimensi' => $dm['id_dimensi'],
					'nilai_dimensi' => $dm['nilai_dimensi'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_dimensi', $dms);
			}
		}
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
	public function saveEditInventoryOld()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$code = $this->Inventory_4_model->generate_id();
		$this->db->trans_begin();
		$id = $_POST['hd1']['1']['id_category3'];
		$numb1 = 0;
		//$head = $_POST['hd1'];
		foreach ($_POST['hd1'] as $h1) {
			$numb1++;

			$header1 =  array(
				'id_type'		        => $h1['inventory_1'],
				'id_category1'		    => $h1['inventory_2'],
				'id_category2'		    => $h1['inventory_3'],
				'nama'		        	=> $h1['nm_inventory'],
				'maker'		        	=> $h1['maker'],
				'density'		        => $h1['density'],
				'hardness'		        => $h1['hardness'],
				'id_bentuk'		        => $h1['id_bentuk'],
				'id_surface'		    => $h1['id_surface'],
				'mountly_forecast'		=> $h1['mountly_forecast'],
				'safety_stock'		    => $h1['safety_stock'],
				'order_point'		    => $h1['order_point'],
				'maksimum'		    	=> $h1['maksimum'],
				'aktif'					=> 'aktif',
				'created_on'		=> date('Y-m-d H:i:s'),
				'created_by'		=> $this->auth->user_id(),
				'deleted'			=> '0'
			);
			//Add Data
			$this->db->where('id_category3', $id)->update("ms_inventory_category3", $data);
		}
		if (empty($_POST['data1'])) {
		} else {
			$numb2 = 0;
			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$data1 =  array(
					'id_category3' => $id,
					'id_suplier' => $d1['id_supplier'],
					'lead' => $d1['lead'],
					'minimum' => $d1['minimum'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_suplier', $data1);
			}
		}
		if (empty($_POST['compo'])) {
		} else {
			$numb3 = 0;
			foreach ($_POST['compo'] as $c1) {
				$numb3++;
				$comp =  array(
					'id_category3' => $id,
					'id_compotition' => $c1['id_compotition'],
					'nilai_compotition' => $c1['jumlah_kandungan'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_compotition', $comp);
			}
		}
		if (empty($_POST['dimens'])) {
		} else {
			$numb4 = 0;
			foreach ($_POST['dimens'] as $dm) {
				$numb4++;
				$dms =  array(
					'id_category3' => $id,
					'id_dimensi' => $dm['id_dimensi'],
					'nilai_dimensi' => $dm['nilai_dimensi'],
					'deleted' => '0',
					'created_on' => date('Y-m-d H:i:s'),
					'created_by' => $this->auth->user_id(),
				);
				//Add Data
				$this->db->insert('child_inven_dimensi', $dms);
			}
		}
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
	function get_compotition()
	{
		$inventory_2 = $_GET['inventory_2'];
		$comp = $this->Inventory_4_model->compotition($inventory_2);
		$numb = 0;
		// print_r($data);
		// exit();
		foreach ($comp as $key => $cmp): $numb++;
			echo "<tr>
					  <td hidden align='left'>
					  <input type='text' name='compo[$numb][id_compotition]' readonly class='form-control'  value='$cmp->id_compotition'>
					  </td>
					  <td align='left'>
					  $cmp->name_compotition
					  </td>
					  <td align='left'>
					  <input type='text' name='compo[$numb][jumlah_kandungan]' class='form-control'>
					  </td>
					  <td align='left'>%</td>
                    </tr>";
		endforeach;
		echo "</select>";
	}



	public function update_material()
	{
		$sql = $this->db->query("SELECT * FROM ms_inventory_new")->result();



		// print_r($sql);
		// exit;

		$n = 0;

		foreach ($sql as $val => $valx) {

			$n++;




			$this->db->query("UPDATE ms_inventory_category33 
							SET nama='$valx->nama',maker='$valx->maker',density='$valx->density',hardness='$valx->hardness',thickness='$valx->thickness',spek='$valx->spek',alloy='$valx->alloy'
							WHERE id_category3='$valx->id_category3'");

			echo "$n";

			//$this->db->where('id', $valx->id )->update("ms_inventory_category32",$data);

		}
	}


	function get_surface()
	{
		$idsurface = $_GET['idsurface'];
		$surface = $this->db->query("SELECT * FROM ms_surface WHERE id_surface='$idsurface'")->row();
		echo "$surface->nm_surface";
	}

	/* ==================================================================
	 * Sheet (B2000002): form, detail, simpan, dan Material Ekuivalen
	 * ================================================================*/

	private function is_sheet($id)
	{
		$r = $this->db->select('id_bentuk')->get_where('ms_inventory_category3', array('id_category3' => $id))->row();
		return $r && $r->id_bentuk === 'B2000002';
	}

	private function is_roll($id)
	{
		$r = $this->db->select('id_bentuk')->get_where('ms_inventory_category3', array('id_category3' => $id))->row();
		return $r && $r->id_bentuk === 'B2000001';
	}

	/** Detail Roll (B2000001): view murni dengan Material Ekuivalen. */
	private function roll_view($id)
	{
		$M   = $this->Inventory_4_model;
		$r   = $M->getview($id);
		if (!$r) { show_404(); }
		$row = $r[0];
		$dim = $M->dim_map($id);
		$eq  = $M->eq_saved($id);
		$res = $M->eq_calc($id, (string) $row->id_spesifikasi, $row->thickness, false, $eq['manual'], $eq['exclude']);
		$surf = '';
		if ($row->id_surface !== '' && $row->id_surface !== null) {
			$sr = $this->db->get_where('ms_surface', array('id_surface' => $row->id_surface))->row();
			$surf = $sr ? $sr->nm_surface : '-';
		} else {
			$surf = '-';
		}
		$data = array(
			'row'       => $row,
			'dim'       => $dim,
			'sup'       => $M->supl($id),
			'komposisi' => $M->kompos($id),
			'surface'   => $surf,
			'eq_html'   => $this->eq_html($res, false, false, $row->thickness, $eq['manual']),
		);
		$this->template->set('results', $data);
		$this->template->render('view_roll');
	}

	/** Form Sheet. $mode: add | edit | copy. */
	private function sheet_form($mode, $id)
	{
		$M = $this->Inventory_4_model;
		$row = null;
		$dim = array();
		$sup = array();
		$eq  = array('manual' => array(), 'exclude' => array());
		if ($mode !== 'add') {
			$r = $M->getedit($id);
			if (!$r) {
				show_404();
			}
			$row = $r[0];
			$dim = $M->dim_map($id);
			$sup = $M->supl($id);
			if ($mode === 'edit') {
				$eq = $M->eq_saved($id);
			}
		}
		$types = $this->db->where('deleted', 0)->group_start()->where('aktif', 'aktif');
		if ($row) {
			$types->or_where('id_type', $row->id_type);
		}
		$types = $types->group_end()->order_by('nama', 'ASC')->get('ms_inventory_type')->result();

		$data = array(
			'mode'      => $mode,
			'row'       => $row,
			'dim'       => $dim,
			'sup'       => $sup,
			'eq'        => $eq,
			'types'     => $types,
			'c1'        => $row ? $M->opsi('c1', $row->id_type, $row->id_category1) : array(),
			'c2'        => $row ? $M->opsi('c2', $row->id_category1, $row->id_category2) : array(),
			'speks'     => $row ? $M->opsi('spek', $row->id_category2, (string) $row->id_spesifikasi) : array(),
			'suppliers' => $this->db->where('deleted', 0)->order_by('name_suplier', 'ASC')->get('master_supplier')->result(),
			'dimensi'   => $M->dimensi_sheet(),
		);
		$this->template->set('results', $data);
		$this->template->title($mode === 'edit' ? 'Edit Material' : 'Tambah Material');
		$this->template->render('sheet_form');
	}

	private function sheet_view($id)
	{
		$M = $this->Inventory_4_model;
		$r = $M->getview($id);
		if (!$r) {
			show_404();
		}
		$row = $r[0];
		$dim = $M->dim_map($id);
		$eq  = $M->eq_saved($id);
		$res = $M->eq_calc($id, (string) $row->id_spesifikasi, $row->thickness, true, $eq['manual'], $eq['exclude']);
		$data = array(
			'row'    => $row,
			'dim'    => $dim,
			'sup'    => $M->supl($id),
			'berat'  => $M->berat_sheet(isset($dim['THICKNESS']) ? $dim['THICKNESS'] : 0, isset($dim['WIDTH']) ? $dim['WIDTH'] : 0, isset($dim['LENGTH']) ? $dim['LENGTH'] : 0, $row->density),
			'eq_html' => $this->eq_html($res, false, true, $row->thickness, $eq['manual']),
		);
		$this->template->set('results', $data);
		$this->template->render('view_sheet');
	}

	/** Opsi berjenjang (JSON): level = c1 | c2 | spek. */
	public function sheet_opsi()
	{
		$this->auth->restrict($this->viewPermission);
		$level = $this->input->get('level');
		if (!in_array($level, array('c1', 'c2', 'spek'), true)) {
			return $this->json(array());
		}
		return $this->json($this->Inventory_4_model->opsi($level, (string) $this->input->get('parent'), (string) $this->input->get('keep')));
	}

	/** Baca daftar id dari POST. */
	private function post_ids($key)
	{
		$v = $this->input->post($key);
		return is_array($v) ? array_map('strval', $v) : array();
	}

	/** HTML tabel Material Ekuivalen untuk keadaan form saat ini. */
	public function ekuivalen_list()
	{
		$this->auth->restrict($this->viewPermission);
		$M = $this->Inventory_4_model;
		$self = (string) $this->input->post('self');
		$thick = (string) $this->input->post('thickness');
		$manual = $M->filter_ids($this->post_ids('manual'), $self);
		$excl = $M->filter_ids($this->post_ids('excl'), $self);
		$res = $M->eq_calc($self, (string) $this->input->post('id_spek'), $thick, $this->input->post('is_sheet') === '1', $manual, $excl);
		echo $this->eq_html($res, true, $this->input->post('is_sheet') === '1', $thick, $manual);
	}

	/** Hasil pencarian material untuk ditambah ke ekuivalen manual. */
	public function ekuivalen_cari()
	{
		$this->auth->restrict($this->viewPermission);
		$M = $this->Inventory_4_model;
		$self = (string) $this->input->post('self');
		$thick = (string) $this->input->post('thickness');
		$is_sheet = $this->input->post('is_sheet') === '1';
		$manual = $M->filter_ids($this->post_ids('manual'), $self);
		$excl = $M->filter_ids($this->post_ids('excl'), $self);
		$res = $M->eq_calc($self, (string) $this->input->post('id_spek'), $thick, $is_sheet, $manual, $excl);
		$c = $M->eq_cari($self, $res['ids'], (string) $this->input->post('q'));
		if (!$c['list']) {
			echo '<div class="text-muted">Tidak ada material lain yang cocok.</div>';
			return;
		}
		echo '<div style="max-height:220px;overflow:auto"><table class="table table-bordered table-condensed"><tbody>';
		foreach ($c['list'] as $m) {
			echo '<tr><td>' . html_escape($m->id_category3) . '</td><td>' . html_escape($m->nama) . '</td><td>' . html_escape($m->spek) . '</td><td>' . html_escape($m->nm_bentuk) . '</td>'
				. '<td><button type="button" class="btn btn-xs btn-primary eq-add" data-id="' . html_escape($m->id_category3) . '"><i class="fa fa-plus"></i> Tambah</button></td></tr>';
		}
		echo '</tbody></table></div>';
		if ($c['total'] > 30) {
			echo '<div class="text-muted">Menampilkan 30 dari ' . (int) $c['total'] . ' - persempit pencarian.</div>';
		}
	}

	private function eq_html($res, $editable, $is_sheet, $thickness, $manual)
	{
		$sp = $res['sp'];
		$manual = array_flip((array) $manual);
		$h = '<p class="text-muted" style="font-size:12px">Ekuivalen otomatis, divalidasi berurutan: <b>1) Spesifikasi sama</b> ('
			. ($sp ? '<b>' . html_escape($sp->nama) . '</b>' : 'belum dipilih') . '), lalu <b>2) Thickness sama</b>'
			. ($thickness !== '' && $thickness !== null ? ' (' . html_escape($thickness) . ' mm)' : ' (isi Thickness dulu)') . '. Maker tidak menjadi syarat. '
			. ($is_sheet ? 'Untuk Sheet, <b>Roll</b> dengan spesifikasi dan thickness yang sama juga ekuivalen karena Roll dapat menjadi Sheet. ' : 'Sheet tidak ekuivalen dengan Roll. ')
			. 'Ditemukan ' . count($res['list']) . ' material (relasi manual dua arah dan berantai: A=B dan B=C berarti A=C)'
			. ($editable ? '. Semua material pada daftar ini bisa dihapus, dan material lain bisa ditambahkan.' : '.') . '</p>';
		if (!$res['list']) {
			return $h . '<div class="well well-sm text-center">Tidak ada material lain dengan spesifikasi sama dan thickness sama</div>';
		}
		$tag = array(
			'manual'     => '<span class="label label-info">Manual</span>',
			'berantai'   => '<span class="label label-warning">Berantai</span>',
			'sama'       => '<span class="label label-success">Sama</span>',
			'roll_sheet' => '<span class="label label-success">Roll &rarr; Sheet</span>',
		);
		$h .= '<div style="max-height:300px;overflow:auto"><table class="table table-bordered table-striped table-condensed"><thead><tr>'
			. '<th>Id Material</th><th>Nama Material</th><th>Spesifikasi</th><th>Relasi</th><th>Bentuk</th><th>Hardness</th><th>Thickness</th><th>Maker</th><th>Status</th>'
			. ($editable ? '<th>Action</th>' : '') . '</tr></thead><tbody>';
		foreach ($res['list'] as $m) {
			$status = $m->aktif === 'aktif' ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Non Aktif</span>';
			$h .= '<tr><td>' . html_escape($m->id_category3) . '</td><td>' . html_escape($m->nama) . '</td><td>' . html_escape($m->spek) . '</td><td>' . $tag[$m->rel]
				. '</td><td>' . html_escape($m->nm_bentuk) . '</td><td>' . html_escape($m->hardness) . '</td><td>' . html_escape($m->thickness) . '</td><td>'
				. html_escape($m->maker !== '' && $m->maker !== null ? $m->maker : '-') . '</td><td>' . $status . '</td>';
			if ($editable) {
				$h .= '<td>' . ($m->rel === 'berantai' ? '<span class="text-muted">lewat material lain</span>'
					: '<button type="button" class="btn btn-xs btn-danger eq-rm" data-id="' . html_escape($m->id_category3) . '" title="Hapus dari daftar ekuivalen"><i class="fa fa-trash"></i></button>') . '</td>';
			}
			$h .= '</tr>';
		}
		return $h . '</tbody></table></div>';
	}

	private function json($data)
	{
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	private function sheet_num($v)
	{
		$v = trim((string) $v);
		return ($v !== '' && is_numeric($v) && (float) $v > 0) ? $v : false;
	}

	/**
	 * Validasi dan susun data Sheet dari POST.
	 * @return array [pesan_error, data]  pesan_error '' bila valid
	 */
	private function sheet_input($old)
	{
		$M = $this->Inventory_4_model;
		$p = $this->input->post();
		$g = function ($k) use ($p) {
			return isset($p[$k]) && !is_array($p[$k]) ? trim((string) $p[$k]) : '';
		};
		$type = $g('t');
		$c1 = $g('c1');
		$c2 = $g('c2');
		$sid = $g('spek');
		if ($type === '' || $c1 === '' || $c2 === '' || $sid === '') {
			return array('Type Inventory, Ferous / Non Ferous, Type Alloy dan Spesifikasi wajib diisi.', null);
		}
		$err = $M->validasi_hierarki($type, $c1, $c2, $sid, $old);
		if ($err) {
			return array($err, null);
		}
		$dimIn = isset($p['dim']) && is_array($p['dim']) ? $p['dim'] : array();
		$dim = array();
		foreach (array('THICKNESS', 'WIDTH', 'LENGTH') as $k) {
			$v = $this->sheet_num(isset($dimIn[$k]) ? $dimIn[$k] : '');
			if ($v === false) {
				return array($k . ' wajib diisi dengan angka lebih dari 0.', null);
			}
			$dim[$k] = $v;
		}
		$density = $this->sheet_num($g('density'));
		if ($density === false) {
			return array('Density wajib diisi dengan angka lebih dari 0.', null);
		}
		$hardness = $g('hardness');
		if (mb_strlen($hardness) > 11) {
			return array('Hardness maksimal 11 karakter.', null);
		}
		$spek = $M->get_spesifikasi($sid);
		$alloy = $M->nama_alloy($c2);
		$nama = $g('nama');
		if ($nama === '') {
			$nama = $M->build_nama_sheet($spek->nama, $hardness, $dim['THICKNESS'], $dim['WIDTH'], $dim['LENGTH'], $alloy);
		}
		if (mb_strlen($nama) > 50) {
			return array('Nama Material maksimal 50 karakter (sekarang ' . mb_strlen($nama) . '). Persingkat nama.', null);
		}
		$stok = array();
		foreach (array('forecast' => 'Monthly Forecast', 'safety' => 'Safety Stock', 'order' => 'Order Point', 'max' => 'Maximum Stock') as $k => $label) {
			$v = $g($k);
			if ($v !== '' && (!is_numeric($v) || (float) $v < 0 || strlen($v) > 11)) {
				return array($label . ' harus angka tidak negatif (maks 11 karakter).', null);
			}
			$stok[$k] = $v;
		}
		// Supplier
		$sup = array();
		$ids = array();
		if (isset($p['sup']) && is_array($p['sup'])) {
			foreach ($p['sup'] as $s) {
				$sid2 = isset($s['s']) ? trim((string) $s['s']) : '';
				if ($sid2 === '') {
					continue;
				}
				$sup[] = array('id' => $sid2, 'lead' => mb_substr(trim((string) (isset($s['lead']) ? $s['lead'] : '')), 0, 50), 'min' => mb_substr(trim((string) (isset($s['min']) ? $s['min'] : '')), 0, 50));
				$ids[] = $sid2;
			}
		}
		if ($ids) {
			$n = $this->db->where('deleted', 0)->where_in('id_suplier', array_unique($ids))->count_all_results('master_supplier');
			if ($n !== count(array_unique($ids))) {
				return array('Ada supplier yang tidak valid.', null);
			}
		}
		return array('', array(
			'type' => $type, 'c1' => $c1, 'c2' => $c2, 'spek_id' => $sid, 'spek' => $spek->nama, 'alloy' => $alloy,
			'dim' => $dim, 'density' => $density, 'hardness' => $hardness, 'nama' => $nama, 'stok' => $stok, 'sup' => $sup,
			'berat' => $M->berat_sheet($dim['THICKNESS'], $dim['WIDTH'], $dim['LENGTH'], $density),
		));
	}

	/** Isi tabel anak (dimensi, supplier) dan pasangan ekuivalen. Dipanggil dalam transaksi. */
	private function sheet_children($id, $d, $manual, $excl)
	{
		$M = $this->Inventory_4_model;
		$now = date('Y-m-d H:i:s');
		$user = $this->auth->user_id();

		$this->db->delete('child_inven_dimensi', array('id_category3' => $id));
		foreach ($M->dimensi_sheet() as $ds) {
			$k = strtoupper($ds->nm_dimensi);
			if (isset($d['dim'][$k])) {
				$this->db->insert('child_inven_dimensi', array(
					'id_category3' => $id, 'id_dimensi' => $ds->id_dimensi, 'nilai_dimensi' => $d['dim'][$k],
					'deleted' => '0', 'created_on' => $now, 'created_by' => $user,
				));
			}
		}
		$this->db->delete('child_inven_suplier', array('id_category3' => $id));
		foreach ($d['sup'] as $s) {
			$this->db->insert('child_inven_suplier', array(
				'id_category3' => $id, 'id_suplier' => $s['id'], 'lead' => $s['lead'], 'minimum' => $s['min'],
				'deleted' => '0', 'created_on' => $now, 'created_by' => $user,
			));
		}
		$M->eq_save($id, $manual, $excl, $user);
	}

	private function sheet_header($d)
	{
		return array(
			'id_type' => $d['type'], 'id_category1' => $d['c1'], 'id_category2' => $d['c2'],
			'id_spesifikasi' => $d['spek_id'], 'spek' => $d['spek'], 'alloy' => $d['alloy'],
			'density' => $d['density'], 'hardness' => $d['hardness'], 'id_bentuk' => 'B2000002',
			'mountly_forecast' => $d['stok']['forecast'], 'safety_stock' => $d['stok']['safety'],
			'order_point' => $d['stok']['order'], 'maksimum' => $d['stok']['max'],
			'thickness' => $d['dim']['THICKNESS'], 'nama' => $d['nama'], 'total_weight' => $d['berat'],
		);
	}

	public function saveNewSheet()
	{
		$this->auth->restrict($this->addPermission);
		$M = $this->Inventory_4_model;
		list($err, $d) = $this->sheet_input(null);
		if ($err) {
			return $this->json(array('status' => 0, 'pesan' => $err, 'code' => 'B2000002'));
		}
		$this->db->trans_begin();
		$code = $M->generate_id();
		$now = date('Y-m-d H:i:s');
		$user = $this->auth->user_id();
		$this->db->insert('ms_inventory_category3', array_merge($this->sheet_header($d), array(
			'id_category3' => $code, 'maker' => '', 'negara' => '', 'id_surface' => '',
			'aktif' => 'aktif', 'created_on' => $now, 'created_by' => $user, 'deleted' => '0',
		)));
		$this->db->insert('ms_bookprice_material', array(
			'id_category3' => $code, 'bentuk' => 'SHEET', 'alloy' => $d['alloy'], 'material' => $d['nama'],
			'hardness' => $d['hardness'], 'thickness' => $d['dim']['THICKNESS'],
		));
		$this->sheet_children($code, $d, $M->filter_ids($this->post_ids('eq_manual'), $code), $M->filter_ids($this->post_ids('eq_excl'), $code));

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return $this->json(array('status' => 0, 'pesan' => 'Gagal menyimpan data.', 'code' => 'B2000002'));
		}
		$this->db->trans_commit();
		return $this->json(array('status' => 1, 'pesan' => 'Data Inventory berhasil disimpan.', 'code' => 'B2000002', 'id' => $code));
	}

	public function saveEditSheet()
	{
		$this->auth->restrict($this->managePermission);
		$M = $this->Inventory_4_model;
		$id = trim((string) $this->input->post('id_category3'));
		$old = $this->db->get_where('ms_inventory_category3', array('id_category3' => $id, 'deleted' => 0, 'id_bentuk' => 'B2000002'))->row();
		if (!$old) {
			return $this->json(array('status' => 0, 'pesan' => 'Data material tidak ditemukan.', 'code' => 'B2000002'));
		}
		list($err, $d) = $this->sheet_input($old);
		if ($err) {
			return $this->json(array('status' => 0, 'pesan' => $err, 'code' => 'B2000002'));
		}
		$aktif = $this->input->post('aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

		$this->db->trans_begin();
		$this->db->where('id_category3', $id)->update('ms_inventory_category3', array_merge($this->sheet_header($d), array(
			'aktif' => $aktif, 'modified_on' => date('Y-m-d H:i:s'), 'modified_by' => $this->auth->user_id(),
		)));
		$bp = array('bentuk' => 'SHEET', 'alloy' => $d['alloy'], 'material' => $d['nama'], 'hardness' => $d['hardness'], 'thickness' => $d['dim']['THICKNESS']);
		if ($this->db->where('id_category3', $id)->count_all_results('ms_bookprice_material') > 0) {
			$this->db->where('id_category3', $id)->update('ms_bookprice_material', $bp);
		} else {
			$this->db->insert('ms_bookprice_material', array_merge($bp, array('id_category3' => $id)));
		}
		$this->sheet_children($id, $d, $M->filter_ids($this->post_ids('eq_manual'), $id), $M->filter_ids($this->post_ids('eq_excl'), $id));

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return $this->json(array('status' => 0, 'pesan' => 'Gagal menyimpan data.', 'code' => 'B2000002'));
		}
		$this->db->trans_commit();
		return $this->json(array('status' => 1, 'pesan' => 'Data Inventory berhasil disimpan.', 'code' => 'B2000002'));
	}

	public function get_data_display()
	{
		$id_bentuk = $this->input->post('id_bentuk');

		$list = $this->Inventory_4_model->get_datatables($id_bentuk);
		$data = array();
		$no = $this->input->post('start');

		$ENABLE_ADD     = has_permission('Level_4.Add');
		$ENABLE_MANAGE  = has_permission('Level_4.Manage');
		$ENABLE_VIEW    = has_permission('Level_4.View');
		$ENABLE_DELETE  = has_permission('Level_4.Delete');

		foreach ($list as $record) {
			$no++;
			$row = array();

			$status = ($record->aktif == 'aktif') ? '<label class="label label-success">Aktif</label>' : '<label class="label label-danger">Non Aktif</label>';

			$action = '<td style="padding-left:20px">';
			if ($ENABLE_VIEW) {
				$action .= '<a class="btn btn-primary btn-sm view" href="javascript:void(0)" title="View" data-id_inventory3="' . $record->id_category3 . '"><i class="fa fa-eye"></i></a> ';
			}
			if ($ENABLE_ADD) {
				if ($id_bentuk != 'B2000001') {
					$action .= '<a class="btn btn-warning btn-sm copy" href="javascript:void(0)" title="Copy" data-id_inventory3="' . $record->id_category3 . '"><i class="fa fa-copy"></i></a> ';
				}
			}
			if ($ENABLE_MANAGE) {
				if ($id_bentuk != 'B2000001') {
					$action .= '<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" data-id_inventory3="' . $record->id_category3 . '"><i class="fa fa-edit"></i></a> ';
				}
			}
			if ($ENABLE_DELETE) {
				$action .= '<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id_inventory3="' . $record->id_category3 . '"><i class="fa fa-trash"></i></a>';
			}
			$action .= '</td>';

			if ($id_bentuk == 'B2000001') {
				$row[] = $no;
				$row[] = $record->id_category3;
				$row[] = $record->nama_type;
				$row[] = $record->nama_category1;
				$row[] = $record->nama_category2;
				$row[] = $record->nama;
				$row[] = $record->nama;
				$row[] = $record->spek;
				$row[] = $record->thickness;
				$row[] = $record->hardness;
				$row[] = $record->density;
				$row[] = $record->maker;
				$row[] = $record->negara;
				$row[] = $status;
				if ($ENABLE_MANAGE) {
					$row[] = $action;
				}
			} else if ($id_bentuk == 'B2000002') {
				$row[] = $no;
				$row[] = $record->id_category3;
				$row[] = $record->nama_type;
				$row[] = $record->nama_category1;
				$row[] = $record->nama_category2;
				$row[] = $record->nama;
				$row[] = $record->nama;

				// Supplier logic
				$sup_names = [];
				$id = $record->id_category3;
				$sup = $this->db->get_where('child_inven_suplier', array('id_category3' => $id))->result();
				foreach ($sup as $sp) {
					$kodesup = $sp->id_suplier;
					$sup2 = $this->db->get_where('master_supplier', array('id_suplier' => $kodesup))->result();
					foreach ($sup2 as $sp2) {
						$sup_names[] = $sp2->name_suplier;
					}
				}
				$row[] = implode(", ", $sup_names);

				$row[] = $status;
				if ($ENABLE_MANAGE) {
					$row[] = $action;
				}
			} else {
				$row[] = $no;
				$row[] = $record->id_category3;
				$row[] = $record->nama_type;
				$row[] = $record->nama_category1;
				$row[] = $record->nama_category2;
				$row[] = $record->nama;
				$row[] = $record->nama_category2 . '-' . $record->nama . '-' . $record->hardness;

				// Supplier logic
				$sup_names = [];
				$id = $record->id_category3;
				$sup = $this->db->get_where('child_inven_suplier', array('id_category3' => $id))->result();
				foreach ($sup as $sp) {
					$kodesup = $sp->id_suplier;
					$sup2 = $this->db->get_where('master_supplier', array('id_suplier' => $kodesup))->result();
					foreach ($sup2 as $sp2) {
						$sup_names[] = $sp2->name_suplier;
					}
				}
				$row[] = implode(", ", $sup_names);

				$row[] = $status;
				if ($ENABLE_MANAGE) {
					$row[] = $action;
				}
			}

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->Inventory_4_model->count_alls($id_bentuk),
			"recordsFiltered" => $this->Inventory_4_model->count_filtered($id_bentuk),
			"data" => $data,
		);

		echo json_encode($output);
	}
}
