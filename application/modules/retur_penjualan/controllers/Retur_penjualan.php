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
	public function incoming_retur()
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
		$dtspk = $this->db->query("SELECT a.*, b.nama, b.maker, b.id_bentuk, b.total_weight, c.no_surat as no_do FROM stock_material a
		JOIN ms_inventory_category3 b ON b.id_category3 = a.id_category3
		JOIN tr_delivery_order c ON c.id_delivery_order = a.no_kirim
		WHERE a.no_surat ='$spkmkt' ORDER BY c.no_surat ASC")->result();

		$check_sheet = 0;

		$data_weight_per_sheet = [];

		foreach ($dtspk as $item) {
			if ($check_sheet == 0 && $item->id_bentuk == 'B2000002') {
				$check_sheet = 1;

				$data_weight_per_sheet[$item->id_category3] = $item->total_weight;
			}
		}

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
			'check_sheet' => $check_sheet,
			'data_weight_per_sheet' => $data_weight_per_sheet
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

		try {
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
			$insert_header_retur = $this->db->insert('tr_retur_penjualan', $data);
			if (!$insert_header_retur) {
				throw new Exception('Data header retur gagal dibuat !');
			}
			$numb1 = 0;

			$arr_stock_retur = [];
			$detRetur = [];
			foreach ($_POST['dp'] as $dp) {
				$numb1++;

				$deal        = $dp[deal];
				$id_material = $dp[id_category3];
				$idspkmarketing = $post['id_spkmarketing'];
				$idpenawaran  = $post['no_penawaran'];
				$lotno = $dp['lotno'];
				$gudang = $dp['gudang'];

				$ppn       = $this->db->query("SELECT exclude_vat FROM tr_penawaran WHERE no_penawaran='$idpenawaran'")->row();

				$harga       = $this->db->query("SELECT * FROM dt_spkmarketing WHERE id_spkmarketing='$idspkmarketing' AND id_material='$id_material'")->row();
				$hargadeal      = $harga->harga_deal;
				$totalretur     = $dp[total_kirim];
				$totalharga     = $hargadeal * $totalretur;
<<<<<<< HEAD
<<<<<<< HEAD
=======
				if (isset($dp['qty_sheet']) && !empty($dp['qty_sheet'])) {
					$totalharga = ($hargadeal * $dp['qty_sheet']);
				}
>>>>>>> development
=======
				if (isset($dp['qty_sheet']) && !empty($dp['qty_sheet'])) {
					$totalharga = ($hargadeal * $dp['qty_sheet']);
				}
>>>>>>> 1dc243b37598ac4ab7f585bf1418d1729f2a6872

				if ($ppn->exclude_vat != '' || $ppn->exclude_vat != '0') {
					$totalppn   = ($totalharga * $ppn->exclude_vat) / 100;
				} else {
					$totalppn   = 0;
				}

				if ($deal == 1) {
<<<<<<< HEAD
<<<<<<< HEAD
					$detRetur[] =  array(
						'id_retur'				=> $code,
						'id_dt_retur'			=> $code . '-' . $numb1,
						'id_material'		    => $dp[id_category3],
						'thickness'		        => $dp[thickness],
						'width'		        	=> $dp[width],
						'length'		        => $dp[length],
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
					if (isset($dp['qty_sheet'])) {
						$detRetur['total_sheet'] = $dp['qty_sheet'];
					}
=======
					$row = array(
						'id_retur'              => $code,
						'id_dt_retur'           => $code . '-' . $numb1,
						'id_material'           => $dp['id_category3'],
						'thickness'             => $dp['thickness'],
						'width'                 => $dp['width'],
						'length'                => $dp['length'],
						'harga_deal'            => $hargadeal,
						'qty_produk'            => 1,
						'weight'                => $totalretur,
						'total_weight'          => $totalretur,
						'total_harga'           => $totalharga,
						'total_ppn'             => $totalppn,
						'deal'                  => $dp['deal'],
						'created_on'            => date('Y-m-d H:i:s'),
						'created_by'            => $this->auth->user_id(),
						'id_stok'               => $dp['id_stok'],
						'lotno'                 => $dp['lotno'],
						// Tambahkan default value di sini supaya jumlah kolom selalu sama
						'total_sheet'           => (isset($dp['qty_sheet']) && !empty($dp['qty_sheet'])) ? $dp['qty_sheet'] : 0
					);

					$detRetur[] = $row;
>>>>>>> development
=======
					$row = array(
						'id_retur'              => $code,
						'id_dt_retur'           => $code . '-' . $numb1,
						'id_material'           => $dp['id_category3'],
						'thickness'             => $dp['thickness'],
						'width'                 => $dp['width'],
						'length'                => $dp['length'],
						'harga_deal'            => $hargadeal,
						'qty_produk'            => 1,
						'weight'                => $totalretur,
						'total_weight'          => $totalretur,
						'total_harga'           => $totalharga,
						'total_ppn'             => $totalppn,
						'deal'                  => $dp['deal'],
						'created_on'            => date('Y-m-d H:i:s'),
						'created_by'            => $this->auth->user_id(),
						'id_stok'               => $dp['id_stok'],
						'lotno'                 => $dp['lotno'],
						// Tambahkan default value di sini supaya jumlah kolom selalu sama
						'total_sheet'           => (isset($dp['qty_sheet']) && !empty($dp['qty_sheet'])) ? $dp['qty_sheet'] : 0
					);

					$detRetur[] = $row;
>>>>>>> 1dc243b37598ac4ab7f585bf1418d1729f2a6872

					$get_last_stock = $this->Retur_penjualan_model->get_last_stock($lotno);

					$arr_stock_retur[] = [
						'id_category3' => $get_last_stock->id_category3,
						'nama_material' => $get_last_stock->nama_material,
						'width' => $get_last_stock->width,
						'length' => $get_last_stock->length,
						'id_bentuk' => $get_last_stock->id_bentuk,
						'lotno' => $get_last_stock->lotno,
						'qty' => 1,
						'weight' => $totalretur,
						'totalweight' => $totalretur,
						'booking' => 0,
						'thickness' => $get_last_stock->thickness,
						'aktif' => 'Y',
						'id_gudang' => $gudang,
						'created_by' => $this->auth->user_id(),
						'created_on' => date('Y-m-d H:i:s'),
						'lot_slitting' => $get_last_stock->lot_slitting,
						'keterangan' => $get_last_stock->keterangan . ' - RETUR (' . $code . ')',
						'id_roll' => $get_last_stock->id_roll,
						'panjang' => $get_last_stock->panjang,
						'actual_berat' => $get_last_stock->actual_berat,
						'sisa_spk' => $totalretur,
						'no_surat' => $get_last_stock->no_surat,
						'customer' => $get_last_stock->customer,
						'status_do' => 'OPN',
						'costbook' => $get_last_stock->costbook,
						'id_dt_spkmarketing' => $get_last_stock->id_dt_spkmarketing,
						'harga_deal' => $get_last_stock->harga_deal,
						'tipe_material' => $get_last_stock->tipe_material,
						'qty_sheet' => $get_last_stock->qty_sheet
					];
				}
			}

			if (!empty($detRetur)) {
<<<<<<< HEAD
<<<<<<< HEAD
=======
				// throw new Exception(''.print_r($detRetur).'');
>>>>>>> development
=======
				// throw new Exception(''.print_r($detRetur).'');
>>>>>>> 1dc243b37598ac4ab7f585bf1418d1729f2a6872
				$insert_detail_retur = $this->db->insert_batch('dt_returpenjualan', $detRetur);
				if (!$insert_detail_retur) {
					throw new Exception('Data detail retur gagal di input !');
				}
			}

			if (!empty($arr_stock_retur)) {
				$insert_stock_retur = $this->db->insert_batch('stock_material', $arr_stock_retur);
				if (!$insert_stock_retur) {
					throw new Exception('Data stock retur gagal di kembalikan !');
				}
			}

			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. invenThanks ...',
				'code' => $code,
				'status'	=> 1
			);

			echo json_encode($status);
		} catch (Exception $e) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> $e->getMessage(),
				'code' => $code,
				'status'	=> 0
			);

			echo json_encode($status);
		}
	}

	public function list_retur_penjualan()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		// $data = $this->Retur_penjualan_model->CariRetur();
		// $this->template->set('results', $data);
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

		$check_type_sheet = $this->db
			->select('a.*, b.nama AS item')
			->from('dt_returpenjualan a')
			->join('ms_inventory_category3 b', 'b.id_category3=a.id_material')
			->where('a.id_retur', $id)
			->where('b.id_bentuk', 'B2000002')
			->get()
			->num_rows();

		$type_sheet = ($check_type_sheet > 0) ? 1 : 0;


		$data['penawaran'] = $this->Retur_penjualan_model->get_data('tr_penawaran');
		$data['detailsum'] 	= array();
		$data['type_sheet'] = $type_sheet;
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
		$dtspk = $this->db->query("SELECT a.*, b.nama, b.maker, b.id_bentuk FROM dt_returpenjualan a
		INNER JOIN ms_inventory_category3 b ON b.id_category3 = a.id_material
		WHERE a.id_retur ='$id2' AND a.deal='1'")->result();

		$type_sheet = 0;
		foreach ($dtspk as $item_detail) {
			if ($type_sheet == '0' && $item_detail->id_bentuk == 'B2000002') {
				$type_sheet = 1;
			}
		}


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
			'type_sheet' => $type_sheet
		];
		$this->template->set('id', $id);
		$this->template->set('id2', $id2);
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
		$this->db->update('tr_retur_penjualan', ['sts' => '1'], ['id_retur' => $post['id2']]);

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

		$this->db->select('a.*, b.name_customer as name_customer');
		$this->db->from('tr_spk_marketing_retur a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->where('a.sts <>', '1');
		$this->db->or_where('a.sts', null);
		$this->db->order_by('a.id_spkmarketing', 'desc');
		$query = $this->db->get();

		$this->template->set('results', $query->result());
		$this->template->title('Retur Penjualan');
		$this->template->render('delivery_retur');
	}


	public function delivery_order()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');

		$id = $this->uri->segment('3');
		$this->template->page_icon('fa fa-users');
		$dt = $this->db->query("SELECT * FROM dt_spkmarketing_retur WHERE id_spkmarketing = '$id' AND deal=1")->result();
		$hd = $this->db->query("SELECT * FROM tr_spk_marketing_retur WHERE id_spkmarketing = '$id'")->row();
		// print_r($dt);
		// exit;
		$this->template->set('id', $id);
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


				$this->db->query("UPDATE dt_spkmarketing_retur SET status_do ='CLS' WHERE id ='$id_dtspk'");
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
		$this->db->update('tr_spk_marketing_retur', ['sts' => '1'], ['id_spkmarketing' => $post['id']]);
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

	public function get_nota_retur()
	{
		$post = $this->input->post();

		// 1. Ambil data mentah dari Model
		$fetch = $this->Retur_penjualan_model->get_data_nota_retur($post);

		// 2. Ambil total data tanpa filter (untuk recordsTotal)
		$total_data = $this->db->count_all('v_nota_retur');

		$arr_data = [];
		$no = intval($post['start']);

		foreach ($fetch['data'] as $item) {
			$no++;

			// Formatting HTML tetep di Controller/Private Function
			$arr_data[] = [
				'no'            => $no,
				'no_retur'      => $item->no_retur,
				'tanggal_retur' => $item->tgl_retur,
				'customer'      => $item->name_customer,
				'nilai_retur'   => number_format($item->total_nilai_retur), // Hasil dari View
				'action'        => $this->_render_get_nota_retur_action($item)
			];
		}

		$response = [
			'draw'            => intval($post['draw']),
			'recordsTotal'    => $total_data,
			'recordsFiltered' => $fetch['count_filter'],
			'data'            => $arr_data
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function _render_get_nota_retur_action($item)
	{
		$return = '';

		if (has_permission($this->viewPermission)) {
			$return .= ' <a class="btn btn-success btn-sm" href="' . base_url('/retur_penjualan/PrintH2/' . $item->id_retur) . '" target="_blank" title="Print"><i class="fa fa-print"></i></a>';
		}

		if (has_permission($this->managePermission)) {
			$return .= ' <a class="btn btn-info btn-sm" href="' . base_url('/retur_penjualan/editHeader/' . $item->id_spkmarketing . '/' . $item->id_retur) . '" title="Create SPK Marketing"><i class="fa fa-edit">&nbsp;</i></i></a></a>';
		}

		return $return;
	}

	public function get_dat_delivery_retur()
	{
		$draw   = $this->input->post('draw', true);
		$length = $this->input->post('length', true);
		$start  = $this->input->post('start', true);
		$search = $this->input->post('search', true)['value'];

		// Panggil Model
		$get_data     = $this->Retur_penjualan_model->get_datatables_retur($length, $start, $search);
		$count_all    = $this->Retur_penjualan_model->count_all_retur(); // Buat fungsi count sederhana di model
		$count_filter = $this->Retur_penjualan_model->count_filtered_retur($search);

		$arr_data = [];
		foreach ($get_data as $item) {
			$arr_data[] = [
				'no'                 => ++$start,
				'tanggal_spk_terbit' => date('d F Y', strtotime($item->tgl_spk_marketing)),
				'no_spk'             => $item->no_surat,
				'customer'           => $item->name_customer,
				'nilai_spk'          => (float)$item->nilai_spk,
				'action'             => $this->_render_actions_delivery_ret($item)
			];
		}

		echo json_encode([
			'draw'            => intval($draw),
			'recordsTotal'    => $count_all,
			'recordsFiltered' => $count_filter,
			'data'            => $arr_data
		]);
	}

	public function _render_actions_delivery_ret($item)
	{
		$buttons = '';
		if (has_permission($this->managePermission)) {
			$buttons .= ' <a class="btn btn-info btn-sm" href="' . base_url('/retur_penjualan/delivery_order/' . $item->id_spkmarketing) . '" title="Edit"><i class="fa fa-edit">&nbsp;</i></i></a></a>';
		}

		return $buttons;
	}

	public function update_retur()
	{
		try {
			$this->db->trans_begin();

			$this->db->select('id, total_ppn, total_harga, total_sheet, harga_deal');
			$this->db->from('dt_returpenjualan');
			$this->db->where('total_sheet >', 0);
			$get_data = $this->db->get()->result();

			if (empty($get_data)) {
				throw new Exception('Data detail retur tidak ditemukan.');
			}

			$arr_update = [];

			foreach ($get_data as $item) {
				// Safety check: Hindari pembagian dengan nol
				$total_harga_lama = (float) $item->total_harga;
				$persen_ppn = 0;

				if ($total_harga_lama > 0) {
					$persen_ppn = ($item->total_ppn / $total_harga_lama * 100);
				}

				$total_harga_baru = ($item->total_sheet * $item->harga_deal);
				$total_ppn_baru   = ($total_harga_baru * $persen_ppn / 100);

				$arr_update[] = [
					'id'          => $item->id,
					'total_harga' => $total_harga_baru,
					'total_ppn'   => $total_ppn_baru
				];
			}

			if (!empty($arr_update)) {
				$this->db->update_batch('dt_returpenjualan', $arr_update, 'id');

				// Cek status transaksi database
				if ($this->db->trans_status() === FALSE) {
					$db_error = $this->db->error();
					throw new Exception('Database Error: ' . $db_error['message']);
				}

				$this->db->trans_commit();
				echo json_encode([
					'status'  => 1,
					'message' => 'Berhasil memperbarui ' . count($arr_update) . ' data.'
				]);
			} else {
				throw new Exception('Tidak ada data yang perlu diupdate.');
			}
		} catch (Exception $e) {
			$this->db->trans_rollback();

			// Return pesan error yang jelas
			echo json_encode([
				'status'  => 0,
				'message' => 'Update Gagal: ' . $e->getMessage()
			]);
		}
	}
}
