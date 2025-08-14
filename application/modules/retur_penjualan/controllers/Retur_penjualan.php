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

class Retur_penjualan extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Retur_Penjualan.View';
	protected $addPermission  	= 'Retur_Penjualan.Add';
	protected $managePermission = 'Retur_Penjualan.Manage';
	protected $deletePermission = 'Retur_Penjualan.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Retur_penjualan/Retur_penjualan_model',
			'Spk_marketing/Inventory_4_model',
			'Delivery_order/Delivery_order_model',
			'Aktifitas/aktifitas_model',
		));
		$this->template->title('Manage Data Supplier');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}
	public function incoming()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$this->template->title('Retur Penjualan');
		$this->template->render('list_spkmarketing');
	}
	public function proses_incoming()
	{

		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';

		$nospk  = $this->db->query("SELECT a.no_surat FROM tr_spk_marketing a WHERE a.id_spkmarketing='$id'")->row();
		$spkmkt = $nospk->no_surat;
		$tr_spk = $this->Retur_penjualan_model->get_data('tr_spk_marketing', 'id_spkmarketing', $id);
		// $dtspk = $this->Retur_penjualan_model->get_data('dt_spkmarketing',array('id_spkmarketing',$id));
		$dtspk = $this->db->query("SELECT a.*, b.nama, b.maker FROM stock_material a
		INNER JOIN ms_inventory_category3 b ON b.id_category3 = a.id_category3
		WHERE a.no_surat ='$spkmkt'")->result();



		$penawaran = $this->Retur_penjualan_model->get_data('tr_penawaran');
		$customer = $this->db
			->select('a.id_customer, b.name_customer')
			->from('tr_penawaran a')
			->join('master_customers b', 'a.id_customer=b.id_customer', 'left')
			->where('a.status', 'N')
			->group_by('a.id_customer')
			->order_by('b.name_customer', 'asc')
			->get()
			->result();
		$karyawan = $this->Retur_penjualan_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Retur_penjualan_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'tr_spk' => $tr_spk,
			'dtspk' => $dtspk,
			'penawaran' => $penawaran,
			'customer' => $customer,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
		];

		$gudang	= $this->db->query("select * FROM ms_gudang ")->result();
		$this->template->set('gudang', $gudang);
		$this->template->set('results', $data);
		$this->template->title('Terima Retur Penjualan');
		$this->template->render('terima_barang');
	}

	public function SaveRetur()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;

		$code = $this->Retur_penjualan_model->generate_code();
		$no_surat = $this->Retur_penjualan_model->BuatNomor();
		$this->db->trans_begin();
		$data = [
			'id_retur'		        => $code,
			'no_retur'				=> $no_surat,
			'id_spkmarketing'		=> $post['id_spkmarketing'],
			'no_surat'				=> $post['no_surat'],
			'tgl_retur'		        => $post['tgl_penawaran'],
			'id_customer'			=> $post['id_customer'],
			'nama_customer'			=> $post['nama_customer'],
			'no_penawaran'			=> $post['no_penawaran'],
			'no_po'					=> $post['no_po'],
			'sample'				=> $post['sample'],
			'tgl_po'				=> date('Y-m-d', strtotime($post['tgl_po'])),
			'plan_cust'				=> date('Y-m-d', strtotime($post['plan_cust'])),
			'note'					=> $post['note'],
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'tahun'					=> date('Y-m-d'),
			'kompensasi'			=> $post['kompensasi'],
			'ganti_material'	    => $post['ganti']

		];
		//Add Data
		$this->db->insert('tr_retur_penjualan', $data);
		$numb1 = 0;
		foreach ($_POST['dp'] as $dp) {
			$numb1++;

			$deal        = $dp[deal];
			$id_material = $dp[id_category3];
			$idspkmarketing = $post['id_spkmarketing'];
			$idpenawaran  = $post['no_penawaran'];

			$ppn       = $this->db->query("SELECT exclude_vat FROM tr_penawaran WHERE no_penawaran='$idpenawaran'")->row();

			$harga       = $this->db->query("SELECT * FROM dt_spkmarketing WHERE id_spkmarketing='$idspkmarketing' AND id_material='$id_material'")->row();
			$hargadeal      = $harga->harga_deal;
			$totalretur     = $dp[total_kirim];
			$totalharga     = $hargadeal * $totalretur;
			if ($ppn->exclude_vat != '' || $ppn->exclude_vat != '0') {
				$totalppn   = ($totalharga * $ppn->exclude_vat) / 100;
			} else {
				$totalppn   = 0;
			}




			if ($deal == 1) {
				$detRetur =  array(
					'id_retur'				=> $code,
					'id_dt_retur'			=> $code . '-' . $numb1,
					'id_material'		    => $dp[id_category3],
					'thickness'		        => $dp[thickness],
					'width'		        	=> $dp[width],
					'harga_deal'		    => $hargadeal,
					'qty_produk'			=> 1,
					'weight'		    	=> $totalretur,
					'total_weight'		    => $totalretur,
					'total_harga'		    => $totalharga,
					'total_ppn'		        => $totalppn,
					'deal'		    		=> $dp[deal],
					'created_on'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $this->auth->user_id(),
					'id_stok'		    	=> $dp[id_stok],
					'lotno'	    			=> $dp['lotno']
				);
				$this->db->insert('dt_returpenjualan', $detRetur);
			}
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function list_retur_penjualan()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Retur_penjualan_model->CariRetur();
		$this->template->set('results', $data);
		$this->template->title('Retur Penjualan');
		$this->template->render('list_retur_penjualan');
	}

	public function PrintH2()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] 	= $this->db->get_where('tr_retur_penjualan', array('id_retur' => $id))->result();
		$data['detail']  	= $this->db
			->select('a.*, b.nama AS item')
			->from('dt_returpenjualan a')
			->join('ms_inventory_category3 b', 'b.id_category3=a.id_material')
			->where('a.id_retur', $id)
			->get()
			->result();

		$data['penawaran'] = $this->Retur_penjualan_model->get_data('tr_penawaran');
		$data['detailsum'] 	= array();
		$this->load->view('print2', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Nota Retur.pdf', 'I');
	}

	public function ViewHeader($id)
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$tr_spk = $this->Retur_penjualan_model->get_data('tr_spk_marketing', 'id_spkmarketing', $id);
		$dtspk = $this->db->query("SELECT * FROM dt_spkmarketing WHERE id_spkmarketing = '$id' AND deal = '1' ")->result();
		$penawaran = $this->Retur_penjualan_model->get_data('tr_penawaran');
		$karyawan = $this->Retur_penjualan_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Retur_penjualan_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'tr_spk' => $tr_spk,
			'dtspk' => $dtspk,
			'penawaran' => $penawaran,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('ViewHeader');
	}

	public function EditHeader()
	{
		$id = $this->uri->segment(3);
		$id2 = $this->uri->segment(4);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$tr_spk = $this->Retur_penjualan_model->get_data('tr_spk_marketing', 'id_spkmarketing', $id);
		// $dtspk = $this->Retur_penjualan_model->get_data('dt_spkmarketing',array('id_spkmarketing',$id));
		$dtspk = $this->db->query("SELECT a.*, b.nama, b.maker FROM dt_returpenjualan a
		INNER JOIN ms_inventory_category3 b ON b.id_category3 = a.id_material
		WHERE a.id_retur ='$id2' AND a.deal='1'")->result();



		$penawaran = $this->Retur_penjualan_model->get_data('tr_penawaran');
		$customer = $this->db
			->select('a.id_customer, b.name_customer')
			->from('tr_penawaran a')
			->join('master_customers b', 'a.id_customer=b.id_customer', 'left')
			->where('a.status', 'N')
			->group_by('a.id_customer')
			->order_by('b.name_customer', 'asc')
			->get()
			->result();
		$karyawan = $this->Retur_penjualan_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Retur_penjualan_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'tr_spk' => $tr_spk,
			'dtspk' => $dtspk,
			'penawaran' => $penawaran,
			'customer' => $customer,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Create SPK Marketing Retur');
		$this->template->render('EditHeader');
	}


	public function SaveSpkRetur()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;
		$code = $this->Inventory_4_model->generate_code();
		$no_surat = $this->Inventory_4_model->BuatNomor();
		$this->db->trans_begin();
		$data = [
			'id_spkmarketing'		=> $code,
			'no_surat'				=> $no_surat,
			'tgl_spk_marketing'		=> date('Y-m-d'),
			'id_customer'			=> $post['id_customer'],
			'nama_customer'			=> $post['nama_customer'],
			'no_penawaran'			=> $post['no_penawaran'],
			'no_po'			        => $post['no_po'],
			'sample'			    => $post['sample'],
			'tgl_po'			    => date('Y-m-d', strtotime($post['tgl_po'])),
			'plan_cust'			    => date('Y-m-d', strtotime($post['plan_cust'])),
			'note'			        => $post['note'],
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'tahun'					=> date('Y-m-d'),
			'retur'		            => 'retur'
		];
		//Add Data
		$this->db->insert('tr_spk_marketing_retur', $data);


		$numb1 = 0;
		foreach ($_POST['dp'] as $dp) {
			$numb1++;
			$stokpakai =  array(
				'id_spkmarketing'		=> $code,
				'id_dt_spkmarketing'	=> $code . '-' . $numb1,
				'id_child_penawaran'	=> $dp[id_child_penawaran],
				'id_material'		    => $dp[idmaterial],
				'no_alloy'		        => $dp[noalloy],
				'thickness'		        => $dp[thickness],
				'width'		        	=> $dp[width],
				'harga_penawaran'		=> $dp[hgpenaaran],
				'harga_deal'		    => $dp[hgdeal],
				'qty_produk'			=> $dp[qty],
				'weight'		    	=> $dp[weight],
				'total_weight'		    => $dp[twight],
				'total_harga'		    => $dp[tharga],
				'delivery'		    	=> $dp[ddate],
				'deal'		    		=> $dp[deal],
				'crcl'		    		=> $dp[crcl],
				'keterangan'		    => $dp[keterangan],
				'retur'		            => 'retur',
			);
			$this->db->insert('dt_spkmarketing_retur', $stokpakai);
			$this->db->insert('dt_spkmarketing_loading_retur', $stokpakai);
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item. Thanks ...',
				'code' => $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function delivery_retur()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Retur_penjualan_model->CariSPKRetur();
		$this->template->set('results', $data);
		$this->template->title('Retur Penjualan');
		$this->template->render('delivery_retur');
	}


	public function delivery_order()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');

		$id = $this->uri->segment('3');
		$this->template->page_icon('fa fa-users');
		$dt = $this->db->query("SELECT * FROM dt_spkmarketing WHERE id_spkmarketing = '$id' AND deal=1")->result();
		$hd = $this->db->query("SELECT * FROM tr_spk_marketing WHERE id_spkmarketing = '$id'")->row();
		// print_r($dt);
		// exit;
		$this->template->set('hd', $hd);
		$this->template->set('dt', $dt);
		$this->template->title('Delivery Retur Penjualan');
		$this->template->render('delivery_order');
	}

	public function SaveNewHeader()
	{
		$this->auth->restrict($this->addPermission);
		$post 		= $this->input->post();


		$code 		= $this->Delivery_order_model->generate_code();
		$no_surat 	= $this->Delivery_order_model->BuatNomor();

		$data = [
			'id_delivery_order'		=> $code,
			'no_surat'				=> $no_surat,
			'tgl_delivery_order'	=> $post['tanggal'],
			'id_customer'			=> $post['id_customer'],
			'nama_customer'			=> $post['nama_customer'],
			'no_spk_marketing'		=> $post['no_surat'],
			'reff'		            => $post['reff'],
			'driver'		        => $post['driver'],
			'nopol'		            => $post['nopol'],
			'tanggal'		        => $post['tanggal'],
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'total_item'		    => $post['total_item'],
			'total_berat'		    => $post['total_berat'],
			'tahun'					=> date('Y-m-d'),
			'retur'					=> 'retur'
		];

		$numb1 = 0;
		$ArrDetail =  array();
		$UpdateArr =  array();


		//SYAMSUDIN 09/12/2022
		if (!empty($_POST['hd'])) {
			foreach ($_POST['hd'] as $val1 => $hd) {
				$numb1++;
				$id_dtspk = $hd['id_dtspk'];


				//$this->db->query("UPDATE dt_spkmarketing SET status_do ='CLS' WHERE id ='$id_dtspk'");

			}
		}


		//SYAMSUDIN 19/03/2022
		if (!empty($_POST['dp'])) {
			foreach ($_POST['dp'] as $val => $dt) {
				$numb1++;
				$lotno = $dt['lot'];

				$id_stock = $dt['id_stock'];

				$idstock = $this->db->query("SELECT * FROM stock_material WHERE lotno='$lotno'")->row();

				$ArrDetail[$val . $vaX]['id_delivery_order']		= $code;
				$ArrDetail[$val . $vaX]['id_dt_delivery_order']	= $code . '-' . $numb1;
				$ArrDetail[$val . $vaX]['id_material']		    = $dt['id_material'];
				$ArrDetail[$val . $vaX]['nm_material']		    = $dt['material'];
				$ArrDetail[$val . $vaX]['no_alloy']		        = $dt['no_alloy'];
				$ArrDetail[$val . $vaX]['thickness']		        = $dt['thickness'];
				$ArrDetail[$val . $vaX]['width']		        	= str_replace(',', '', $dt['width']);
				$ArrDetail[$val . $vaX]['length']		        	= str_replace(',', '', $dt['length']);
				$ArrDetail[$val . $vaX]['qty_order']			    = str_replace(',', '', $dt['qty_produk']);

				$ArrDetail[$val . $vaX]['id_stock']			    = $id_stock;
				$ArrDetail[$val . $vaX]['qty']			    	= str_replace(',', '', $dt['qty']);
				$ArrDetail[$val . $vaX]['weight']			    	= str_replace(',', '', $dt['weight']);
				$ArrDetail[$val . $vaX]['qty_mat']			    = str_replace(',', '', $dt['qty_mat']);
				$ArrDetail[$val . $vaX]['weight_mat']			    = str_replace(',', '', $dt['weight_mat']);
				$ArrDetail[$val . $vaX]['remark']			    	= $dt['remarks'];
				$ArrDetail[$val . $vaX]['bantuan']			    = $dt['bantuan'];
				$ArrDetail[$val . $vaX]['id_dt_spkmarketing']	    = $dt['id_dt_spkmarketing'];
				$ArrDetail[$val . $vaX]['lotno']			        = $dt['lot'];
				$ArrDetail[$val . $vaX]['lot_slitting']			= $dt['lot'];
				$ArrDetail[$val . $vaX]['part_number']			= $dt['part_number'];


				$ArrDetail[$val . $vaX]['created_by']			    = $this->auth->user_id();
				$ArrDetail[$val . $vaX]['created_on']			    = date('Y-m-d H:i:s');

				$UpdateArr[$val . $vaX]['id']			    		= $dt['lot'];
				$UpdateArr[$val . $vaX]['qty']			    	= str_replace(',', '', $dt['qty_mat']);
				$UpdateArr[$val . $vaX]['weight']			    	= str_replace(',', '', $dt['weight_mat']);
			}
		}


		$no = 0;
		if (!empty($_POST['dp2'])) {

			foreach ($_POST['dp2'] as $val => $dt2) {

				$no++;
				$numb2 = $numb1 + $no;

				$lotno2 = $dt2['lot'];
				$idstock2 = $this->db->query("SELECT * FROM stock_material WHERE lotno='$lotno2'")->row();

				$ArrDetail2[$val . $vaX]['id_delivery_order']		= $code;
				$ArrDetail2[$val . $vaX]['id_dt_delivery_order']	= $code . '-' . $numb2;
				$ArrDetail2[$val . $vaX]['id_material']		    = $dt2['id_material'];
				$ArrDetail2[$val . $vaX]['nm_material']		    = $dt2['material'];
				$ArrDetail2[$val . $vaX]['no_alloy']		        = $dt2['no_alloy'];
				$ArrDetail2[$val . $vaX]['thickness']		        = $dt2['thickness'];
				$ArrDetail2[$val . $vaX]['width']		        	= str_replace(',', '', $dt2['width']);
				$ArrDetail2[$val . $vaX]['length']		        	= str_replace(',', '', $dt2['length']);
				$ArrDetail2[$val . $vaX]['qty_order']			    = str_replace(',', '', $dt2['qty_produk']);

				$ArrDetail2[$val . $vaX]['id_stock']			    = $idstock2->id_stock;
				$ArrDetail2[$val . $vaX]['qty']			    	= str_replace(',', '', $dt2['qty']);
				$ArrDetail2[$val . $vaX]['weight']			    	= str_replace(',', '', $dt2['weight']);
				$ArrDetail2[$val . $vaX]['qty_mat']			    = str_replace(',', '', $dt2['qty_mat']);
				$ArrDetail2[$val . $vaX]['weight_mat']			    = str_replace(',', '', $dt2['weight_mat']);
				$ArrDetail2[$val . $vaX]['remark']			    	= $dt2['remarks'];
				$ArrDetail2[$val . $vaX]['bantuan']			    = $dt2['bantuan'];
				$ArrDetail2[$val . $vaX]['id_dt_spkmarketing']	    = $dt2['id_dt_spkmarketing'];
				$ArrDetail2[$val . $vaX]['lotno']			        = $dt2['numberlot'];
				$ArrDetail2[$val . $vaX]['lot_slitting']			= $dt2['lot'];


				$ArrDetail2[$val . $vaX]['created_by']			    = $this->auth->user_id();
				$ArrDetail2[$val . $vaX]['created_on']			    = date('Y-m-d H:i:s');

				$UpdateArr[$val . $vaX]['id']			    		= $dt2['lot'];
				$UpdateArr[$val . $vaX]['qty']			    	= str_replace(',', '', $dt2['qty_mat']);
				$UpdateArr[$val . $vaX]['weight']			    	= str_replace(',', '', $dt2['weight_mat']);
			}
		}


		//grouping sum
		$temp = [];
		foreach ($UpdateArr as $val => $value) {
			if (!array_key_exists($value['id'], $temp)) {
				$temp[$value['id']]['qty'] 		= 0;
				$temp[$value['id']]['weight'] 	= 0;
			}
			$temp[$value['id']]['id'] 		= $value['id'];
			$temp[$value['id']]['qty'] 		+= $value['qty'];
			$temp[$value['id']]['weight'] 	+= $value['weight'];
		}

		$ArrStock = array();
		foreach ($temp as $key => $value) {
			$rest_pusat = $this->db->get_where('stock_material', array('id_gudang' => '3', 'id_stock' => $value['id']))->result();

			if (!empty($rest_pusat)) {

				$QTY = $rest_pusat[0]->qty - $value['qty'];
				$TWG = $rest_pusat[0]->totalweight - $value['weight'];
				$SAT = 0;
				if ($QTY > 0 and $TWG > 0) {
					$SAT = $TWG / $QTY;
				}
				$ArrStock[$key]['id_stock'] 	= $value['id'];
				$ArrStock[$key]['qty'] 			= $QTY;
				$ArrStock[$key]['totalweight'] 	= $TWG;
				$ArrStock[$key]['weight'] 		= $SAT;
			}
		}

		// print_r($ArrStock);
		// print_r($data);
		// print_r($ArrDetail);
		// exit;

		$this->db->trans_start();
		$this->db->insert('tr_delivery_order', $data);
		if (!empty($_POST['dp'])) {
			$this->db->insert_batch('dt_delivery_order_child', $ArrDetail);
		}
		if (!empty($_POST['dp2'])) {
			$this->db->insert_batch('dt_delivery_order_child', $ArrDetail2);
		}
		// if(!empty($ArrStock)){
		// 	$this->db->update_batch('stock_material',$ArrStock,'id_stock');
		// }
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'	=> 'Gagal Save Item. Thanks ...',
				'code' 	=> $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'	=> 'Success Save Item. invenThanks ...',
				'code' 	=> $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}


	public function save_jurnal_jv()
	{

		$post        = $this->input->post();
		$session = $this->session->userdata('app_session');
		$data_session	= $this->session->userdata;

		$tgl_inv  = $this->input->post('tgl_jurnal[0]');
		$keterangan  = $this->input->post('keterangan[0]');
		$type        = $this->input->post('type[0]');
		$reff        = $this->input->post('reff[0]');
		$no_req      = $this->input->post('no_request[0]');
		$total       = $this->input->post('total');
		$jenis       = $this->input->post('jenis');
		$tipe_jurnal       = $this->input->post('tipe');
		$jenis_jurnal       = $this->input->post('jenis_jurnal');

		$total_po           = $this->input->post('total_po');
		$id_vendor          = $this->input->post('vendor_id');
		$nama_vendor        = $this->input->post('vendor_nm');

		$this->db->trans_begin();

		$Nomor_JV				= $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


		$Bln 			= substr($tgl_inv, 5, 2);
		$Thn 			= substr($tgl_inv, 0, 4);


		$dataJVhead = array(
			'nomor' 	    	=> $Nomor_JV,
			'tgl'	         	=> $tgl_inv,
			'jml'	            => $total,
			'koreksi_no'		=> '-',
			'kdcab'				=> '101',
			'jenis'			    => 'JV',
			'keterangan' 		=> $keterangan,
			'bulan'				=> $Bln,
			'tahun'				=> $Thn,
			'user_id'			=> $this->auth->user_id(),
			'memo'			    => '',
			'tgl_jvkoreksi'	    => $tgl_inv,
			'ho_valid'			=> ''
		);


		$this->db->insert(DBACC . '.javh', $dataJVhead);



		for ($i = 0; $i < count($this->input->post('type')); $i++) {
			$tipe = $this->input->post('type')[$i];
			$perkiraan = $this->input->post('no_coa')[$i];
			$noreff = $this->input->post('reff')[$i];
			$jenisjurnal = $this->input->post('jenisjurnal')[$i];

			$datadetail = array(
				'tipe'            => $this->input->post('type')[$i],
				'nomor'           => $Nomor_JV,
				'tanggal'         => $this->input->post('tgl_jurnal')[$i],
				'no_perkiraan'    => $this->input->post('no_coa')[$i],
				'keterangan'      => $this->input->post('keterangan')[$i],
				'no_reff'     	  => $this->input->post('reff')[$i],
				'debet'      	  => $this->input->post('debet')[$i],
				'kredit'          => $this->input->post('kredit')[$i]
			);
			$this->db->insert(DBACC . '.jurnal', $datadetail);

			$jurnal_posting	 = "UPDATE jurnal SET stspos=1 WHERE tipe = '$tipe'
			AND  jenis_jurnal = '$jenisjurnal' AND no_reff  = '$noreff' ";
			$this->db->query($jurnal_posting);
		}

		$Qry_Update_Cabang_acc	 = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
		$this->db->query($Qry_Update_Cabang_acc);

		$jurnal_inv	 = "UPDATE tr_delivery_order SET status_jurnal='CLS' WHERE no_invoice = '$reff' ";
		$this->db->query($jurnal_inv);





		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();

			$param = array(
				'save' => 0,
				'msg' => "GAGAL, simpan data..!!!",

			);
		} else {
			$this->db->trans_commit();

			$param = array(
				'save' => 1,
				'msg' => "SUKSES, simpan data..!!!",

			);
		}
		echo json_encode($param);
	}

	public function get_retur_incoming()
	{
		$this->Retur_penjualan_model->get_retur_incoming();
	}
}
