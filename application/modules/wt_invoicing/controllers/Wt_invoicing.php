<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/*
* @author Syamsudin
* @Copyright (c) 2022, Syamsudin
*
* This is controller for Wt_penawaran
*/

class Wt_invoicing extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Invoicing.View';
	protected $addPermission  	= 'Invoicing.Add';
	protected $managePermission = 'Invoicing.Manage';
	protected $deletePermission = 'Invoicing.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Spk_marketing/Inventory_4_model',
			'Delivery_order/Delivery_order_model',
			'Wt_invoicing/Wt_invoicing_model',
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
		// $data = $this->Wt_invoicing_model->CariInvoice();
		// $this->template->set('results', $data);
		$this->template->title('Invoice');
		$this->template->render('index_invoice');
	}

	public function index_proforma()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->CariProformaInvoice();
		$this->template->set('results', $data);
		$this->template->title('Proforma Invoice');
		$this->template->render('index_proforma_invoice');
	}

	public function spk_marketing()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');

		$this->template->title('Invoicing');
		$this->template->render('index_spk_marketing');
	}

	public function delivery_order()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Delivery_order_model->CariDOopen();
		$this->template->set('results', $data);
		$this->template->title('Invoicing');
		$this->template->render('index_delivery_order');
	}

	public function PrintHeader()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Delivery_order_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Delivery_order_model->PrintDetail($id);
		$this->load->view('PrintHeader2', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Delivery_order.pdf', 'I');
	}

	public function PrintHeaderSlitting()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Delivery_order_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Delivery_order_model->PrintDetail($id);
		$data['detail2']  = $this->Delivery_order_model->PrintDetail2($id);
		$this->load->view('PrintHeader2Slitting', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Delivery_order_Slitting.pdf', 'I');
	}

	public function index_monitoring()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->CariInvoiceDeal();
		$this->template->set('results', $data);
		$this->template->title('Monitoring Invoice');
		$this->template->render('index_monitoring');
	}

	public function index_close()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->CariInvoiceClose();
		$this->template->set('results', $data);
		$this->template->title('Close Invoice');
		$this->template->render('index_close');
	}
	public function plan_tagih()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->cariPlantagih();
		$this->template->set('results', $data);
		$this->template->title('Planning Tagih');
		$this->template->render('index_planning');
	}
	public function previewInvoice($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$plan      = $this->db->query("SELECT * FROM wt_plan_tagih WHERE id_plan_tagih='$id'")->row();
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_sales_order', 'no_so', $plan->no_so);
		$alamat    = $this->Wt_invoicing_model->getAlamatSO($plan->no_so);
		$detail    = $this->Wt_invoicing_model->get_data('tr_sales_order_detail', 'no_so', $plan->no_so);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'plan' => $plan,
			'header' => $header,
			'detail' => $detail,
			'alamat' => $alamat,
		];

		$this->template->set('results', $data);
		$this->template->title('Create Invoice');
		$this->template->render('preview_invoice');
	}

	public function createInvoice($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		//$plan      = $this->db->query("SELECT * FROM tr_spk_marketing WHERE id_spkmarketing ='$id'")->row();
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_spk_marketing', 'id_spkmarketing', $id);
		$detail    = $this->Wt_invoicing_model->CariSPK($id);
		//$alamat    = $this->Wt_invoicing_model->getAlamatSO($plan->no_so);
		$hdso      = $this->Wt_invoicing_model->get_data('tr_spk_marketing', 'id_spkmarketing', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'plan' => $plan,
			'header' => $header,
			'detail' => $detail,
			//'alamat'=>$alamat,
			'top' => $top,
			'headerso' => $hdso,
		];

		$this->template->set('results', $data);
		$this->template->title('Create Invoice');
		$this->template->render('create_invoice');
	}

	public function createProformaInvoice($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		//$plan      = $this->db->query("SELECT * FROM tr_spk_marketing WHERE id_spkmarketing ='$id'")->row();
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_spk_marketing', 'id_spkmarketing', $id);
		$detail    = $this->Wt_invoicing_model->CariSPK($id);
		//$alamat    = $this->Wt_invoicing_model->getAlamatSO($plan->no_so);
		$hdso      = $this->Wt_invoicing_model->get_data('tr_spk_marketing', 'id_spkmarketing', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'plan' => $plan,
			'header' => $header,
			'detail' => $detail,
			//'alamat'=>$alamat,
			'top' => $top,
			'headerso' => $hdso,
		];



		$this->template->set('results', $data);
		$this->template->title('Create Proforma Invoice');
		$this->template->render('create_invoice_proforma');
	}

	public function createInvoiceDo($iddo)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$do      = $this->db->query("SELECT * FROM tr_delivery_order WHERE id_delivery_order ='$iddo'")->row();
		$id      = $do->no_spk_marketing;
		$idspk      = $this->db->query("SELECT * FROM tr_spk_marketing WHERE no_surat ='$id'")->row();


		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_spk_marketing', 'no_surat', $id);
		$detail    = $this->Wt_invoicing_model->CariSPKdo($iddo);
		//$alamat    = $this->Wt_invoicing_model->getAlamatSO($plan->no_so);
		$hdso      = $this->Wt_invoicing_model->get_data('tr_spk_marketing', 'no_surat', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'plan' => $plan,
			'header' => $header,
			'detail' => $detail,
			'do' => $do,
			'top' => $top,
			'headerso' => $hdso,
		];



		$this->template->set('results', $data);
		$this->template->title('Create Invoice');
		$this->template->render('create_invoice_do');
	}


	public function createInvoiceDoSlitting($iddo)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$do      = $this->db->query("SELECT * FROM tr_delivery_order WHERE id_delivery_order ='$iddo'")->row();
		$id      = $do->no_spk_marketing;
		$idspk      = $this->db->query("SELECT * FROM tr_spk_marketing WHERE no_surat ='$id'")->row();


		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_spk_marketing', 'no_surat', $id);
		$detail    = $this->Wt_invoicing_model->CariSPKdo($iddo);
		$detail2    = $this->Wt_invoicing_model->CariSPKdoscrap($iddo);

		// print_r($detail2->total_kirim);
		// exit;
		//$alamat    = $this->Wt_invoicing_model->getAlamatSO($plan->no_so);
		$hdso      = $this->Wt_invoicing_model->get_data('tr_spk_marketing', 'no_surat', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'plan' => $plan,
			'header' => $header,
			'detail' => $detail,
			'detail2' => $detail2,
			'do' => $do,
			'top' => $top,
			'headerso' => $hdso,
		];



		$this->template->set('results', $data);
		$this->template->title('Create Invoice Slitting');
		$this->template->render('create_invoice_do_slitting');
	}

	public function FollowUp($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');

		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_invoice', 'no_invoice', $id);
		$detail    = $this->Wt_invoicing_model->get_data('tr_invoice_detail', 'no_invoice', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'header' => $header,
			'detail' => $detail,
		];

		$this->template->set('results', $data);
		$this->template->title('Followup');
		$this->template->render('createFollowup');
	}


	public function createDealInvoice($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');


		$aktif = 'active';
		$deleted = '0';
		$plan      = $this->db->query("SELECT * FROM tr_invoice WHERE id_invoice='$id'")->row();
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_invoice', 'id_invoice', $id);
		$detail    = $this->Wt_invoicing_model->get_data('tr_invoice_detail', 'id_invoice', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'inv' => $plan,
			'header' => $header,
			'detail' => $detail,
		];

		$this->template->set('results', $data);
		$this->template->title('Create Invoice');
		$this->template->render('deal_invoice');
	}


	function GetProduk()
	{
		$loop = $_GET['jumlah'] + 1;

		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);


		$material = $this->db->query("SELECT a.*, b.nama as nama_produk, b.kode_barang, c.nama_category2 as nama_formula FROM ms_product_pricelist as a 
										INNER JOIN ms_inventory_category3 b on b.id_category3=a.id_category3
										INNER JOIN ms_product_costing c on c.id_category2 = a.id_formula
										")->result();



		echo "
		<tr id='tr_$loop'>
			<td>$loop</td>
			<td>
				<select id='used_no_surat_$loop' name='dt[$loop][no_surat]' data-no='$loop' onchange='CariDetail($loop)' class='form-control select' required>
					<option value=''>-Pilih-</option>";
		foreach ($material as $produk) {
			echo "<option value='$produk->id_category3'>$produk->nama_formula|$produk->nama_produk|$produk->kode_barang</option>";
		}
		echo	"</select>
			</td>
			<td id='nama_produk_$loop' hidden><input type='text' class='form-control input-sm' readonly id='used_nama_produk_$loop' required name='dt[$loop][nama_produk]'></td>
			<td id='date_$loop'><input type='date' class='form-control input-sm' id='used_date_$loop' required name='dt[$loop][date]'></td>
			<td id='qty_so_$loop'><input type='text' class='form-control input-sm' id='used_qty_so_$loop' required name='dt[$loop][qty_so]' onblur='HitungTotal($loop)'></td>
			<td id='qty_$loop'><input type='text' class='form-control input-sm' id='used_qty_$loop' required name='dt[$loop][qty]' onblur='HitungTotal($loop)'></td>
			<td id='harga_satuan_$loop'><input type='text' class='form-control input-sm' id='used_harga_satuan_$loop' required name='dt[$loop][harga_satuan]'></td>
			<td id='stok_tersedia_$loop'><input type='text' class='form-control input-sm' id='used_stok_tersedia_$loop' required name='dt[$loop][stok_tersedia]' onblur='HitungLoss($loop)'></td>
			<td id='potensial_loss_$loop'><input type='text' class='form-control input-sm' id='used_potensial_loss_$loop' required name='dt[$loop][potensial_loss]' readonly></td>
			<td id='diskon_$loop'><input type='text' class='form-control'  id='used_diskon_$loop' required name='dt[$loop][diskon]' onblur='HitungTotal($loop)'></td>
			<td id='nilai_diskon_$loop' hidden><input type='text' class='form-control'  id='used_nilai_diskon_$loop' required name='dt[$loop][nilai_diskon]'></td>
			<td id='freight_cost_$loop'><input type='text' class='form-control input-sm' id='used_freight_cost_$loop' value='0' required name='dt[$loop][freight_cost]' onblur='Freight($loop)'></td>
			<td id='total_harga_$loop'><input type='text' class='form-control input-sm total' id='used_total_harga_$loop' required name='dt[$loop][total_harga]' readonly></td>
			<td align='center'>
				<button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return HapusItem($loop);'><i class='fa fa-close'></i></button>
			</td>
			
		</tr>
		";
	}

	public function SaveNewInvoice()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;

		$id = $this->Wt_invoicing_model->generate_id();
		$code = $this->Wt_invoicing_model->generate_code();
		$no_surat = $this->Wt_invoicing_model->BuatNomor($post['no_do']);

		$this->db->trans_begin();

		$config['upload_path'] = './assets/file_po/'; //path folder
		$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config);
		if ($this->upload->do_upload('upload_po')) {
			$gbr = $this->upload->data();
			//Compress Image
			$config['image_library'] = 'gd2';
			$config['source_image'] = './assets/file_po/' . $gbr['file_name'];
			$config['create_thumb'] = FALSE;
			$config['maintain_ratio'] = FALSE;
			$config['umum'] = '50%';
			$config['width'] = 260;
			$config['height'] = 350;
			$config['new_image'] = './assets/file_po/' . $gbr['file_name'];
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			$gambar  = $gbr['file_name'];
			$type    = $gbr['file_type'];
			$ukuran  = $gbr['file_size'];
			$ext1    = explode('.', $gambar);
			$ext     = $ext1[1];
			$lokasi = './assets/file_po/' . $gbr['file_name'];
		}

		// print_r($lokasi);
		// exit;

		$config1['upload_path'] = './assets/file_do/'; //path folder
		$config1['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config1['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config1);
		if ($this->upload->do_upload('upload_so')) {
			$gbr2 = $this->upload->data();
			//Compress Image
			$config1['image_library'] = 'gd2';
			$config1['source_image'] = './assets/file_do/' . $gbr2['file_name'];
			$config1['create_thumb'] = FALSE;
			$config1['maintain_ratio'] = FALSE;
			$config1['umum'] = '50%';
			$config1['width'] = 260;
			$config1['height'] = 350;
			$config1['new_image'] = './assets/file_do/' . $gbr2['file_name'];
			$this->load->library('image_lib', $config1);
			$this->image_lib->resize();

			$gambar1  = $gbr2['file_name'];
			$type1    = $gbr2['file_type'];
			$ukuran1  = $gbr2['file_size'];
			$ext2    = explode('.', $gambar1);
			$ext3     = $ext2[1];
			$lokasi2 = './assets/file_do/' . $gbr2['file_name'];
		}

		$data = [
			'no_invoice'		 	=> $code,
			'no_surat'				=> $no_surat,
			'tgl_invoice'			=> $post['tanggal'],
			'no_so'		    		=> $post['no_so'],
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'nilai_produk'			=> str_replace(',', '', $post['totalproduk']),
			'persentase'			=> str_replace(',', '', $post['persentase']),
			'nilai_invoice'			=> str_replace(',', '', $post['nilai_tagih']),
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal']),
			'upload_po'				=> $lokasi,
			'upload_so'				=> $lokasi2,
			'referensi'				=> $post['reff'],
			'no_faktur'				=> $post['faktur'],
			'jatuh_tempo'			=> $post['jatuh_tempo'],
			'id_plan_tagih'         => $post['id_plan_tagih'],
			'payment'				=> $post['pembayaran'],
			'keterangan_top'        => $post['keterangan_top'],
			'id_invoice'		 	=> $id,
			'alamat'                => $post['alamat'],
			'sisa_invoice_idr'      => str_replace(',', '', $post['nilai_tagih']),
			'note'              	=> $post['note'],
			'total'					=> str_replace(',', '', $post['total']),
			'diskon'				=> str_replace(',', '', $post['diskon']),
			'dpp'					=> str_replace(',', '', $post['dpp']),
			'tgl_do'				=> $post['tgl_do'],
			'no_do'		    		=> $post['no_do'],
			'tgl_po'				=> $post['tgl_po'],
			'no_po'		    		=> $post['no_po'],
			'id_do'		    		=> $post['id_do'],
			'type'		    		=> $post['tipe'],
			'tahun'					=> date('Y-m-d'),
		];
		//Add Data
		$this->db->insert('tr_invoice', $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[id_material])) {
				$numb1++;
				$dt[] =  array(

					'id_penawaran_detail' => $used[id_penawaran],
					'no_penawaran'		=> $post['no_penawaran'],
					'no_so'				=> $post['no_so'],
					'no_invoice'			=> $code,
					'id_category3'		=> $used[id_material],
					'nama_produk'	    => $used[nama_produk],
					'qty_invoice'	    => $used[qty_so],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'		=> $used[potensial_loss],
					'diskon'		        => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    =>  str_replace(',', '', $used[total_harga]),
					'tgl_delivery'	    => $used[tgl_delivery],
					'created_on'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $this->auth->user_id(),
					'nilai_diskon'		=> str_replace(',', '', $used[nilai_diskon]),
					'id_invoice'		 	=> $id,
					'original_size'		=> str_replace(',', '', $used[original_size]),
					'tobe_size'	        =>  str_replace(',', '', $used[tobe_size]),
				);
			}
		}
		//    print_r($dt);
		//    exit();
		$this->db->insert_batch('tr_invoice_detail', $dt);

		$percent = str_replace(',', '', $post['persentase']);
		$total	= str_replace(',', '', $post['nilai_tagih']);
		$dpp	= str_replace(',', '', $post['dpp']);
		$no_so	= $post['no_so'];
		$Qry_Update_SO	 = "UPDATE tr_spk_marketing SET percent_invoice=percent_invoice + $percent,  
			total_invoice=total_invoice + $dpp  WHERE id_spkmarketing='$no_so'";
		$this->db->query($Qry_Update_SO);

		$dataupdate = [
			'status_invoice'				=> 'CLS',
			'nilai_invoice'				    =>  $dpp,
		];
		//Edit Data
		$this->db->where('id_delivery_order', $post['id_do'])->update("tr_delivery_order", $dataupdate);

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



	public function SaveNewProformaInvoice()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;
		$id = $this->Wt_invoicing_model->generate_id_proforma();
		$code = $this->Wt_invoicing_model->generate_code_proforma();
		$no_surat = $this->Wt_invoicing_model->BuatNomorProforma();

		$this->db->trans_begin();

		$config['upload_path'] = './assets/file_po/'; //path folder
		$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config);
		if ($this->upload->do_upload('upload_po')) {
			$gbr = $this->upload->data();
			//Compress Image
			$config['image_library'] = 'gd2';
			$config['source_image'] = './assets/file_po/' . $gbr['file_name'];
			$config['create_thumb'] = FALSE;
			$config['maintain_ratio'] = FALSE;
			$config['umum'] = '50%';
			$config['width'] = 260;
			$config['height'] = 350;
			$config['new_image'] = './assets/file_po/' . $gbr['file_name'];
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			$gambar  = $gbr['file_name'];
			$type    = $gbr['file_type'];
			$ukuran  = $gbr['file_size'];
			$ext1    = explode('.', $gambar);
			$ext     = $ext1[1];
			$lokasi = './assets/file_po/' . $gbr['file_name'];
		}

		// print_r($lokasi);
		// exit;

		$config1['upload_path'] = './assets/file_do/'; //path folder
		$config1['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config1['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config1);
		if ($this->upload->do_upload('upload_so')) {
			$gbr2 = $this->upload->data();
			//Compress Image
			$config1['image_library'] = 'gd2';
			$config1['source_image'] = './assets/file_do/' . $gbr2['file_name'];
			$config1['create_thumb'] = FALSE;
			$config1['maintain_ratio'] = FALSE;
			$config1['umum'] = '50%';
			$config1['width'] = 260;
			$config1['height'] = 350;
			$config1['new_image'] = './assets/file_do/' . $gbr2['file_name'];
			$this->load->library('image_lib', $config1);
			$this->image_lib->resize();

			$gambar1  = $gbr2['file_name'];
			$type1    = $gbr2['file_type'];
			$ukuran1  = $gbr2['file_size'];
			$ext2    = explode('.', $gambar1);
			$ext3     = $ext2[1];
			$lokasi2 = './assets/file_do/' . $gbr2['file_name'];
		}

		$data = [
			'no_invoice'		 	=> $code,
			'no_surat'				=> $no_surat,
			'tgl_invoice'			=> $post['tanggal'],
			'no_so'		    		=> $post['no_so'],
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'nilai_produk'			=> str_replace(',', '', $post['totalproduk']),
			'persentase'			=> str_replace(',', '', $post['persentase']),
			'nilai_invoice'			=> str_replace(',', '', $post['nilai_tagih']),
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal']),
			'upload_po'				=> $lokasi,
			'upload_so'				=> $lokasi2,
			'referensi'				=> $post['reff'],
			'no_faktur'				=> $post['faktur'],
			'jatuh_tempo'			=> $post['jatuh_tempo'],
			'id_plan_tagih'         => $post['id_plan_tagih'],
			'payment'				=> $post['pembayaran'],
			'keterangan_top'        => $post['keterangan_top'],
			'id_invoice'		 	=> $id,
			'alamat'                => $post['alamat'],
			'sisa_invoice_idr'      => str_replace(',', '', $post['nilai_tagih']),
			'note'              	=> $post['note'],
			'total'					=> str_replace(',', '', $post['total']),
			'diskon'				=> str_replace(',', '', $post['diskon']),
			'dpp'					=> str_replace(',', '', $post['dpp']),
			'tgl_do'				=> $post['tgl_do'],
			'no_do'		    		=> $post['no_do'],
			'tgl_po'				=> $post['tgl_po'],
			'no_po'		    		=> $post['no_po'],
			'id_do'		    		=> $post['id_do'],
			'tahun'					=> date('Y-m-d'),
		];
		//Add Data
		$this->db->insert('tr_invoice_proforma', $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[id_material])) {
				$numb1++;
				$dt[] =  array(

					'id_penawaran_detail' => $used[id_penawaran],
					'no_penawaran'		=> $post['no_penawaran'],
					'no_so'				=> $post['no_so'],
					'no_invoice'			=> $code,
					'id_category3'		=> $used[id_material],
					'nama_produk'	    => $used[nama_produk],
					'qty_invoice'	    => $used[qty_so],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'		=> $used[potensial_loss],
					'diskon'		        => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    =>  str_replace(',', '', $used[total_harga]),
					'tgl_delivery'	    => $used[tgl_delivery],
					'created_on'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $this->auth->user_id(),
					'nilai_diskon'		=> str_replace(',', '', $used[nilai_diskon]),
					'id_invoice'		 	=> $id,
					'original_size'		=> str_replace(',', '', $used[original_size]),
					'tobe_size'	        =>  str_replace(',', '', $used[tobe_size]),
				);
			}
		}
		//    print_r($dt);
		//    exit();
		$this->db->insert_batch('tr_invoice_detail_proforma', $dt);

		// $percent =str_replace(',','',$post['persentase']);
		// $total	=str_replace(',','',$post['nilai_tagih']);
		// $no_so	=$post['no_so'];
		// $Qry_Update_SO	 = "UPDATE tr_spk_marketing SET percent_invoice=percent_invoice + $percent,  
		// total_invoice=total_invoice + $total  WHERE id_spkmarketing='$no_so'";
		// $this->db->query($Qry_Update_SO);

		// $dataupdate = [
		// 'status_invoice'				=> 'CLS',
		// 'nilai_invoice'				    =>  $total,			
		// ];
		//Edit Data
		// $this->db->where('id_delivery_order', $post['id_do'])->update("tr_delivery_order",$dataupdate);		

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


	public function SavePreviewInvoice()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;
		$id = $this->Wt_invoicing_model->generate_id();
		$code = $this->Wt_invoicing_model->generate_code();
		$no_surat = $this->Wt_invoicing_model->BuatNomor();
		$this->db->trans_begin();

		$config['upload_path'] = './assets/file_po/'; //path folder
		$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config);
		if ($this->upload->do_upload('upload_po')) {
			$gbr = $this->upload->data();
			//Compress Image
			$config['image_library'] = 'gd2';
			$config['source_image'] = './assets/file_po/' . $gbr['file_name'];
			$config['create_thumb'] = FALSE;
			$config['maintain_ratio'] = FALSE;
			$config['umum'] = '50%';
			$config['width'] = 260;
			$config['height'] = 350;
			$config['new_image'] = './assets/file_po/' . $gbr['file_name'];
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			$gambar  = $gbr['file_name'];
			$type    = $gbr['file_type'];
			$ukuran  = $gbr['file_size'];
			$ext1    = explode('.', $gambar);
			$ext     = $ext1[1];
			$lokasi = './assets/file_po/' . $gbr['file_name'];
		}

		// print_r($lokasi);
		// exit;

		$config1['upload_path'] = './assets/file_do/'; //path folder
		$config1['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config1['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config1);
		if ($this->upload->do_upload('upload_so')) {
			$gbr2 = $this->upload->data();
			//Compress Image
			$config1['image_library'] = 'gd2';
			$config1['source_image'] = './assets/file_do/' . $gbr2['file_name'];
			$config1['create_thumb'] = FALSE;
			$config1['maintain_ratio'] = FALSE;
			$config1['umum'] = '50%';
			$config1['width'] = 260;
			$config1['height'] = 350;
			$config1['new_image'] = './assets/file_do/' . $gbr2['file_name'];
			$this->load->library('image_lib', $config1);
			$this->image_lib->resize();

			$gambar1  = $gbr2['file_name'];
			$type1    = $gbr2['file_type'];
			$ukuran1  = $gbr2['file_size'];
			$ext2    = explode('.', $gambar1);
			$ext3     = $ext2[1];
			$lokasi2 = './assets/file_do/' . $gbr2['file_name'];
		}

		$data = [
			'no_invoice'		 	=> $code,
			'no_surat'				=> $no_surat,
			'tgl_invoice'			=> $post['tanggal'],
			'no_so'		    		=> $post['no_so'],
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'nilai_produk'			=> str_replace(',', '', $post['totalproduk']),
			'persentase'			=> str_replace(',', '', $post['persentase']),
			'nilai_invoice'			=> str_replace(',', '', $post['nilai_tagih']),
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal']),
			'upload_po'				=> $lokasi,
			'upload_so'				=> $lokasi2,
			'referensi'				=> $post['reff'],
			'no_faktur'				=> $post['faktur'],
			'jatuh_tempo'			=> $post['jatuh_tempo'],
			'id_plan_tagih'         => $post['id_plan_tagih'],
			'payment'				=> $post['pembayaran'],
			'keterangan_top'        => $post['keterangan_top'],
			'id_invoice'		 	=> $id,
			'alamat'                => $post['alamat'],
			'total'					=> str_replace(',', '', $post['total']),
			'diskon'				=> str_replace(',', '', $post['diskon']),
			'dpp'					=> str_replace(',', '', $post['dpp']),
			'note'              	=> $post['note'],

		];
		//Add Data
		$this->db->truncate('tr_invoice_preview');
		$this->db->insert('tr_invoice_preview', $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[no_surat])) {
				$numb1++;
				$dt[] =  array(

					'id_penawaran_detail' => $used[id_penawaran],
					'no_penawaran'		=> $post['no_penawaran'],
					'no_so'				=> $post['no_so'],
					'no_invoice'			=> $code,
					'id_category3'		=> $used[no_surat],
					'nama_produk'	    => $used[nama_produk],
					'qty_invoice'	    => $used[qty_so],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'		=> $used[potensial_loss],
					'diskon'		        => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    =>  str_replace(',', '', $used[total_harga]),
					'tgl_delivery'	    => $used[tgl_delivery],
					'created_on'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $this->auth->user_id(),
					'nilai_diskon'		=> str_replace(',', '', $used[nilai_diskon]),
					'id_invoice'		 	=> $id
				);
			}
		}


		$this->db->truncate('tr_invoice_detail_preview');
		$this->db->insert_batch('tr_invoice_detail_preview', $dt);

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


	public function SaveNewProformaInvoice2()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;


		$code = $this->Wt_invoicing_model->generate_id();
		$no_surat = $this->Wt_invoicing_model->BuatNomorProforma();
		$this->db->trans_begin();

		$config['upload_path'] = './assets/file_po/'; //path folder
		$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config);
		if ($this->upload->do_upload('upload_po')) {
			$gbr = $this->upload->data();
			//Compress Image
			$config['image_library'] = 'gd2';
			$config['source_image'] = './assets/file_po/' . $gbr['file_name'];
			$config['create_thumb'] = FALSE;
			$config['maintain_ratio'] = FALSE;
			$config['umum'] = '50%';
			$config['width'] = 260;
			$config['height'] = 350;
			$config['new_image'] = './assets/file_po/' . $gbr['file_name'];
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			$gambar  = $gbr['file_name'];
			$type    = $gbr['file_type'];
			$ukuran  = $gbr['file_size'];
			$ext1    = explode('.', $gambar);
			$ext     = $ext1[1];
			$lokasi = './assets/file_po/' . $gbr['file_name'];
		}

		// print_r($lokasi);
		// exit;

		$config1['upload_path'] = './assets/file_do/'; //path folder
		$config1['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config1['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config1);
		if ($this->upload->do_upload('upload_so')) {
			$gbr2 = $this->upload->data();
			//Compress Image
			$config1['image_library'] = 'gd2';
			$config1['source_image'] = './assets/file_do/' . $gbr2['file_name'];
			$config1['create_thumb'] = FALSE;
			$config1['maintain_ratio'] = FALSE;
			$config1['umum'] = '50%';
			$config1['width'] = 260;
			$config1['height'] = 350;
			$config1['new_image'] = './assets/file_do/' . $gbr2['file_name'];
			$this->load->library('image_lib', $config1);
			$this->image_lib->resize();

			$gambar1  = $gbr2['file_name'];
			$type1    = $gbr2['file_type'];
			$ukuran1  = $gbr2['file_size'];
			$ext2    = explode('.', $gambar1);
			$ext3     = $ext2[1];
			$lokasi2 = './assets/file_do/' . $gbr2['file_name'];
		}

		$data = [
			'id_invoice'		    		=> $code,
			'no_proforma_invoice'	=> $no_surat,
			'tgl_invoice'			=> $post['tanggal'],
			'no_so'		    		=> $post['no_so'],
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'nilai_produk'			=> str_replace(',', '', $post['totalproduk']),
			'persentase'			=> str_replace(',', '', $post['persentase']),
			'nilai_invoice'			=> str_replace(',', '', $post['nilai_tagih']),
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal']),
			'upload_po'				=> $lokasi,
			'upload_so'				=> $lokasi2,
			'referensi'				=> $post['reff'],
			'no_faktur'				=> $post['faktur'],
			'jatuh_tempo'			=> $post['jatuh_tempo'],
			'id_plan_tagih'           => $post['id_plan_tagih'],
			'payment'				=> $post['pembayaran'],
			'keterangan_top'           => $post['keterangan_top'],

		];
		//Add Data
		$this->db->insert('tr_invoice', $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[no_surat])) {
				$numb1++;
				$dt[] =  array(

					'id_penawaran_detail' => $used[id_penawaran],
					'no_penawaran'		=> $post['no_penawaran'],
					'id_category3'		=> $used[no_surat],
					'nama_produk'	    => $used[nama_produk],
					'qty_invoice'	    => $used[qty_so],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'		=> $used[potensial_loss],
					'diskon'		        => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    =>  str_replace(',', '', $used[total_harga]),
					'tgl_delivery'	    => $used[date],
					'created_on'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $this->auth->user_id(),
					'nilai_diskon'		=> str_replace(',', '', $used[nilai_diskon]),
					'id_invoice'		    => $code
				);
			}
		}
		//    print_r($dt);
		//    exit();
		$this->db->insert_batch('tr_invoice_detail', $dt);


		// $data = [
		// 	'status_so'				=> 1,				
		// 	];
		// 	//Edit Data
		// 	  $this->db->where('no_penawaran', $post['no_penawaran'])->update("tr_penawaran",$data);		

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


	public function SaveNewDealInvoice()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;
		$id = $post['id_invoice'];
		$code = $this->Wt_invoicing_model->generate_code();
		$no_surat = $this->Wt_invoicing_model->BuatNomor();
		$this->db->trans_begin();

		$config['upload_path'] = './assets/file_po/'; //path folder
		$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config);
		if ($this->upload->do_upload('upload_po')) {
			$gbr = $this->upload->data();
			//Compress Image
			$config['image_library'] = 'gd2';
			$config['source_image'] = './assets/file_po/' . $gbr['file_name'];
			$config['create_thumb'] = FALSE;
			$config['maintain_ratio'] = FALSE;
			$config['umum'] = '50%';
			$config['width'] = 260;
			$config['height'] = 350;
			$config['new_image'] = './assets/file_po/' . $gbr['file_name'];
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			$gambar  = $gbr['file_name'];
			$type    = $gbr['file_type'];
			$ukuran  = $gbr['file_size'];
			$ext1    = explode('.', $gambar);
			$ext     = $ext1[1];
			$lokasi = './assets/file_po/' . $gbr['file_name'];
		}

		// print_r($lokasi);
		// exit;

		$config1['upload_path'] = './assets/file_do/'; //path folder
		$config1['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config1['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config1);
		if ($this->upload->do_upload('upload_so')) {
			$gbr2 = $this->upload->data();
			//Compress Image
			$config1['image_library'] = 'gd2';
			$config1['source_image'] = './assets/file_do/' . $gbr2['file_name'];
			$config1['create_thumb'] = FALSE;
			$config1['maintain_ratio'] = FALSE;
			$config1['umum'] = '50%';
			$config1['width'] = 260;
			$config1['height'] = 350;
			$config1['new_image'] = './assets/file_do/' . $gbr2['file_name'];
			$this->load->library('image_lib', $config1);
			$this->image_lib->resize();

			$gambar1  = $gbr2['file_name'];
			$type1    = $gbr2['file_type'];
			$ukuran1  = $gbr2['file_size'];
			$ext2    = explode('.', $gambar1);
			$ext3     = $ext2[1];
			$lokasi2 = './assets/file_do/' . $gbr2['file_name'];
		}

		$data = [
			'no_invoice'		 	=> $code,
			'no_surat'				=> $no_surat,
			'tgl_invoice'			=> $post['tanggal'],
			'no_so'		    		=> $post['no_so'],
			'no_proforma_invoice'	=> $post['no_proforma'],
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'nilai_produk'			=> str_replace(',', '', $post['totalproduk']),
			'persentase'			=> str_replace(',', '', $post['persentase']),
			'nilai_invoice'			=> str_replace(',', '', $post['nilai_tagih']),
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal']),
			'upload_po'				=> $lokasi,
			'upload_so'				=> $lokasi2,
			'referensi'				=> $post['reff'],
			'no_faktur'				=> $post['faktur'],
			'jatuh_tempo'			=> $post['jatuh_tempo'],
			'id_plan_tagih'           => $post['id_plan_tagih'],
			'payment'				=> $post['pembayaran'],
			'keterangan_top'           => $post['keterangan_top'],
			'id_invoice'			=> $id

		];
		//Add Data
		$this->db->delete('tr_invoice', array('id_invoice' => $id));
		$this->db->insert('tr_invoice', $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[no_surat])) {
				$numb1++;
				$dt[] =  array(

					'id_penawaran_detail' => $used[id_penawaran],
					'no_so'				=> $post['no_so'],
					'no_invoice'			=> $code,
					'id_category3'		=> $used[no_surat],
					'nama_produk'	    => $used[nama_produk],
					'qty_invoice'	    => $used[qty_so],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'		=> $used[potensial_loss],
					'diskon'		        => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    =>  str_replace(',', '', $used[total_harga]),
					'tgl_delivery'	    => $used[date],
					'created_on'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $this->auth->user_id(),
					'nilai_diskon'		=> str_replace(',', '', $used[nilai_diskon]),
					'id_invoice'			=> $id
				);
			}
		}
		//    print_r($dt);
		//    exit();
		$this->db->delete('tr_invoice_detail', array('id_invoice' => $id));
		$this->db->insert_batch('tr_invoice_detail', $dt);


		$dataupdate = [
			'status_invoice'				=> '1',
			'nilai_invoice'				    =>  str_replace(',', '', $post['nilai_tagih']),
		];
		//Edit Data
		$this->db->where('id_plan_tagih', $post['id_plan_tagih'])->update("wt_plan_tagih", $dataupdate);

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


	public function SaveDealInvoice()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$id	= $post['id_so'];
		$code = $this->Wt_sales_order_model->generate_code();
		$no_surat = $this->Wt_sales_order_model->BuatNomor();
		$this->db->trans_begin();

		$config['upload_path'] = './assets/file_po/'; //path folder
		$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config);
		if ($this->upload->do_upload('upload_po')) {
			$gbr = $this->upload->data();
			//Compress Image
			$config['image_library'] = 'gd2';
			$config['source_image'] = './assets/file_po/' . $gbr['file_name'];
			$config['create_thumb'] = FALSE;
			$config['maintain_ratio'] = FALSE;
			$config['umum'] = '50%';
			$config['width'] = 260;
			$config['height'] = 350;
			$config['new_image'] = './assets/file_po/' . $gbr['file_name'];
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			$gambar  = $gbr['file_name'];
			$type    = $gbr['file_type'];
			$ukuran  = $gbr['file_size'];
			$ext1    = explode('.', $gambar);
			$ext     = $ext1[1];
			$lokasi = './assets/file_po/' . $gbr['file_name'];
		}

		// print_r($lokasi);
		// exit;

		$config1['upload_path'] = './assets/file_do/'; //path folder
		$config1['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config1['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config1);
		if ($this->upload->do_upload('upload_so')) {
			$gbr2 = $this->upload->data();
			//Compress Image
			$config1['image_library'] = 'gd2';
			$config1['source_image'] = './assets/file_do/' . $gbr2['file_name'];
			$config1['create_thumb'] = FALSE;
			$config1['maintain_ratio'] = FALSE;
			$config1['umum'] = '50%';
			$config1['width'] = 260;
			$config1['height'] = 350;
			$config1['new_image'] = './assets/file_do/' . $gbr2['file_name'];
			$this->load->library('image_lib', $config1);
			$this->image_lib->resize();

			$gambar1  = $gbr2['file_name'];
			$type1    = $gbr2['file_type'];
			$ukuran1  = $gbr2['file_size'];
			$ext2    = explode('.', $gambar1);
			$ext3     = $ext2[1];
			$lokasi2 = './assets/file_do/' . $gbr2['file_name'];
		}

		$data = [
			'no_so'			        => $code,
			'no_surat'				=> $no_surat,
			'tgl_so'			    => $post['tanggal'],
			'no_penawaran'		    => $post['no_penawaran'],
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'status'			    => 1,
			'nilai_so'				=> str_replace(',', '', $post['totalproduk']),
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal']),
			'upload_po'				=> $lokasi,
			'upload_so'				=> $lokasi2,
		];
		//Add Data

		$this->db->delete('tr_sales_order', array('id_so' => $id));
		$this->db->insert('tr_sales_order', $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[no_surat])) {
				$numb1++;

				//    print_r($used);
				//    exit;
				$dt[] =  array(
					'no_so'		=> $code,
					'id_penawaran_detail' => $used[id_penawaran],
					'no_penawaran'		=> $post['no_penawaran'],
					'id_category3'		=> $used[no_surat],
					'nama_produk'	    => $used[nama_produk],
					'qty_so'			    => $used[qty_so],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'		=> $used[potensial_loss],
					'diskon'		        => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    =>  str_replace(',', '', $used[total_harga]),
					'tgl_delivery'	    => $used[tgl_delivery],
					'created_on'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $this->auth->user_id(),
					'nilai_diskon'		=> str_replace(',', '', $used[nilai_diskon])
				);
			}
		}
		//    print_r($dt);
		//    exit();

		$this->db->delete('tr_sales_order_detail', array('id_so' => $id));
		$this->db->insert_batch('tr_sales_order_detail', $dt);


		$data = [
			'status_so'				=> 1,
		];
		//Edit Data
		$this->db->where('no_penawaran', $post['no_penawaran'])->update("tr_penawaran", $data);
		$id_top = $post['top'];
		$top  = $this->db->query("SELECT * FROM ms_top_planning WHERE id_top='$id_top'")->result_array();

		foreach ($top as $det) {
			$nilai  = str_replace(',', '', $post['totalproduk']);


			$datatop = [
				'id_top'			    => $det[id_top],
				'id_top_planning'		=> $det[id_top_planning],
				'payment'			    => $det[payment],
				'keterangan'		    => $det[keterangan],
				'persentase'			=> $det[persentase],
				'nilai'					=> $nilai,
				'nilai_tagih'			=> round(($det[persentase] * $nilai) / 100, 2),
				'no_so'			        => $code,
				'created_on'			=> date('Y-m-d H:i:s'),
				'created_by'			=> $this->auth->user_id(),
			];

			$this->db->insert('wt_plan_tagih', $datatop);
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

	public function SaveEditPenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$no_surat	= $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'no_penawaran'			=> $code,
			'no_surat'				=> $no_surat,
			'tgl_penawaran'			=> date('Y-m-d'),
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'nilai_penawaran'		=> str_replace(',', '', $post['totalproduk']),
			'modified_on'			=> date('Y-m-d H:i:s'),
			'modified_by'			=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal'])
		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);




		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[no_surat])) {
				$numb1++;
				$dt[] =  array(
					'no_penawaran'		=> $code,
					'id_category3'		=> $used[no_surat],
					'nama_produk'	    => $used[nama_produk],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'	=> $used[potensial_loss],
					'diskon'		    => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    => str_replace(',', '', $used[total_harga]),
					'created_on'		=> date('Y-m-d H:i:s'),
					'created_by'		=> $this->auth->user_id(),
					'nilai_diskon'		=> str_replace(',', '', $used[nilai_diskon])
				);
			}
		}
		//    print_r($dt);
		//    exit();
		$this->db->delete('tr_penawaran_detail', array('no_penawaran' => $code));
		$this->db->insert_batch('tr_penawaran_detail', $dt);



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

	function getemail()
	{
		$id_customer = $_GET['id_customer'];
		$kategory3	= $this->db->query("SELECT * FROM master_customers WHERE id_customer = '$id_customer' ")->result();
		$thickness = $kategory3[0]->email;
		echo "<input type='email' class='form-control' id='email_customer' value='$thickness' required name='email_customer' >";
	}
	function getsales()
	{
		$id_customer = $_GET['id_customer'];
		$kategory3	= $this->db->query("SELECT * FROM master_customers WHERE id_customer = '$id_customer' ")->result();
		$id_karyawan = $kategory3[0]->id_karyawan;
		$karyawan	= $this->db->query("SELECT * FROM ms_karyawan WHERE id_karyawan = '$id_karyawan' ")->result();
		$nama_karyawan = $karyawan[0]->nama_karyawan;
		echo "	<div class='col-md-8' >
					<input type='text' class='form-control' id='nama_sales' value='$nama_karyawan' required name='nama_sales' readonly placeholder='Sales Marketing'>
				</div>
				<div class='col-md-8' hidden>
					<input type='text' class='form-control' id='id_sales' value='$id_karyawan'  required name='id_sales' readonly placeholder='Sales Marketing'>
				</div>";
	}
	function getpic()
	{
		$id_customer = $_GET['id_customer'];
		$kategory3	= $this->db->query("SELECT * FROM child_customer_pic WHERE id_customer = '$id_customer' ")->result();
		echo "<select id='pic_customer' name='pic_customer' class='form-control select' required>
				<option value=''>--Pilih--</option>";
		foreach ($kategory3 as $pic) {
			echo "<option value='$pic->name_pic'>$pic->name_pic</option>";
		}
		echo "</select>";
	}

	function CariNamaProduk()
	{
		$loop = $_GET['id'];
		$id_category3 = $_GET['id_category3'];
		$material	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$produk = $material[0]->nama;

		echo "<input type='text' class='form-control input-sm' readonly id='used_nama_produk_$loop' required name='dt[$loop][nama_produk]' value='$produk'>";
	}

	function CariHarga()
	{
		$loop = $_GET['id'];
		$id_category3 = $_GET['id_category3'];
		$material	= $this->db->query("SELECT * FROM ms_product_pricelist WHERE id_category3 = '$id_category3' ")->result();
		$produk = $material[0]->total_pricelist;


		echo "<input type='text' class='form-control input-sm' readonly id='used_harga_satuan_$loop' required name='dt[$loop][harga_satuan]' value='$produk'>";
	}

	function CariDiskon()
	{
		$loop = $_GET['id'];
		$id_category3 = $_GET['id_category3'];
		$idtop       = $_GET['top'];
		$material	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$produk = $material[0]->id_type;
		$diskon	= $this->db->query("SELECT * FROM ms_diskon WHERE id_type = '$produk' AND id_top='$idtop' ")->result();
		$diskonvalue = $diskon[0]->nilai_diskon;

		echo "<input type='text' class='form-control input-sm' id='used_diskon_$loop' required name='dt[$loop][diskon]' value='$diskonvalue' onblur='HitungTotal($loop)'>";
	}

	public function PrintInvoice($id)
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);

		$data = [
			'status'		        => 1,
			'printed_on'			=> date('Y-m-d H:i:s'),
			'printed_by'			=> $this->auth->user_id()
		];
		//Edit Data
		$this->db->where('no_invoice', $id)->update("tr_invoice", $data);

		$data['header']   = $this->Wt_invoicing_model->get_data('tr_invoice', 'no_invoice', $id);
		$data['detail']   = $this->Wt_invoicing_model->get_data('tr_invoice_detail', 'no_invoice', $id);
		$this->load->view('PrintInvoice', $data);

		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'Letter', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Invoice.pdf', 'I');
	}

	public function PrintPackinglist($id)
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$do = $this->db->query("select * from tr_invoice WHERE no_invoice='$id'")->row();
		$id2 = $do->id_do;
		$data = [
			'status'		        => 1,
			'printed_on'			=> date('Y-m-d H:i:s'),
			'printed_by'			=> $this->auth->user_id()
		];
		//Edit Data
		$this->db->where('no_invoice', $id)->update("tr_invoice", $data);

		$data['header']   = $this->Wt_invoicing_model->get_data('tr_invoice', 'no_invoice', $id);
		//$data['detail']   = $this->Wt_invoicing_model->get_data('tr_invoice_detail','no_invoice',$id);
		$data['detail'] 	= $this->Wt_invoicing_model->PrintDetail($id2);

		$data['id']         = $id2;
		$this->load->view('PrintPackinglist', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'Letter', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Packinglist.pdf', 'I');
	}


	public function PrintPreviewInvoice()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);

		$data['header']   = $this->Wt_invoicing_model->get_data('tr_invoice_preview');
		$data['detail']   = $this->Wt_invoicing_model->get_data('tr_invoice_detail_preview');
		$this->load->view('PrintInvoice', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'Letter', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Preview Invoice.pdf', 'I');
	}

	public function PrintProformaInvoice($id)
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);

		// $data = [
		// 'status'		        => 1,
		// 'printed_on'			=> date('Y-m-d H:i:s'),
		// 'printed_by'			=> $this->auth->user_id()
		// ];
		//Edit Data
		// $this->db->where('no_invoice',$id)->update("tr_invoice",$data);			

		$data['header']   = $this->Wt_invoicing_model->get_data('tr_invoice_proforma', 'id_invoice', $id);
		$data['detail']   = $this->Wt_invoicing_model->get_data('tr_invoice_detail_proforma', 'id_invoice', $id);
		$this->load->view('PrintProformaInvoice', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'Letter', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Proforma Invoice.pdf', 'I');
	}


	public function ajukanApprove($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$detail    = $this->Wt_invoicing_model->get_data('tr_penawaran_detail', 'no_penawaran', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'header' => $header,
			'detail' => $detail,
		];

		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('ajukanpenawaran');
	}

	public function FormApproval($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');

		$data = [
			'id' => $id,
		];

		$this->template->set('results', $data);
		$this->template->title('Ajukan Approve');
		$this->template->render('formapproval');
	}

	public function SaveAprrovePenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$this->db->trans_begin();
		$data = [
			'no_penawaran'			=> $code,
			'status'				=> 1,
			'keterangan'			=> $post['keterangan'],
			'approved_on'			=> date('Y-m-d H:i:s'),
			'approved_by'			=> $this->auth->user_id()
		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);

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

	public function index_approval()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$status = 1;
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->CariPenawaranApproval();
		$this->template->set('results', $data);
		$this->template->title('Request Approval');
		$this->template->render('index_approval');
	}
	public function index_so()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$status = 6;
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->CariPenawaranSo();
		$this->template->set('results', $data);
		$this->template->title('Sales Order');
		$this->template->render('index_so');
	}
	public function index_loss()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$status = 7;
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->CariPenawaranLoss();
		$this->template->set('results', $data);
		$this->template->title('Loss Penawaran');
		$this->template->render('index_loss');
	}

	public function history()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->CariPenawaranHistory();
		$this->template->set('results', $data);
		$this->template->title('History Penawaran');
		$this->template->render('history');
	}

	public function ProsesApproval($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$detail    = $this->Wt_invoicing_model->get_data('tr_penawaran_detail', 'no_penawaran', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'header' => $header,
			'detail' => $detail,
		];


		$this->template->set('results', $data);
		$this->template->title('Proses Approval');
		$this->template->render('formprosesapproval');
	}

	public function SaveApprovePenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$no_surat	= $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'no_penawaran'			=> $code,
			'no_surat'				=> $no_surat,
			'tgl_penawaran'			=> date('Y-m-d'),
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'nilai_penawaran'		=> str_replace(',', '', $post['totalproduk']),
			'status'		        => $post['status'],
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal'])

		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);




		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[no_surat])) {
				$numb1++;
				$dt[] =  array(
					'no_penawaran'		=> $code,
					'id_category3'		=> $used[no_surat],
					'nama_produk'	    => $used[nama_produk],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'	=> $used[potensial_loss],
					'diskon'		    => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    => str_replace(',', '', $used[total_harga]),
					'created_on'		=> date('Y-m-d H:i:s'),
					'created_by'		=> $this->auth->user_id(),
					'nilai_diskon'      => str_replace(',', '', $used[nilai_diskon])
				);
			}
		}
		//    print_r($dt);
		//    exit();
		$this->db->delete('tr_penawaran_detail', array('no_penawaran' => $code));
		$this->db->insert_batch('tr_penawaran_detail', $dt);



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


	public function statusTerkirim($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$detail    = $this->Wt_invoicing_model->get_data('tr_penawaran_detail', 'no_penawaran', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'header' => $header,
			'detail' => $detail,
		];

		$this->template->set('results', $data);
		$this->template->title('Ubah Status Penawaran');
		$this->template->render('statusterkirim');
	}


	public function SaveStatusTerkirim()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$no_surat	= $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'status'				=> 4,
			'delivered_on'			=> date('Y-m-d H:i:s'),
			'delivered_by'			=> $this->auth->user_id()
		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);


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

	public function revisiPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$detail    = $this->Wt_invoicing_model->get_data('tr_penawaran_detail', 'no_penawaran', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'header' => $header,
			'detail' => $detail,
		];

		$this->template->set('results', $data);
		$this->template->title('Revisi Penawaran');
		$this->template->render('revisipenawaran');
	}

	public function SaveRevisiPenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$no_surat	= $post['no_surat'];
		$this->db->trans_begin();

		$select1 = $this->db->select('
		no_penawaran,
		no_surat,
		tgl_penawaran,
		id_customer,
		pic_customer,
		mata_uang,
		email_customer,
		valid_until,
		top,
		nilai_penawaran,
		order_status,
		id_sales,
		nama_sales,
		pengiriman,
		status,
		revisi,
		keterangan,
		created_by,
		created_on,
		modified_by,
		modified_on,
		printed_by,
		printed_on,
		delivered_by,
		delivered_on,
		approved_by,
		approved_on,
		revisi_by,
		revisi_on,
		ppn,
		nilai_ppn,
		grand_total')->where('no_penawaran', $code)->get('tr_penawaran');
		if ($select1->num_rows()) {
			$insert = $this->db->insert_batch('tr_penawaran_history', $select1->result_array());
		}


		$select2 = $this->db->select('
		id_penawaran_detail,
		no_penawaran,
		id_category3,
		nama_produk,
		id_bentuk,
		qty,
		harga_satuan,
		stok_tersedia,
		potensial_loss,
		diskon,
		freight_cost,
		total_harga,
		keterangan,
		revisi,
		created_by,
		created_on,
		modified_by,
		modified_on,
		nilai_diskon
		')->where('no_penawaran', $code)->get('tr_penawaran_detail');


		$rev = $select1->row();
		$norev = $rev->revisi + 1;
		$data = [
			'no_penawaran'			=> $code,
			'no_surat'				=> $no_surat,
			'tgl_penawaran'			=> date('Y-m-d'),
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'email_customer'		=> $post['email_customer'],
			'top'			        => $post['top'],
			'order_status'			=> $post['order_sts'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'nilai_penawaran'		=> str_replace(',', '', $post['totalproduk']),
			'status'			    => 0,
			'revisi'			    => $norev,
			'revisi_on'				=> date('Y-m-d H:i:s'),
			'revisi_by'				=> $this->auth->user_id(),
			'ppn'					=> str_replace(',', '', $post['ppn']),
			'nilai_ppn'				=> str_replace(',', '', $post['totalppn']),
			'grand_total'			=> str_replace(',', '', $post['grandtotal'])
		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);




		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			if (!empty($used[no_surat])) {
				$numb1++;
				$dt[] =  array(
					'no_penawaran'		=> $code,
					'id_category3'		=> $used[no_surat],
					'nama_produk'	    => $used[nama_produk],
					'qty'			    => $used[qty],
					'harga_satuan'		=> str_replace(',', '', $used[harga_satuan]),
					'stok_tersedia'		=> $used[stok_tersedia],
					'potensial_loss'	=> $used[potensial_loss],
					'diskon'		    => $used[diskon],
					'freight_cost'		=> str_replace(',', '', $used[freight_cost]),
					'total_harga'	    => str_replace(',', '', $used[total_harga]),
					'revisi'			=> $norev,
					'created_on'		=> date('Y-m-d H:i:s'),
					'created_by'		=> $this->auth->user_id(),
					'nilai_diskon'      => str_replace(',', '', $used[nilai_diskon])
				);
			}
		}
		//    print_r($dt);
		//    exit();
		if ($select2->num_rows()) {
			$insert2 = $this->db->insert_batch('tr_penawaran_detail_history', $select2->result_array());

			$this->db->delete('tr_penawaran_detail', array('no_penawaran' => $code));
			$this->db->insert_batch('tr_penawaran_detail', $dt);
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

	public function statusSo($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$detail    = $this->Wt_invoicing_model->get_data('tr_penawaran_detail', 'no_penawaran', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'header' => $header,
			'detail' => $detail,
		];

		$this->template->set('results', $data);
		$this->template->title('Ubah Status Penawaran');
		$this->template->render('statusso');
	}


	public function SaveStatusSo()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$no_surat	= $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'status'				=> 6,
			'delivered_on'			=> date('Y-m-d H:i:s'),
			'delivered_by'			=> $this->auth->user_id()
		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);


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

	public function statusLoss($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$detail    = $this->Wt_invoicing_model->get_data('tr_penawaran_detail', 'no_penawaran', $id);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'header' => $header,
			'detail' => $detail,
		];

		$this->template->set('results', $data);
		$this->template->title('Ubah Status Penawaran');
		$this->template->render('statusloss');
	}

	public function SaveStatusLoss()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$no_surat	= $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'status'				=> 7,
			'keterangan_loss'	    => $post['keterangan'],
			'delivered_on'			=> date('Y-m-d H:i:s'),
			'delivered_by'			=> $this->auth->user_id()
		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);


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
	public function viewhistory()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$id = $this->uri->segment(3);
		$revisi = $this->uri->segment(4);
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Wt_invoicing_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Wt_invoicing_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Wt_invoicing_model->get_data('mata_uang', 'deleted' . $deleted);
		$top       = $this->Wt_invoicing_model->get_data('ms_top', 'deleted' . $deleted);
		$header    = $this->Wt_invoicing_model->CariHeaderHistory($id, $revisi);
		$detail    = $this->Wt_invoicing_model->CariDetailHistory($id, $revisi);


		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
			'top' => $top,
			'header' => $header,
			'detail' => $detail,
		];

		$this->template->set('results', $data);
		$this->template->title('History Penawaran');
		$this->template->render('viewhistory');
	}



	public function historyfu($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');

		$follow       = $this->Wt_invoicing_model->get_data('tr_followup', 'no_invoice', $id);


		$data = [
			'follow' => $follow,
		];

		$this->template->set('results', $data);
		$this->template->title('History Follow UP');
		$this->template->render('historyfu');
	}


	public function saveFollowUp()
	{
		$this->auth->restrict($this->editPermission);
		$post = $this->input->post();
		// print_r($post);
		// exit;


		$no_invoice = $post['no_invoice'];
		$this->db->trans_begin();

		$data_update = [
			'aktif' 		=> 'N',
			'modified_by' 	=> $this->auth->user_id()
		];

		$this->db->where('no_invoice', $no_invoice);
		$this->db->update("tr_followup", $data_update);


		$config['upload_path'] = './assets/file_po/'; //path folder
		$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|docx|xls|xlsx|ppt|pptx|pdf|rar|zip|vsd'; //type yang dapat diakses bisa anda sesuaikan
		$config['encrypt_name'] = false; //Enkripsi nama yang terupload


		$this->upload->initialize($config);
		if ($this->upload->do_upload('tanda_terima')) {
			$gbr = $this->upload->data();
			//Compress Image
			$config['image_library'] = 'gd2';
			$config['source_image'] = './assets/file_po/' . $gbr['file_name'];
			$config['create_thumb'] = FALSE;
			$config['maintain_ratio'] = FALSE;
			$config['umum'] = '50%';
			$config['width'] = 260;
			$config['height'] = 350;
			$config['new_image'] = './assets/file_po/' . $gbr['file_name'];
			$this->load->library('image_lib', $config);
			$this->image_lib->resize();

			$gambar  = $gbr['file_name'];
			$type    = $gbr['file_type'];
			$ukuran  = $gbr['file_size'];
			$ext1    = explode('.', $gambar);
			$ext     = $ext1[1];
			$lokasi = './assets/file_po/' . $gbr['file_name'];
		}


		$data = [
			'no_invoice'		=> $post['no_invoice'],
			'received'			=> $post['received'],
			'tgl_terima'		=> $post['tgl_terima'],
			'tgl_followup'		=> $post['tgl_followup'],
			'tgl_janji_bayar'	=> $post['tgl_janji_bayar'],
			'keterangan_followup'		=> $post['keterangan_fu'],
			'upload_tanda_terima' => $gambar,
			'aktif'				=> 'Y',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id()

		];

		$insert = $this->db->insert("tr_followup", $data);

		if ($post['tgl_terima'] != '') {
			$data_invoice = [
				'tgl_terima'		=> $post['tgl_terima'],
				'tgl_followup'		=> $post['tgl_followup'],
				'tgl_janji_bayar'	=> $post['tgl_janji_bayar'],
				'modified_on'		=> date('Y-m-d H:i:s'),
				'modified_by'    	=> $this->auth->user_id()
			];
		} else {

			$data_invoice = [
				'tgl_followup'		=> $post['tgl_followup'],
				'tgl_janji_bayar'	=> $post['tgl_janji_bayar'],
				'modified_on'		=> date('Y-m-d H:i:s'),
				'modified_by'    	=> $this->auth->user_id()
			];
		}

		$this->db->where('no_invoice', $no_invoice);
		$this->db->update("tr_invoice", $data_invoice);

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

	public function closeInvoice()
	{

		$this->auth->restrict($this->editPermission);
		$id = $this->input->post('id');

		$data = [
			'status_close' 		=> '1'
		];
		$this->db->trans_begin();
		$this->db->where('no_invoice', $id)->update("tr_invoice", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Failed ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function updatePlan()
	{

		$header 			 = $this->Wt_invoicing_model->get_data('tr_planning_delivery');

		foreach ($header as $hd) {
			$noplanning          = 	$hd->no_planning;
			$no_so				 =  $hd->no_so;
			$Qry_Update_plan	 = "UPDATE wt_plan_tagih2 SET no_planning='$noplanning' WHERE no_so='$no_so'";
			$this->db->query($Qry_Update_plan);
		}
	}

	public function jurnal_invoicing()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Wt_invoicing_model->CariInvoiceJurnal();
		$this->template->set('results', $data);
		$this->template->title('Jurnal Invoicing');
		$this->template->render('index_jurnal_piutang');
	}

	public function tes_nomor()
	{
		$no_surat = $this->Wt_invoicing_model->BuatNomor();
		print_r($no_surat);
	}



	public function PrintPackinglistSlitting($id)
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$do = $this->db->query("select * from tr_invoice WHERE no_invoice='$id'")->row();
		$id2 = $do->id_do;
		$data = [
			'status'		        => 1,
			'printed_on'			=> date('Y-m-d H:i:s'),
			'printed_by'			=> $this->auth->user_id()
		];
		//Edit Data
		$this->db->where('no_invoice', $id)->update("tr_invoice", $data);

		$data['header']   = $this->Wt_invoicing_model->get_data('tr_invoice', 'no_invoice', $id);
		//$data['detail']   = $this->Wt_invoicing_model->get_data('tr_invoice_detail','no_invoice',$id);
		$data['detail'] 	= $this->Wt_invoicing_model->PrintDetail($id2);
		$data['detail2'] 	= $this->Wt_invoicing_model->PrintDetail2($id2);

		$data['id']         = $id2;
		$this->load->view('PrintPackinglistSlitting', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'Letter', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Packinglist.pdf', 'I');
	}

	public function get_invoicing()
	{
		$this->Wt_invoicing_model->get_invoicing();
	}

	public function get_monitoring_invoice()
	{
		$this->Wt_invoicing_model->get_monitoring_invoice();
	}

	public function get_data_spk_marketing()
	{
		$this->Wt_invoicing_model->get_data_spk_marketing();
	}

	public function export_data_mon_inv($tgl_awal = null, $tgl_akhir = null)
	{
		$get_data = $this->Wt_invoicing_model->get_data_monitoring($tgl_awal, $tgl_akhir);

		$data = [
			'tgl_awal' => $tgl_awal,
			'tgl_akhir' => $tgl_akhir,
			'data_monitoring' => $get_data
		];
		$this->load->view('export_excel_monitoring', $data);
	}

	// E-Faktur
	public function e_faktur()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');

		$this->template->title('E-Faktur');
		$this->template->render('index_efaktur');
	}
	public function get_efaktur()
	{
		$get = $this->Wt_invoicing_model->get_efaktur();

		echo json_encode([
			'draw' => $get['draw'],
			'recordsTotal' => $get['recordsTotal'],
			'recordsFiltered' => $get['recordsFiltered'],
			'totalValid' => isset($get['totalValid']) ? $get['totalValid'] : 0,
			'data' => $get['data']
		]);
	}
	public function get_all_efaktur_id()
	{
		$get = $this->Wt_invoicing_model->get_all_efaktur_id();

		echo json_encode([
			'data' => $get
		]);
	}
	public function e_faktur_list()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');

		$this->template->title('List E-Faktur');
		$this->template->render('index_efaktur_list');
	}
	public function list_efaktur()
	{
		$get = $this->Wt_invoicing_model->list_efaktur();

		echo json_encode([
			'draw' => $get['draw'],
			'recordsTotal' => $get['recordsTotal'],
			'recordsFiltered' => $get['recordsFiltered'],
			'data' => $get['data']
		]);
	}
	public function generate_efaktur()
	{
		$post = $this->input->post();
		$id_generate = $post['id_generate'];

		$this->db->select('a.*, b.name_customer as name_customer, b.npwp as npwp, b.npwp_name as npwp_name, b.npwp_address as npwp_address');
		$this->db->from('tr_invoice a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->where('a.stat_efaktur =', 0);
		$this->db->where('b.npwp !=', '');
		$this->db->where_in('a.no_surat', $id_generate);
		$this->db->order_by('a.no_surat', 'ASC');
		// $this->db->limit(5, 0);

		$get_data = $this->db->get();

		$invoices_data_for_export = [];
		$no = (0);
		foreach ($get_data->result_array() as $item) {
			$no++;

			$this->db->select('a.*, b.id_bentuk, b.nama as nama_barang, c.type as tipe_invoice');
			$this->db->from('tr_invoice_detail a');
			$this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_category3', 'left');
			$this->db->join('tr_invoice c', 'c.no_surat = a.no_invoice', 'left');
			$this->db->where('a.no_invoice', $item['no_invoice']);
			// $this->db->where('b.id_bentuk', 'B2000002');
			$get_detail_sheet = $this->db->get()->result();

			$items = [];
			$nilai_invoice = 0;
			$nilai_ppn = 0;
			$nilai_dpp = 0;
			foreach ($get_detail_sheet as $item_sheet) {
				$qty = 0;
				$satuan = 'UM.0003';
				if ($item_sheet->id_bentuk == 'B2000002') {
					$this->db->select('a.qty_sheet, a.price_sheet');
					$this->db->from('stock_material a');
					$this->db->join('dt_delivery_order_child b', 'b.lotno = a.lotno');
					$this->db->join('tr_delivery_order c', 'c.id_delivery_order = b.id_delivery_order');
					$this->db->where('c.no_surat', $item['no_do']);
					$this->db->where('b.id_material', $item_sheet->id_category3);
					$this->db->where('a.no_kirim', $item['id_do']);
					$this->db->group_by('a.id_stock');
					$get_qty_sheet = $this->db->get()->result();

					$qty_sheet = 0;
					foreach ($get_qty_sheet as $item_qty_sheet) {
						$qty_sheet += $item_qty_sheet->qty_sheet;

						if ($item_qty_sheet->price_sheet > 0) :
							$satuan = 'UM.0020';
						else :
							$satuan = 'UM.0003';
						endif;
					}
					$qty = $qty_sheet;

					$ttl_harga = ($item_sheet->harga_satuan * $qty_sheet);
					$dpp_lain_lain = ceil(11 / 12 * $ttl_harga);
					$ppn = ($dpp_lain_lain * 12 / 100);

					// $nilai_invoice += ($qty_sheet);
					$nilai_invoice += ($ttl_harga + $ppn);
					$nilai_ppn += ($ppn);
					$nilai_dpp += $dpp_lain_lain;
				} else {
					$qty = $item_sheet->qty_invoice;
					$ttl_harga = $item_sheet->qty_invoice * $item_sheet->harga_satuan;

					$dpp_lain_lain = ceil(11 / 12 * $ttl_harga);
					$ppn = ($dpp_lain_lain * 12 / 100);
					$grand_total = ($ttl_harga + $ppn);

					$nilai_invoice = $grand_total;
					$nilai_ppn = $ppn;
					$nilai_dpp = $dpp_lain_lain;
				}

				if ($item_sheet->tipe_invoice == 'slitting') {
					$satuan = 'UM.0033';
				}


				$items[] = [
					'barang_jasa' => 'A',
					'nama_barang' =>  $item_sheet->nama_barang . ', ' . $item_sheet->tobe_size,
					'satuan' => $satuan,
					'harga_satuan' => $item_sheet->harga_satuan,
					'qty' => $qty,
					'diskon' => 0,
					'dpp' => $ttl_harga,
					'dpp_lain' => $dpp_lain_lain,
					'tarif_ppn' => '12',
					'ppn' => $ppn,
					'tarif_ppnbm' => 0,
					'ppnbm' => 0,
					'kode_barang' => $item_sheet->kode_coretax
				];
			}
			// echo '<pre>';
			// var_dump($items);
			// exit();

			$npwp_name = strtoupper($item['npwp_name']);
			if ($npwp_name == '' || $npwp_name == null) {
				$npwp_name = strtoupper($item['name_customer']);
			}

			$invoices_data_for_export[] = [
				'no' => $no,
				'no_faktur' => '',
				'no_invoice' => $item['no_surat'],
				'npwp' => $item['npwp'],
				'nama_customer' => strtoupper($npwp_name),
				'address' => $item['npwp_address'],
				'term' => $item['note'],
				'nomor_do' => $item['no_do'],
				'nilai_dpp' => $nilai_dpp,
				'nilai_ppn' => $nilai_ppn,
				'nilai_invoice' => $nilai_invoice,
				'tanggal_invoice' => $item['tgl_invoice'],
				'items' => $items
			];
		}

		if (empty($invoices_data_for_export)) {
			echo json_encode([
				'status' => 'no_data',
				'message' => 'Tidak ada faktur yang perlu diekspor.'
			]);
			exit;
		}

		$invoices_to_update = array_column($invoices_data_for_export, 'no_invoice');

		// =====================
		// PREPARE LOG DATA
		// =====================

		$logData = [];
		$export_id = date("ymdHi");
		$date_export = date("Y-m-d");
		$time_export = date("H:i:s");
		foreach ($invoices_to_update as $inv) {
			$logData[] = [
				"id_export"   => $export_id,
				"date_export" => $date_export,
				"time_export" => $time_export,
				"invoice_no"  => $inv
			];
		}

		// =====================
		// INSERT BATCH (1 QUERY)
		// =====================

		if (!empty($logData)) {
			$this->db->insert_batch('faktur_e_logs', $logData);
		}

		// =====================
		// UPDATE STATUS
		// =====================

		$this->db->set('stat_efaktur', 1);
		$this->db->where_in('no_surat', $invoices_to_update);
		$this->db->where('stat_efaktur', 0);
		$this->db->update('tr_invoice');

		// sementara tidak dipakai
		// history($this->sessionName.' Generate Data E-Faktur: ' . count($invoices_to_update) . ' faktur');

		// =====================
		// SIMPAN EXPORT DATA
		// =====================

		$this->session->set_userdata('export_data_temp', $invoices_data_for_export);

		session_write_close();

		echo json_encode(['status' => 'success']);
		exit;
	}

	public function export_coretax_excel()
	{
		ob_start();
		// 
		ini_set('display_errors', 0);
		error_reporting(0);

		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");

		$invoices_data = $this->session->userdata('export_data_temp');
		$this->session->unset_userdata('export_data_temp');

		if (empty($invoices_data)) {
			redirect('wt_invoicing/e_faktur?msg=data_hilang');
			exit;
		}

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);
		$sheetFaktur = $objPHPExcel->getActiveSheet();
		$sheetFaktur->setTitle('Faktur');

		$sheetFaktur->getStyle("A3:R3")->applyFromArray(
			array(
				'font' => array(
					'color' => array('rgb' => '000000'),
					'bold' => true
				)
			)
		);

		$sheetFaktur->mergeCells('A1:B1');
		$sheetFaktur->setCellValue("A1", 'NPWP Penjual');
		$sheetFaktur->getStyle('A1')->getFont()->setBold(true);

		$sheetFaktur->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheetFaktur->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$sheetFaktur->setCellValueExplicit('C1', '0210982047414000', PHPExcel_Cell_DataType::TYPE_STRING);
		$sheetFaktur->setCellValueExplicit('K2', '', PHPExcel_Cell_DataType::TYPE_STRING); //0761851856433000

		$sheetFaktur->getColumnDimension('C')->setAutoSize(true);

		$headerFaktur = [
			'Baris',
			'Tanggal Faktur',
			'Jenis Faktur',
			'Kode Transaksi',
			'Keterangan',
			'Dokumen Pendukung',
			'Period Dok Pendukung',
			'Referensi',
			'Cap Fasilitas',
			'ID TKU Penjual',
			'NPWP/NIK Pembeli',
			'Jenis ID Pembeli',
			'Negara Pembeli',
			'Nomor Dokumen Pembeli',
			'Nama Pembeli',
			'Alamat Pembeli',
			'Email Pembeli',
			'ID TKU Pembeli'
		];

		// Judul Header
		$sheetFaktur->fromArray($headerFaktur, NULL, 'A3');

		$rowFaktur 		= 4;
		$itemRowIndex 	= 1; // Index untuk menghubungkan Faktur dan Detail

		//MULAI Setup Sheet Detail Faktur (Sheet 1) ya gais ya
		$objPHPExcel->createSheet();
		$sheetDetail = $objPHPExcel->getSheet(1);
		$sheetDetail->setTitle('DetailFaktur');

		$headerDetail = [
			'Baris',
			'Barang/Jasa',
			'Kode Barang Jasa',
			'Nama Barang/Jasa',
			'Nama Satuan Ukur',
			'Harga Satuan',
			'Jumlah Barang Jasa',
			'Total Diskon',
			'DPP',
			'DPP Nilai Lain',
			'Tarif PPN',
			'PPN',
			'Tarif PPnBM',
			'PPnBM'
		];

		// Judul Header si Detail
		$sheetDetail->fromArray($headerDetail, NULL, 'A1');
		$rowDetail = 2;

		foreach ($invoices_data as $invoice) {

			$tanggal_faktur_formatted = date('d/m/Y', strtotime($invoice['tanggal_invoice']));
			$NPWP = preg_replace("/[^0-9]/", "", $invoice['npwp']);
			if (strlen($NPWP) < 16) {
				$NPWP = str_pad($NPWP, 16, '0', STR_PAD_LEFT);
			}

			$dataFaktur = [
				$itemRowIndex,
				"", // Date handled explicitly
				"Normal",
				"",
				"",
				"",
				"",
				$invoice['no_invoice'],
				"",
				"",
				"",
				"TIN",
				"IDN",
				$invoice['no_invoice'],
				$invoice['nama_customer'],
				$invoice['address'],
				"",
				""
			];

			$endFaktur = [
				"END"
			];

			$sheetFaktur->fromArray($dataFaktur, NULL, 'A' . $rowFaktur);
			$sheetFaktur->setCellValueExplicit('B' . $rowFaktur, $tanggal_faktur_formatted, PHPExcel_Cell_DataType::TYPE_STRING);

			$sheetFaktur->setCellValueExplicit('D' . $rowFaktur, "04", PHPExcel_Cell_DataType::TYPE_STRING);
			$sheetFaktur->setCellValueExplicit('J' . $rowFaktur, "0210982047414000000000", PHPExcel_Cell_DataType::TYPE_STRING);
			$sheetFaktur->setCellValueExplicit('K' . $rowFaktur, $NPWP, PHPExcel_Cell_DataType::TYPE_STRING);
			$sheetFaktur->setCellValueExplicit('R' . $rowFaktur, $NPWP . "000000", PHPExcel_Cell_DataType::TYPE_STRING);

			$sheetFaktur->getStyle('J' . $rowFaktur)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$sheetFaktur->getStyle('K' . $rowFaktur)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$sheetFaktur->getStyle('R' . $rowFaktur)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

			$rowFaktur++;

			// Data untuk Sheet Detail Faktur
			foreach ($invoice['items'] as $item) {

				$this->db->select('a.*');
				$this->db->from('tr_invoice a');
				$this->db->where('a.no_surat', $item['no_invoice']);
				$get_header = $this->db->get()->row_array();

				$tipe_invoice = ($get_header['type'] == 'slitting') ? 'Jasa Slitting' : '';

				$nama_barang = (!empty($tipe_invoice)) ? $tipe_invoice . ' ' . $item['nama_barang'] : $item['nama_barang'];

				$dataDetail = [
					$itemRowIndex, // Kunci penghubung
					$item['barang_jasa'],
					'',
					$nama_barang . ', ' . $item['tobe_size'],
					$item['satuan'],
					$item['harga_satuan'],
					$item['qty'],
					$item['diskon'],
					$item['dpp'],
					$item['dpp_lain'],
					$item['tarif_ppn'],
					$item['ppn'],
					$item['tarif_ppnbm'],
					$item['ppnbm']
				];

				$sheetDetail->fromArray($dataDetail, NULL, 'A' . $rowDetail);

				$sheetDetail->setCellValueExplicit('C' . $rowDetail, (empty($tipe_invoice)) ? $item['kode_coretax'] : '290000', PHPExcel_Cell_DataType::TYPE_STRING);
				$sheetDetail->getStyle('C' . $rowDetail)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

				$rowDetail++;
			}

			$itemRowIndex++;
		}

		$lastRowHeader = $rowFaktur - 1;
		$lastRowDetail = $rowDetail - 1;
		$EndRowFaktur  = $rowFaktur;

		$sheetFaktur->setCellValue('A' . $EndRowFaktur, 'END');
		$sheetFaktur->getStyle('A' . $EndRowFaktur)->applyFromArray(
			array(
				'font' => array(
					'color' => array('rgb' => '000000'),
					'bold' => true
				)
			)
		);

		$sheetFaktur->getStyle('B4:B' . $lastRowHeader)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
		// $sheetFaktur->getStyle('J4:J' . $lastRowHeader)->getNumberFormat()->setFormatCode('0000000000000000000000');
		// $sheetFaktur->getStyle('K4:K' . $lastRowHeader)->getNumberFormat()->setFormatCode('0000000000000000');
		// $sheetFaktur->getStyle('R4:R' . $lastRowHeader)->getNumberFormat()->setFormatCode('0000000000000000000000');

		//Setingan Nilai Comma
		$sheetDetail->getStyle('H2:H' . $lastRowDetail)
			->getNumberFormat()
			->setFormatCode('0.00');
		$sheetDetail->getStyle('I2:J' . $lastRowDetail)
			->getNumberFormat()
			->setFormatCode('0.00');
		$sheetDetail->getStyle('L2:L' . $lastRowDetail)
			->getNumberFormat()
			->setFormatCode('0.00');
		$sheetDetail->getStyle('N2:N' . $lastRowDetail)
			->getNumberFormat()
			->setFormatCode('0.00');

		$filename = 'Impor_Faktur_Keluaran_Coretax_' . date('Ymd_His') . '.xlsx';

		while (ob_get_level()) {
			ob_end_clean();
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

	public function export_coretax_excel_row()
	{
		ob_start();

		ini_set('display_errors', 0);
		error_reporting(0);

		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");

		$getID = $this->input->get('getID');

		$this->db->select('a.*, b.name_customer as name_customer, b.npwp as npwp, b.npwp_name as npwp_name, b.npwp_address as npwp_address');
		$this->db->from('tr_invoice a');
		$this->db->join('master_customers b', 'b.id_customer=a.id_customer');
		$this->db->join('faktur_e_logs f', 'f.invoice_no=a.no_surat');
		$this->db->where('f.id_export =', $getID);
		$this->db->order_by('a.no_surat', 'ASC');

		$get_data = $this->db->get();
		// echo '<pre>';
		// var_dump($get_data->result_array());
		// exit();

		$invoices_data = [];
		$no = (0 + $start);
		foreach ($get_data->result_array() as $item) {
			$no++;

			$tipe_invoice = ($item['type'] == 'slitting') ? 'Jasa Slitting' : '';

			$this->db->select('a.*, b.id_bentuk, b.nama as nama_barang, c.kode_coretax');
			$this->db->from('tr_invoice_detail a');
			$this->db->join('ms_inventory_category3 b', 'b.id_category3 = a.id_category3', 'left');
			$this->db->join('ms_inventory_category2 c', 'c.id_category2 = b.id_category2', 'left');
			$this->db->where('a.no_invoice', $item['no_invoice']);
			// $this->db->where('b.id_bentuk', 'B2000002');
			$get_detail_sheet = $this->db->get()->result();

			$items = [];
			$nilai_invoice = 0;
			$nilai_ppn = 0;
			$nilai_dpp = 0;
			foreach ($get_detail_sheet as $item_sheet) {
				$qty = 0;
				$satuan = 'UM.0003';
				if ($item_sheet->id_bentuk == 'B2000002') {
					$this->db->select('a.qty_sheet, a.price_sheet');
					$this->db->from('stock_material a');
					$this->db->join('dt_delivery_order_child b', 'b.lotno = a.lotno');
					$this->db->join('tr_delivery_order c', 'c.id_delivery_order = b.id_delivery_order');
					$this->db->where('c.no_surat', $item['no_do']);
					$this->db->where('b.id_material', $item_sheet->id_category3);
					$this->db->where('a.no_kirim', $item['id_do']);
					$this->db->group_by('a.id_stock');
					$get_qty_sheet = $this->db->get()->result();

					$qty_sheet = 0;
					foreach ($get_qty_sheet as $item_qty_sheet) {
						$qty_sheet += $item_qty_sheet->qty_sheet;

						if (!empty($tipe_invoice)) :
							$satuan = 'UM.0033';
						else :
							if ($item_qty_sheet->price_sheet > 0) :
								$satuan = 'UM.0020';
							else :
								$satuan = 'UM.0003';
							endif;
						endif;
					}
					$qty = $qty_sheet;

					$ttl_harga = ($item_sheet->harga_satuan * $qty_sheet);
					$dpp_lain_lain = ceil(11 / 12 * $ttl_harga);
					$ppn = ($dpp_lain_lain * 12 / 100);

					// $nilai_invoice += ($qty_sheet);
					$nilai_invoice += ($ttl_harga + $ppn);
					$nilai_ppn += ($ppn);
					$nilai_dpp += $dpp_lain_lain;
				} else {
					$qty = $item_sheet->qty_invoice;
					$ttl_harga = $item_sheet->qty_invoice * $item_sheet->harga_satuan;

					$dpp_lain_lain = ceil(11 / 12 * $ttl_harga);
					$ppn = ($dpp_lain_lain * 12 / 100);
					$grand_total = ($ttl_harga + $ppn);

					$nilai_invoice = $grand_total;
					$nilai_ppn = $ppn;
					$nilai_dpp = $dpp_lain_lain;
				}

				$items[] = [
					'barang_jasa' => 'A',
					'nama_barang' =>  $item_sheet->nama_barang . ', ' . $item_sheet->tobe_size,
					'satuan' => $satuan,
					'harga_satuan' => $item_sheet->harga_satuan,
					'qty' => $qty,
					'diskon' => 0,
					'dpp' => $ttl_harga,
					'dpp_lain' => $dpp_lain_lain,
					'tarif_ppn' => '12',
					'ppn' => $ppn,
					'tarif_ppnbm' => 0,
					'ppnbm' => 0,
					'kode_barang' => $item_sheet->kode_coretax
				];
			}
			// echo '<pre>';
			// var_dump($items);
			// exit();

			$npwp_name = strtoupper($item['npwp_name']);
			if ($npwp_name == '' || $npwp_name == null) {
				$npwp_name = strtoupper($item['name_customer']);
			}

			$invoices_data[] = [
				'no' => $no,
				'no_faktur' => '',
				'no_invoice' => $item['no_surat'],
				'npwp' => $item['npwp'],
				'nama_customer' => strtoupper($npwp_name),
				'address' => $item['npwp_address'],
				'term' => $item['note'],
				'nomor_do' => $item['no_do'],
				'nilai_dpp' => $nilai_dpp,
				'nilai_ppn' => $nilai_ppn,
				'nilai_invoice' => $nilai_invoice,
				'tanggal_invoice' => $item['tgl_invoice'],
				'items' => $items
			];
		}

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0);
		$sheetFaktur = $objPHPExcel->getActiveSheet();
		$sheetFaktur->setTitle('Faktur');

		$sheetFaktur->getStyle("A3:R3")->applyFromArray(
			array(
				'font' => array(
					'color' => array('rgb' => '000000'),
					'bold' => true
				)
			)
		);

		$sheetFaktur->mergeCells('A1:B1');
		$sheetFaktur->setCellValue("A1", 'NPWP Penjual');
		$sheetFaktur->getStyle('A1')->getFont()->setBold(true);

		$sheetFaktur->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheetFaktur->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$sheetFaktur->setCellValueExplicit('C1', '0210982047414000', PHPExcel_Cell_DataType::TYPE_STRING);
		$sheetFaktur->setCellValueExplicit('K2', '', PHPExcel_Cell_DataType::TYPE_STRING); //0761851856433000

		$sheetFaktur->getColumnDimension('C')->setAutoSize(true);

		$headerFaktur = [
			'Baris',
			'Tanggal Faktur',
			'Jenis Faktur',
			'Kode Transaksi',
			'Keterangan',
			'Dokumen Pendukung',
			'Period Dok Pendukung',
			'Referensi',
			'Cap Fasilitas',
			'ID TKU Penjual',
			'NPWP/NIK Pembeli',
			'Jenis ID Pembeli',
			'Negara Pembeli',
			'Nomor Dokumen Pembeli',
			'Nama Pembeli',
			'Alamat Pembeli',
			'Email Pembeli',
			'ID TKU Pembeli'
		];

		// Judul Header
		$sheetFaktur->fromArray($headerFaktur, NULL, 'A3');

		$rowFaktur 		= 4;
		$itemRowIndex 	= 1; // Index untuk menghubungkan Faktur dan Detail

		//MULAI Setup Sheet Detail Faktur (Sheet 1) ya gais ya
		$objPHPExcel->createSheet();
		$sheetDetail = $objPHPExcel->getSheet(1);
		$sheetDetail->setTitle('DetailFaktur');

		$headerDetail = [
			'Baris',
			'Barang/Jasa',
			'Kode Barang Jasa',
			'Nama Barang/Jasa',
			'Nama Satuan Ukur',
			'Harga Satuan',
			'Jumlah Barang Jasa',
			'Total Diskon',
			'DPP',
			'DPP Nilai Lain',
			'Tarif PPN',
			'PPN',
			'Tarif PPnBM',
			'PPnBM'
		];

		// Judul Header si Detail
		$sheetDetail->fromArray($headerDetail, NULL, 'A1');
		$rowDetail = 2;

		foreach ($invoices_data as $invoice) {

			$tanggal_faktur_formatted = date('d/m/Y', strtotime($invoice['tanggal_invoice']));
			$NPWP = preg_replace("/[^0-9]/", "", $invoice['npwp']);
			if (strlen($NPWP) < 16) {
				$NPWP = str_pad($NPWP, 16, '0', STR_PAD_LEFT);
			}

			$dataFaktur = [
				$itemRowIndex,
				"", // Date handled explicitly
				"Normal",
				"",
				"",
				"",
				"",
				$invoice['no_invoice'],
				"",
				"",
				"",
				"TIN",
				"IDN",
				$invoice['no_invoice'],
				$invoice['nama_customer'],
				$invoice['address'],
				"",
				""
			];

			$endFaktur = [
				"END"
			];

			$sheetFaktur->fromArray($dataFaktur, NULL, 'A' . $rowFaktur);
			$sheetFaktur->setCellValueExplicit('B' . $rowFaktur, $tanggal_faktur_formatted, PHPExcel_Cell_DataType::TYPE_STRING);

			$sheetFaktur->setCellValueExplicit('D' . $rowFaktur, "04", PHPExcel_Cell_DataType::TYPE_STRING);
			$sheetFaktur->setCellValueExplicit('J' . $rowFaktur, "0210982047414000000000", PHPExcel_Cell_DataType::TYPE_STRING);
			$sheetFaktur->setCellValueExplicit('K' . $rowFaktur, $NPWP, PHPExcel_Cell_DataType::TYPE_STRING);
			$sheetFaktur->setCellValueExplicit('R' . $rowFaktur, $NPWP . "000000", PHPExcel_Cell_DataType::TYPE_STRING);

			$sheetFaktur->getStyle('J' . $rowFaktur)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$sheetFaktur->getStyle('K' . $rowFaktur)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$sheetFaktur->getStyle('R' . $rowFaktur)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

			$rowFaktur++;

			// Data untuk Sheet Detail Faktur
			foreach ($invoice['items'] as $item) {

				$dataDetail = [
					$itemRowIndex, // Kunci penghubung
					$item['barang_jasa'],
					'',
					$item['nama_barang'] . ', ' . $item['tobe_size'],
					$item['satuan'],
					$item['harga_satuan'],
					$item['qty'],
					$item['diskon'],
					$item['dpp'],
					$item['dpp_lain'],
					$item['tarif_ppn'],
					$item['ppn'],
					$item['tarif_ppnbm'],
					$item['ppnbm']
				];

				$sheetDetail->fromArray($dataDetail, NULL, 'A' . $rowDetail);

				$sheetDetail->setCellValueExplicit('C' . $rowDetail, (empty($tipe_invoice)) ? $item['kode_barang'] : '290000', PHPExcel_Cell_DataType::TYPE_STRING);
				$sheetDetail->getStyle('C' . $rowDetail)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

				$rowDetail++;
			}

			$itemRowIndex++;
		}

		$lastRowHeader = $rowFaktur - 1;
		$lastRowDetail = $rowDetail - 1;
		$EndRowFaktur  = $rowFaktur;

		$sheetFaktur->setCellValue('A' . $EndRowFaktur, 'END');
		$sheetFaktur->getStyle('A' . $EndRowFaktur)->applyFromArray(
			array(
				'font' => array(
					'color' => array('rgb' => '000000'),
					'bold' => true
				)
			)
		);

		$sheetFaktur->getStyle('B4:B' . $lastRowHeader)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
		// $sheetFaktur->getStyle('J4:J' . $lastRowHeader)->getNumberFormat()->setFormatCode('0000000000000000000000');
		// $sheetFaktur->getStyle('K4:K' . $lastRowHeader)->getNumberFormat()->setFormatCode('0000000000000000');
		// $sheetFaktur->getStyle('R4:R' . $lastRowHeader)->getNumberFormat()->setFormatCode('0000000000000000000000');

		//Setingan Nilai Comma
		$sheetDetail->getStyle('H2:H' . $lastRowDetail)
			->getNumberFormat()
			->setFormatCode('0.00');
		$sheetDetail->getStyle('I2:J' . $lastRowDetail)
			->getNumberFormat()
			->setFormatCode('0.00');
		$sheetDetail->getStyle('L2:L' . $lastRowDetail)
			->getNumberFormat()
			->setFormatCode('0.00');
		$sheetDetail->getStyle('N2:N' . $lastRowDetail)
			->getNumberFormat()
			->setFormatCode('0.00');

		$filename = 'Impor_Faktur_Keluaran_Coretax_' . date('Ymd_His') . '.xlsx';

		while (ob_get_level()) {
			ob_end_clean();
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}
}
