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

class Adjustmentstock extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Adjustment.View';
	protected $addPermission  	= 'Adjustment.Add';
	protected $managePermission = 'Adjustment.Manage';
	protected $deletePermission = 'Adjustment.Delete';


	public function __construct()
	{
		parent::__construct();

		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Adjustmentstock/Inventory_4_model',
			'adjustmentstock/Adjustment_balance_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Jakarta');
	}
	public function index()
	{
		$id_bentuk = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		// $stok		 = $this->Inventory_4_model->get_data_category3();
		// $history	= $this->db->query("SELECT * FROM adjustment_stock ")->result();
		// $data = [
		// 	'stok' => $stok,
		// 	'history' => $history,
		// ];
		// $this->template->set('results', $data);
		$this->template->title('Adjustment Stock');
		$this->template->render('index');
	}
	public function detail()
	{
		$id_category3 = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Inventory_4_model->dapat_stock($id_category3);
		$this->template->set('results', $data);
		$this->template->title('Stock Material');
		$this->template->render('list');
	}
	public function GudangRawMaterial()
	{
		$id_gudang = '1';
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$gudang = $this->Inventory_4_model->PerGudang($id_gudang);
		$jumlah = $this->Inventory_4_model->SumPerGudang($id_gudang);
		$data = [
			'gudang' => $gudang,
			'jumlah' => $jumlah,
		];
		$this->template->set('results', $data);
		$this->template->title('Stock Material');
		$this->template->render('gudang');
	}
	public function GudangWIP()
	{
		$id_gudang = '2';
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$gudang = $this->Inventory_4_model->PerGudang($id_gudang);
		$jumlah = $this->Inventory_4_model->SumPerGudang($id_gudang);
		$data = [
			'gudang' => $gudang,
			'jumlah' => $jumlah,
		];
		$this->template->set('results', $data);
		$this->template->title('Stock Material');
		$this->template->render('gudang');
	}
	public function GudangFinishGood()
	{
		$id_gudang = '3';
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$gudang = $this->Inventory_4_model->PerGudang($id_gudang);
		$jumlah = $this->Inventory_4_model->SumPerGudang($id_gudang);
		$data = [
			'gudang' => $gudang,
			'jumlah' => $jumlah,
		];
		$this->template->set('results', $data);
		$this->template->title('Stock Material');
		$this->template->render('gudang');
	}
	public function GudangScrap()
	{
		$id_gudang = '4';
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$gudang = $this->Inventory_4_model->PerGudang($id_gudang);
		$jumlah = $this->Inventory_4_model->SumPerGudang($id_gudang);
		$data = [
			'gudang' => $gudang,
			'jumlah' => $jumlah,
		];
		$this->template->set('results', $data);
		$this->template->title('Stock Material');
		$this->template->render('gudang');
	}
	public function GudangSubcont()
	{
		$id_gudang = '5';
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$gudang = $this->Inventory_4_model->PerGudang($id_gudang);
		$jumlah = $this->Inventory_4_model->SumPerGudang($id_gudang);
		$data = [
			'gudang' => $gudang,
			'jumlah' => $jumlah,
		];
		$this->template->set('results', $data);
		$this->template->title('Stock Material');
		$this->template->render('gudang');
	}

	public function editInventory($id)
	{
		$this->auth->restrict($this->viewPermission);
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
		$this->template->render('edit_inventory');
	}
	public function copyInventory($id)
	{
		$this->auth->restrict($this->viewPermission);
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
			'id_supplier' => $id_supplier
		];
		$this->template->set('results', $data);
		$this->template->title('Add Inventory');
		$this->template->render('add_inventory');
	}

	public function AddStock($id)
	{
		$id_category3 = $this->uri->segment(3);
		$id_data 	= $this->input->post('id');
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$inventory_3 = $this->Inventory_4_model->GetMaterial($id_category3);
		$gudang = $this->Inventory_4_model->get_data('ms_gudang');
		$data = [
			'inventory_3' => $inventory_3,
			'gudang' => $gudang
		];
		$this->template->set('results', $data);
		$this->template->title('Add Stock');
		$this->template->render('AddStock');
	}

	public function AdjustNow($id)
	{
		$this->auth->restrict($this->addPermission);
		try {
			$material = $this->Adjustment_balance_model->material($id);
			$this->template->set('material', $material);
			$this->template->render('Adjust');
		} catch (InvalidArgumentException $e) {
			$this->output->set_status_header(422)->set_output(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
		}
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
	public function form_options()
	{
		$this->auth->restrict($this->addPermission);
		try {
			$id = $this->input->get('id_material');
			if (!is_string($id) || $id === '' || strlen($id) > 15) throw new InvalidArgumentException('Material tidak valid.');
			$data = $this->Adjustment_balance_model->options($id);
			$data['status'] = 1;
		} catch (InvalidArgumentException $e) {
			$data = array('status'=>0, 'pesan'=>$e->getMessage());
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function PrintHeader()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$data['header']  = $this->db->query("SELECT * FROM stock_material ")->result();
		$data['sum'] = $this->db->query("SELECT SUM(weight) as weight FROM stock_material ")->result();
		$this->template->title('Data');
		$this->load->view('PrintHeader', $data);
		$html = ob_get_contents();
		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('L', 'A4', 'en', true, 'UTF-8', array(0, 0, 0, 0));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output($id . '.pdf', 'I');
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
	public function SavePlus()
	{
		$this->auth->restrict($this->addPermission);
		if ($this->input->method() !== 'post') {
			$this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(array('status'=>0,'pesan'=>'Gunakan metode POST.')));
			return;
		}
		try {
			$code = $this->Adjustment_balance_model->save($this->input->post(), $this->auth->user_id());
			$data = array('status'=>1,'pesan'=>'Adjustment stok berhasil disimpan.','code'=>$code);
		} catch (InvalidArgumentException $e) {
			$data = array('status'=>0,'pesan'=>$e->getMessage());
		} catch (Exception $e) {
			log_message('error', 'Adjustment Stock: '.$e->getMessage());
			$data = array('status'=>0,'pesan'=>'Adjustment gagal disimpan. Muat ulang saldo dan coba lagi.');
		}
		if ($this->config->item('csrf_protection')) {
			$data['csrf'] = array('name'=>$this->security->get_csrf_token_name(),'hash'=>$this->security->get_csrf_hash());
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function saveEditInventory()
	{
		$this->auth->restrict($this->addPermission);
		$session = $this->session->userdata('app_session');
		$code = $this->Inventory_4_model->generate_id();
		$this->db->trans_begin();
		$id = $_POST['hd1']['1']['id_category3'];
		$id_bentuk = $_POST['hd1']['1']['id_bentuk'];
		$numb1 = 0;
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
			$this->db->where('id_category3', $id)->update("ms_inventory_category3", $header1);
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
				'id_type'		        => $h1[inventory_1],
				'id_category1'		    => $h1[inventory_2],
				'id_category2'		    => $h1[inventory_3],
				'nama'		        	=> $h1[nm_inventory],
				'maker'		        	=> $h1[maker],
				'density'		        => $h1[density],
				'hardness'		        => $h1[hardness],
				'id_bentuk'		        => $h1[id_bentuk],
				'id_surface'		    => $h1[id_surface],
				'mountly_forecast'		=> $h1[mountly_forecast],
				'safety_stock'		    => $h1[safety_stock],
				'order_point'		    => $h1[order_point],
				'maksimum'		    	=> $h1[maksimum],
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
					'id_suplier' => $d1[id_supplier],
					'lead' => $d1[lead],
					'minimum' => $d1[minimum],
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
					'id_compotition' => $c1[id_compotition],
					'nilai_compotition' => $c1[jumlah_kandungan],
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
					'id_dimensi' => $dm[id_dimensi],
					'nilai_dimensi' => $dm[nilai_dimensi],
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
				'id_type'		        => $h1[inventory_1],
				'id_category1'		    => $h1[inventory_2],
				'id_category2'		    => $h1[inventory_3],
				'nama'		        	=> $h1[nm_inventory],
				'maker'		        	=> $h1[maker],
				'density'		        => $h1[density],
				'hardness'		        => $h1[hardness],
				'id_bentuk'		        => $h1[id_bentuk],
				'id_surface'		    => $h1[id_surface],
				'mountly_forecast'		=> $h1[mountly_forecast],
				'safety_stock'		    => $h1[safety_stock],
				'order_point'		    => $h1[order_point],
				'maksimum'		    	=> $h1[maksimum],
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
					'id_suplier' => $d1[id_supplier],
					'lead' => $d1[lead],
					'minimum' => $d1[minimum],
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
					'id_compotition' => $c1[id_compotition],
					'nilai_compotition' => $c1[jumlah_kandungan],
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
					'id_dimensi' => $dm[id_dimensi],
					'nilai_dimensi' => $dm[nilai_dimensi],
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

	public function history_datatable()
	{
		$this->auth->restrict($this->viewPermission);
		// Inputan POST dari Datatables
		$draw = $this->input->post('draw', true);
		$length = $this->input->post('length', true);
		$start = $this->input->post('start', true);
		$search = $this->input->post('search', true)['value'];

		// Query utama untuk mendapatkan data
		$this->db->select('a.id_transaksi, a.tanggal_transaksi, a.id_material, a.nama_material, a.adjustment, a.nama_gudang, a.nama_gudang_baru, a.note, a.total_qty, a.total_jumlah_berat');
		$this->db->from('adjustment_stock a');

		// Hitung total records sebelum filtering
		$count_all_records = $this->db->count_all_results('', false);

		// Filtering berdasarkan pencarian
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.id_transaksi', $search);
			$this->db->or_like('a.nama_material', $search);
			$this->db->or_like('a.adjustment', $search);
			$this->db->or_like('a.nama_gudang', $search);
			$this->db->or_like('a.nama_gudang_baru', $search);
			$this->db->or_like('a.note', $search);
			$this->db->group_end();
		}

		// Hitung total records setelah filtering
		$count_filtered_records = $this->db->count_all_results('', false);

		// Pengurutan dan pembatasan data
		$this->db->order_by('a.tanggal_transaksi', 'DESC');
		$this->db->limit($length, $start);
		$query = $this->db->get();
		$data = $query->result_array();

		// Persiapan data untuk output
		$no = (int)$start + 1;
		$data_result = [];
		foreach ($data as $row) {
			if ($row['adjustment'] == 'PLUS') {
				$gudang = $row['nama_gudang'];
			} elseif ($row['adjustment'] == 'MINUS') {
				$gudang = $row['nama_gudang'];
			} elseif ($row['adjustment'] == 'MUTASI') {
				$gudang = $row['nama_gudang'] . ' -> ' . $row['nama_gudang_baru'];
			}

			$data_result[] = [
				'no' => $no++,
				'no_transaksi' => $row['id_transaksi'],
				'tanggal_transaksi' => $row['tanggal_transaksi'],
				'nama_material' => $row['nama_material'],
				'adjustment' => $row['adjustment'],
				'gudang' => $gudang,
				'keterangan' => $row['note'],
				'qty' => $row['total_qty'],
				'jumlah_stock' => $row['total_jumlah_berat']
			];
		}
		// Output dalam format JSON
		$output = [
			'draw' => $draw,
			'recordsTotal' => $count_all_records,
			'recordsFiltered' => $count_filtered_records,
			'data' => $data_result
		];

		echo json_encode($output);
	}

	public function adjust_datatable()
	{
		$this->auth->restrict($this->viewPermission);
		// Inputan POST dari Datatables
		$draw = $this->input->post('draw', true);
		$length = $this->input->post('length', true);
		$start = $this->input->post('start', true);
		$search = $this->input->post('search', true)['value'];

		// Query utama untuk mendapatkan data
		$this->db->select('a.*, b.nama as nama_type, c.nama as nama_category1,f.nm_bentuk as nm_bentuk, d.nama as nama_category2, e.nilai_dimensi as nilai_dimensi');
		$this->db->from('ms_inventory_category3 a');
		$this->db->join('ms_inventory_type b', 'b.id_type=a.id_type');
		$this->db->join('ms_inventory_category1 c', 'c.id_category1 =a.id_category1');
		$this->db->join('ms_inventory_category2 d', 'd.id_category2 =a.id_category2');
		$this->db->join('child_inven_dimensi e', 'e.id_category3 =a.id_category3');
		$this->db->join('ms_bentuk f', 'f.id_bentuk =a.id_bentuk');
		$this->db->where('a.deleted', '0');

		// Hitung total records sebelum filtering
		$count_all_records = $this->db->count_all_results('', false);

		// Filtering berdasarkan pencarian
		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.nama', $search);
			$this->db->or_like('b.nama', $search);
			$this->db->or_like('c.nama', $search);
			$this->db->or_like('d.nama', $search);
			$this->db->or_like('f.nm_bentuk', $search);
			$this->db->or_like('e.nilai_dimensi', $search);
			$this->db->group_end();
		}

		// Hitung total records setelah filtering
		$count_filtered_records = $this->db->count_all_results('', false);

		// Pengurutan dan pembatasan data
		$this->db->group_by('a.id_category3');
		$this->db->order_by('a.created_on', 'DESC');
		$this->db->limit($length, $start);
		$query = $this->db->get();
		$data = $query->result_array();

		// Persiapan data untuk output
		$no = (int)$start + 1;
		$data_result = [];
		foreach ($data as $row) {
			$this->db->select('a.*, b.name_suplier as nama_supplier');
			$this->db->from('child_inven_suplier a');
			$this->db->join('master_supplier b', 'b.id_suplier =a.id_suplier');
			$this->db->where('a.id_category3', $row['id_category3']);
			$suppliers = $this->db->get()->result_array();
			$supplier_names = array_column($suppliers, 'nama_supplier');
			$supplier_list = implode(', ', $supplier_names);

			$get_total_stock = $this->Inventory_4_model->get_total_stock($row['id_category3']);
			$jumlah_stok = !empty($get_total_stock->total_stock) ? $get_total_stock->total_stock : '0';

			$action = '<button type="button" class="btn btn-primary edit" data-id_inventory3="' . htmlspecialchars($row['id_category3'], ENT_QUOTES, 'UTF-8') . '">Adjustment</button>';

			$data_result[] = [
				'no' => $no++,
				'ferrous_nonferrous' => $row['nama_category1'],
				'id' => $row['id_category3'],
				'detail_nama_material' => $row['nama'],
				'bentuk_material' => $row['nm_bentuk'],
				'supplier' => $supplier_list,
				'jumlah_stok' => number_format($jumlah_stok, 2),
				'action' => $action
			];
		}

		// Output dalam format JSON
		$output = [
			'draw' => $draw,
			'recordsTotal' => $count_all_records,
			'recordsFiltered' => $count_filtered_records,
			'data' => $data_result
		];
		echo json_encode($output);
	}
}
