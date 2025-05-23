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

class Penawaran extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Penawaran.View';
	protected $addPermission  	= 'Penawaran.Add';
	protected $managePermission = 'Penawaran.Manage';
	protected $deletePermission = 'Penawaran.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Penawaran/Inventory_4_model',
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
		$this->template->title('Penawaran');
		$this->template->render('index');
	}

	public function addHeader()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$customers = $this->Inventory_4_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang', 'deleted' . $deleted);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Add Penawaran');
		$this->template->render('AddHeader');
	}
	public function PrintHeader1($id)
	{
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Inventory_4_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Inventory_4_model->PrintDetail($id);
		$this->load->view('PrintHeader', $data);
	}
	public function PrintHeader($id)
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Inventory_4_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Inventory_4_model->PrintDetail($id);

		$tipe_sheet = 0;
		foreach($data['detail'] as $item) :
			if($item->id_bentuk == 'B2000002' && $tipe_sheet == 0) {
				$tipe_sheet = 1;
			}
		endforeach;
		$data['tipe_sheet'] = $tipe_sheet;

		$this->load->view('PrintHeader', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(10, 5, 10, 5));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Penawaran.pdf', 'I');
	}
	public function EditHeader($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $this->Inventory_4_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$customers = $this->Inventory_4_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'head' => $head,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Add Penawaran');
		$this->template->render('EditHeader');
	}
	public function detail()
	{
		$id = $this->uri->segment(3);
		// print($id);
		// exit();
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		$detail = $this->Inventory_4_model->getpenawaran($id);
		$header = $this->Inventory_4_model->getHeaderPenawaran($id);

		// print($header);
		// exit();
		$data = [
			'detail' => $detail,
			'header' => $header
		];
		$this->template->set('results', $data);
		$this->template->title('Penawaran');
		$this->template->render('detail');
	}

	public function detailrevisi()
	{
		$id = $this->uri->segment(3);
		// print($id);
		// exit();
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		$detail = $this->Inventory_4_model->getpenawaran($id);
		$header = $this->Inventory_4_model->getHeaderPenawaran($id);
		$data = [
			'detail' => $detail,
			'header' => $header
		];
		$this->template->set('results', $data);
		$this->template->title('Penawaran');
		$this->template->render('detailrevisi');
	}
	public function editPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$penawaran = $this->Inventory_4_model->get_data('child_penawaran', 'id_child_penawaran', $id);
		$inventory_3 = $this->Inventory_4_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('editPenawaran');
	}

	public function editRevisiPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$penawaran = $this->Inventory_4_model->get_data('child_penawaran', 'id_child_penawaran', $id);
		$inventory_3 = $this->Inventory_4_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Revisi Penawaran');
		$this->template->render('editRevisiPenawaran');
	}



	public function ViewHeader($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$header = $this->Inventory_4_model->getHeaderPenawaran($id);
		$detail = $this->Inventory_4_model->PrintDetail($id);
		$data = [
			'header' => $header,
			'detail' => $detail,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('ViewHeader');
	}

	public function viewPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$penawaran = $this->Inventory_4_model->get_data('child_penawaran', 'id_child_penawaran', $id);
		$inventory_3 = $this->Inventory_4_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('viewPenawaran');
	}
	public function viewRevisiPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$penawaran = $this->Inventory_4_model->get_data('child_penawaran', 'id_child_penawaran', $id);
		$inventory_3 = $this->Inventory_4_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
		$this->template->set('results', $data);
		$this->template->title('View Penawaran');
		$this->template->render('viewRevisiPenawaran');
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


	public function addPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$headpenawaran = $this->Inventory_4_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$inventory_3 = $this->Inventory_4_model->get_data_material();
		$data = [
			'inventory_3' => $inventory_3,
			'headpenawaran' => $headpenawaran
		];
		$this->template->set('results', $data);
		$this->template->title('Add Penawaran');
		$this->template->render('AddPenawaran');
	}

	public function addRevisiPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$headpenawaran = $this->Inventory_4_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$inventory_3 = $this->Inventory_4_model->get_data_material();
		$data = [
			'inventory_3' => $inventory_3,
			'headpenawaran' => $headpenawaran
		];
		$this->template->set('results', $data);
		$this->template->title('Add Revisi Penawaran');
		$this->template->render('AddRevisiPenawaran');
	}


	function cari_pricelist()
	{
		$id_category3 = $_GET['id_category3'];
		$mata_uang = $_GET['mata_uang'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$inven1 = $kategory3[0]->id_category1;
		if ($inven1 == "I2000001") {
			$plquery	= $this->db->query("SELECT * FROM ms_pricelistfr WHERE id_category3 = '$id_category3' ")->result();
			if (empty($plquery)) {
				echo "<div class='col-sm-12' align='center'>
					<label  for='forecast'>PRICELIST</label>
					</div>
					<div class='col-sm-12' align='center'>
					<div class='form-group row'>
					<table class='col-sm-12'>
					<tr>
						<th><center>Book Price<c/enter></th>
					</tr>
					<tr>
						<td><center>
						Price List Untuk Material Ini Belum Terinput
						</center></td>
					</tr>
					</table>
					</div>
					</div>";
			} else {
				$bottom_price = $plquery[0]->bottom_price;

				echo "	<div class='col-sm-12' align='center'>
					<label  for='forecast'>PRICELIST</label>
					</div>
					<div class='col-sm-12' align='center'>
					<div class='form-group row'>
					<table class='col-sm-12'>
					<tr>
						<th><center>Book Price<c/enter></th>
					</tr>
					<tr>
						<td><center>Rp. $bottom_price  ,-</center></td>
					</tr>
					</table>
					</div>
					</div>
					";
			};
		} elseif ($inven1 == "I2000002") {

			$plquery	= $this->db->query("SELECT * FROM ms_pricelistnfr WHERE id_category3 = '$id_category3' ")->result();
			if (empty($plquery)) {
				echo "<div class='col-sm-12' align='center'>
					<label  for='forecast'>PRICELIST</label>
					</div>
					<div class='col-sm-12' align='center'>
					<div class='form-group row'>
					<table class='col-sm-12'>
					<tr>
						<th><center>Book Price<c/enter></th>
						<th><center>LME 10 Hari</center></th>
						<th><center>LME 30 Hari</center></th>
						<th><center>LME SPOT</center></th>
					</tr>
					<tr>
						<td colspan='4'><center>
						Price List Untuk Material Ini Belum Terinput
						</center></td>
					</tr>
					</table>
					</div>
					</div>";
			} else {
				$hariini = date('Y-m-d');
				$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
				$tendays = date("Y-m-d", $sepuluh_hari);
				$tglnow = date('d');
				$blnnow = date('m');
				if ($blnnow != '1') {
					$blnkmrn = $blnnow - 1;
					$yearkemaren = date('Y');
				} else {
					$blnkmrn = "12";
					$yearnow = date('Y');
					$yearkemaren = $yearnow - 1;
				}
				$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
				$kurs10hari	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
				$kurs30hari	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
				$nomkurs = $kurs[0]->kurs;
				$nomkurs10 = $kurs10hari[0]->nominal;
				$nomkurs30 = $kurs30hari[0]->nominal;
				$bottom_price = number_format($plquery[0]->bottom_price, 2);
				$bottom_price10 = number_format($plquery[0]->bottom_price10 * $nomkurs, 2);
				$bottom_price30 = number_format($plquery[0]->bottom_price30 * $nomkurs, 2);
				$bottom_pricespot = number_format($plquery[0]->bottom_pricespot * $nomkurs, 2);
				$bottom_priceclear = number_format($plquery[0]->bottom_price, 2);
				$bottom_price10clear = number_format($plquery[0]->bottom_price10, 2);
				$bottom_price30clear = number_format($plquery[0]->bottom_price30, 2);
				$bottom_pricespotclear = number_format($plquery[0]->bottom_pricespot, 2);
				$bottom_pricekurs10 = number_format($plquery[0]->bottom_price, 2);
				$bottom_price10kurs10 = number_format($plquery[0]->bottom_price10 * $nomkurs10, 2);
				$bottom_price30kurs10 = number_format($plquery[0]->bottom_price30 * $nomkurs10, 2);
				$bottom_pricespotkurs10 = number_format($plquery[0]->bottom_pricespot * $nomkurs10, 2);
				$bottom_pricekurs30 = number_format($plquery[0]->bottom_price, 2);
				$bottom_price10kurs30 = number_format($plquery[0]->bottom_price10 * $nomkurs30, 2);
				$bottom_price30kurs30 = number_format($plquery[0]->bottom_price30 * $nomkurs30, 2);
				$bottom_pricespotkurs30 = number_format($plquery[0]->bottom_pricespot * $nomkurs30, 2);
				$k =  number_format($nomkurs, 2);
				$k10 =  number_format($nomkurs10, 2);
				$k30 =  number_format($nomkurs30, 2);

				echo "	<div class='col-sm-12' align='center'>
					<label  for='forecast'>PRICELIST</label>
					</div>
					<div class='col-sm-12' align='center'>
					<div class='form-group row'>
					<table class='col-sm-12' border='1' cellspacing='0'>
					<tr>
						<th>Keterangan</th>
						<th>Kurs</th>
						<th><center>Book Price<c/enter></th>
						<th><center>LME 10 Hari</center></th>
						<th><center>LME 30 Hari</center></th>
						<th><center>LME SPOT</center></th>
					</tr>
					<tr>
						<td>Kurs Terbaru</td>
						<td><center>Rp. $k  ,-</center></td>
						<td><center>Rp. $bottom_price  ,-</center></td>
						<td><center>Rp. $bottom_price10  ,-</center></td>
						<td><center>Rp. $bottom_price30  ,-</center></td>
						<td><center>Rp. $bottom_pricespot  ,-</center></td>
					</tr>
					<tr>
					<td>Kurs 10 Hari</td>
					<td><center>Rp. $k10  ,-</center></td>
						<td><center>Rp. $bottom_pricekurs10  ,-</center></td>
						<td><center>Rp. $bottom_price10kurs10  ,-</center></td>
						<td><center>Rp. $bottom_price30kurs10  ,-</center></td>
						<td><center>Rp. $bottom_pricespotkurs10  ,-</center></td>
					</tr>
					<tr>
					<td>Kurs 30 Hari</td>
					<td><center>Rp. $k30  ,-</center></td>
						<td><center>Rp. $bottom_pricekurs30  ,-</center></td>
						<td><center>Rp. $bottom_price10kurs30  ,-</center></td>
						<td><center>Rp. $bottom_price30kurs30  ,-</center></td>
						<td><center>Rp. $bottom_pricespotkurs30  ,-</center></td>
					</tr>
					<tr>
					<td>Dolar</td>
					<td> - </td>
						<td><center>-</center></td>
						<td><center>$. $bottom_price10clear  ,-</center></td>
						<td><center>$. $bottom_price30clear  ,-</center></td>
						<td><center>$. $bottom_pricespotclear  ,-</center></td>
					</tr>
					</table>
					</div>
					</div>
					";
			};
		};
	}
	function cari_bentuk()
	{
		$id_category3 = $_GET['id_category3'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$id_bentuk = $kategory3[0]->id_bentuk;
		$bentukquery	= $this->db->query("SELECT * FROM ms_bentuk WHERE id_bentuk = '$id_bentuk' ")->result();
		$bentuk_material = $bentukquery[0]->nm_bentuk;
		echo "<div class='col-md-4'>
				<label for='customer'>Bentuk</label>
			  </div>
			  <div class='col-md-8'>
				<input type='text' class='form-control' readonly value='$bentuk_material' id='bentuk_material'  required name='bentuk_material' placeholder='Bentuk Material'>
			  </div>
			  <div class='col-md-8' hidden>
				<input type='text' class='form-control' readonly value='$id_bentuk' id='id_bentuk'  required name='id_bentuk' placeholder='Bentuk Material'>
			  </div>";
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
		echo "	<div class='col-md-8' hidden>
					<input type='text' class='form-control' id='nama_sales' value='$id_karyawan' required name='nama_sales' readonly placeholder='Sales Marketing'>
				</div>
				<div class='col-md-8'>
					<input type='text' class='form-control' id='id_sales' value='$nama_karyawan'  required name='id_sales' readonly placeholder='Sales Marketing'>
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

	function cari_thickness()
	{
		$id_category3 = $_GET['id_category3'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$thickness = $kategory3[0]->thickness;
		echo "<input type='text' class='form-control' readonly id='thickness' value='$thickness' required name='thickness' placeholder='Bentuk Material'>";
	}
	function cari_density()
	{
		$id_category3 = $_GET['id_category3'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$density = $kategory3[0]->density;
		echo "<input type='text' class='form-control' readonly id='density' value='$density' required name='density' placeholder='Bentuk Material'>";
	}
	function hitung_komisi()
	{
		$bottom = $_GET['bottom'];
		$komisi = $_GET['komisi'];
		$profit = $_GET['bottom'] * $_GET['profit'] / 100;
		$hasil = $bottom + $komisi + $profit;
		echo "<input type='text' class='form-control autoNumeric' value='$hasil' id='harga_penawaran'  required name='harga_penawaran' placeholder='Bentuk Material'>";
	}
	function carimsprofit()
	{
		$density = $_GET['density'];
		$inven1 = $_GET['inven1'];
		$thickness = $_GET['thickness'];
		$width = $_GET['width'];
		$berat = $_GET['forecast'];
		$maxprofit	= $this->db->query("SELECT max(maksimum) as maximum FROM ms_profit_material WHERE alloy = '$inven1' ")->result();
		$nilaimax = $maxprofit[0]->maximum;

		if ($berat > $nilaimax) {
			$profitaa	= $this->db->query("SELECT * FROM ms_profit_material WHERE alloy = '$inven1' AND minimum < '$berat' AND maksimum  IS NULL   ")->result();
			$nilai_profit = $profitaa[0]->profit;
			$aaa = huhu;
		} else {
			$profitaa	= $this->db->query("SELECT * FROM ms_profit_material WHERE  alloy = '$inven1' AND minimum < '$berat' AND maksimum >= '$berat'  ")->result();
			$nilai_profit = $profitaa[0]->profit;
			$aaa = hihi;
		}
		echo "$nilai_profit %";
	}
	function cari_inven1()
	{
		$id_category3 = $_GET['id_category3'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$inven1 = $kategory3[0]->id_category1;
		echo "<input type='text' class='form-control' id='inven1' value='$inven1'  required name='inven1' placeholder='Bentuk Material'>";
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
		echo "<select id='inventory_2' name='hd1[1][inventory_2]' class='form-control onchange='get_inv3()'  input-sm select2'>";
		echo "<option value=''>--Pilih--</option>";
		foreach ($data as $key => $st) :
			echo "<option value='$st->id_category1' set_select('inventory_2', $st->id_category1, isset($data->id_category1) && $data->id_category1 == $st->id_category1)>$st->nama
                    </option>";
		endforeach;
		echo "</select>";
	}
	function get_inven3()
	{
		$inventory_2 = $_GET['inventory_2'];
		$data = $this->Inventory_4_model->level_3($inventory_2);

		// print_r($data);
		// exit();
		echo "<select id='inventory_3' name='hd1[1][inventory_3]' class='form-control input-sm select2'>";
		echo "<option value=''>--Pilih--</option>";
		foreach ($data as $key => $st) :
			echo "<option value='$st->id_category2' set_select('inventory_3', $st->id_category2, isset($data->id_category2) && $data->id_category2 == $st->id_category2)>$st->nama
                    </option>";
		endforeach;
		echo "</select>";
	}
	public function saveNewPenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$this->db->trans_begin();
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$sebulan = mktime(0, 0, 0, date('n'), date('j') - 30, date('Y'));
		$tirtydays = date("Y-m-d", $sebulan);
		$tglnow = date('d');
		$blnnow = date('m');
		if ($blnnow != '1') {
			$blnkmrn = $blnnow - 1;
			$yearkemaren = date('Y');
		} else {
			$blnkmrn = "12";
			$yearnow = date('Y');
			$yearkemaren = $yearnow - 1;
		}
		$kurs_terpakai = $post['kurs_terpakai'];
		if ($kurs_terpakai == 'spot') {
			$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
			$nominal = $kurs[0]->kurs;
		} elseif ($kurs_terpakai == '10') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} elseif ($kurs_terpakai == '30') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} else {
			$nominal = '1';
		}
		$code = $post['no_penawaran'];
		// print_r($tendays);
		// exit;


		if ($nominal != '' || $nominal != 0) {
			$dolar = $post['harga_penawaran'] / $nominal;

			$data = [
				'id_child_penawaran'	=> $code,
				'id_category3'			=> $post['id_category3'],
				'no_penawaran'			=> $post['no_penawaran'],
				'bentuk_material'		=> $post['bentuk_material'],
				'id_bentuk'				=> $post['id_bentuk'],
				'thickness'				=> $post['thickness'],
				'density'				=> $post['density'],
				'lotno'					=> $post['lotno'],
				'length'					=> $post['length'],
				'width'					=> $post['width'],
				'forecast'				=> $post['forecast'],
				'kurs_terpakai'				=> $post['kurs_terpakai'],
				'inven1'				=> $post['inven1'],
				'bottom'				=> str_replace(',', '', $post['bottom']),
				'dasar_harga'			=> $post['dasar_harga'],
				'komisi'				=> str_replace(',', '', $post['komisi']),
				'profit'				=> str_replace(',', '', $post['profit']),
				'keterangan'			=> $post['keterangan'],
				'harga_penawaran'		=> str_replace(',', '', $post['harga_penawaran']),
				'harga_penawaran_cust'	=> str_replace(',', '', $post['harga_penawaran_cust']),
				'harga_dolar'			=> $dolar,
				'price_sheet'			=> str_replace(',', '', $post['price_sheet']),
				'qty_sheet'			=> str_replace(',', '', $post['qty_sheet']),
				'created_on'			=> date('Y-m-d H:i:s'),
				'created_by'			=> $this->auth->user_id()
			];
			//Add Data
			$idtes = $this->db->insert('child_penawaran', $data);
		} else {
			$idtes = 0;
		}

		if ($idtes == 0) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item Kurs Yang anda pilih tidak tersedia. Thanks ...',
				'code' => $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'code' => $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}

	public function saveRevisiPenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$this->db->trans_begin();
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$sebulan = mktime(0, 0, 0, date('n'), date('j') - 30, date('Y'));
		$tirtydays = date("Y-m-d", $sebulan);
		$tglnow = date('d');
		$blnnow = date('m');
		if ($blnnow != '1') {
			$blnkmrn = $blnnow - 1;
			$yearkemaren = date('Y');
		} else {
			$blnkmrn = "12";
			$yearnow = date('Y');
			$yearkemaren = $yearnow - 1;
		}
		$kurs_terpakai = $post['kurs_terpakai'];
		if ($kurs_terpakai == 'spot') {
			$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
			$nominal = $kurs[0]->kurs;
		} elseif ($kurs_terpakai == '10') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} elseif ($kurs_terpakai == '30') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} else {
			$nominal = '1';
		}
		$code = $post['no_penawaran'];
		// print_r($tendays);
		// exit;


		if ($nominal != '' || $nominal != 0) {
			$dolar = $post['harga_penawaran'] / $nominal;

			$data = [
				'id_child_penawaran'	=> $code,
				'id_category3'			=> $post['id_category3'],
				'no_penawaran'			=> $post['no_penawaran'],
				'bentuk_material'		=> $post['bentuk_material'],
				'id_bentuk'				=> $post['id_bentuk'],
				'thickness'				=> $post['thickness'],
				'density'				=> $post['density'],
				'lotno'					=> $post['lotno'],
				'length'					=> $post['length'],
				'width'					=> $post['width'],
				'forecast'				=> $post['forecast'],
				'kurs_terpakai'				=> $post['kurs_terpakai'],
				'inven1'				=> $post['inven1'],
				'bottom'				=> str_replace(',', '', $post['bottom']),
				'dasar_harga'			=> $post['dasar_harga'],
				'komisi'				=> str_replace(',', '', $post['komisi']),
				'profit'				=> str_replace(',', '', $post['profit']),
				'keterangan'			=> $post['keterangan'],
				'harga_penawaran'		=> str_replace(',', '', $post['harga_penawaran']),
				'harga_penawaran_cust'	=> str_replace(',', '', $post['harga_penawaran_cust']),
				'harga_dolar'			=> $dolar,
				'price_sheet'			=> str_replace(',', '', $post['price_sheet']),
				'created_on'			=> date('Y-m-d H:i:s'),
				'created_by'			=> $this->auth->user_id(),
				'ket_detail'			=> $post['ket_detail'],
			];
			//Add Data
			$idtes = $this->db->insert('child_penawaran', $data);
		} else {
			$idtes = 0;
		}

		if ($idtes == 0) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Save Item Kurs Yang anda pilih tidak tersedia. Thanks ...',
				'code' => $code,
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Save Item. Thanks ...',
				'code' => $code,
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}


	public function SaveNewHeader()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Inventory_4_model->generate_code();

		$no_surat = $this->Inventory_4_model->BuatNomor();
		$this->db->trans_begin();
		$data = [
			'no_penawaran'			=> $code,
			'no_surat'				=> $no_surat,
			'tgl_penawaran'			=> date('Y-m-d'),
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'mata_uang'			    => $post['mata_uang'],
			'email_customer'		=> $post['email_customer'],
			'valid_until'			=> $post['valid_until'],
			'terms_payment'			=> $post['terms_payment'],
			'exclude_vat'			=> $post['exclude_vat'],
			'pengiriman'			=> $post['pengiriman'],
			'note'					=> $post['note'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'tahun'					=> date('Y-m-d')
		];
		//Add Data
		$this->db->insert('tr_penawaran', $data);

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
	public function SaveEditHeader()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran'];
		$no_surat	= $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'no_surat'				=> $no_surat,
			'tgl_penawaran'			=> date('Y-m-d'),
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'mata_uang'			=> $post['mata_uang'],
			'email_customer'		=> $post['email_customer'],
			'valid_until'			=> $post['valid_until'],
			'pengiriman'			=> $post['pengiriman'],
			'terms_payment'			=> $post['terms_payment'],
			'exclude_vat'			=> $post['exclude_vat'],
			'note'					=> $post['note'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'modified_on'			=> date('Y-m-d H:i:s'),
			'modified_by'			=> $this->auth->user_id(),
			'tahun'					=> date('Y-m-d')
		];
		//Add Data
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

	public function SaveCopyHeader()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;

		$code_old		= $post['no_penawaran_lama'];
		$no_surat_old	= $post['no_surat'];

		$code = $this->Inventory_4_model->generate_code();
		$no_surat = $this->Inventory_4_model->BuatNomor();
		$this->db->trans_begin();
		$data = [
			'no_penawaran'			=> $code,
			'no_surat'				=> $no_surat,
			'tgl_penawaran'			=> date('Y-m-d'),
			'id_customer'			=> $post['id_customer'],
			'pic_customer'			=> $post['pic_customer'],
			'mata_uang'				=> $post['mata_uang'],
			'email_customer'		=> $post['email_customer'],
			'valid_until'			=> $post['valid_until'],
			'terms_payment'			=> $post['terms_payment'],
			'exclude_vat'			=> $post['exclude_vat'],
			'pengiriman'			=> $post['pengiriman'],
			'note'					=> $post['note'],
			'id_sales'				=> $post['id_sales'],
			'nama_sales'			=> $post['nama_sales'],
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'tahun'					=> date('Y-m-d')
		];
		//Add Data
		$this->db->insert('tr_penawaran', $data);


		$this->db->select('*');
		$this->db->from('child_penawaran');
		$this->db->where('no_penawaran', $code_old);
		$query = $this->db->get();

		if ($query->num_rows()) {
			$new_author = $query->result_array();

			foreach ($new_author as $row => $author) {


				$data = [
					'id_child_penawaran'	=> $code,
					'id_category3'			=> $author['id_category3'],
					'no_penawaran'			=> $code,
					'bentuk_material'		=> $author['bentuk_material'],
					'id_bentuk'				=> $author['id_bentuk'],
					'thickness'				=> $author['thickness'],
					'density'				=> $author['density'],
					'lotno'					=> $author['lotno'],
					'length'				=> $author['length'],
					'width'					=> $author['width'],
					'forecast'				=> $author['forecast'],
					'kurs_terpakai'			=> $author['kurs_terpakai'],
					'inven1'				=> $author['inven1'],
					'bottom'				=> str_replace(',', '', $author['bottom']),
					'dasar_harga'			=> $author['dasar_harga'],
					'komisi'				=> str_replace(',', '', $author['komisi']),
					'profit'				=> str_replace(',', '', $author['profit']),
					'keterangan'			=> $author['keterangan'],
					'harga_penawaran'		=> str_replace(',', '', $author['harga_penawaran']),
					'harga_penawaran_cust'	=> str_replace(',', '', $author['harga_penawaran_cust']),
					'harga_dolar'			=> $author['harga_dolar'],
					'created_on'			=> date('Y-m-d H:i:s'),
					'created_by'			=> $this->auth->user_id()
				];
				//Add Data
				$idtes = $this->db->insert('child_penawaran', $data);
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

	public function saveEditPenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;


		$this->db->trans_begin();
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$sebulan = mktime(0, 0, 0, date('n'), date('j') - 30, date('Y'));
		$tirtydays = date("Y-m-d", $sebulan);
		$tglnow = date('d');
		$blnnow = date('m');
		if ($blnnow != '1') {
			$blnkmrn = $blnnow - 1;
			$yearkemaren = date('Y');
		} else {
			$blnkmrn = "12";
			$yearnow = date('Y');
			$yearkemaren = $yearnow - 1;
		}
		$kurs_terpakai = $post['kurs_terpakai'];
		if ($kurs_terpakai == 'spot') {
			$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
			$nominal = $kurs[0]->kurs;
		} elseif ($kurs_terpakai == '10') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} elseif ($kurs_terpakai == '30') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} else {
			$noinal = '1';
		}
		$id = $post['id_child_penawaran'];
		$dolar = $post['harga_penawaran'] / $nominal;
		$data = [
			'id_category3'			=> $post['id_category3'],
			'bentuk_material'		=> $post['bentuk_material'],
			'id_bentuk'				=> $post['id_bentuk'],
			'thickness'				=> $post['thickness'],
			'lotno'			     	=> $post['lotno'],
			'width'				    => $post['width'],
			'length'				=> $post['length'],
			'density'				=> $post['density'],
			'forecast'				=> $post['forecast'],
			'inven1'				=> $post['inven1'],
			'bottom'				=> str_replace(',', '', $post['bottom']),
			'dasar_harga'			=> $post['dasar_harga'],
			'komisi'				=> str_replace(',', '', $post['komisi']),
			'profit'				=> str_replace(',', '', $post['profit']),
			'kurs_terpakai'			=> $post['kurs_terpakai'],
			'keterangan'			=> $post['keterangan'],
			'harga_penawaran'		=> str_replace(',', '', $post['harga_penawaran']),
			'harga_penawaran_cust'	=> str_replace(',', '', $post['harga_penawaran_cust']),
			'price_sheet'	=> str_replace(',', '', $post['price_sheet']),
			'qty_sheet'	=> str_replace(',', '', $post['qty_sheet']),
			'harga_dolar'			=> $dolar,
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
		];
		//Add Data
		$this->db->where('id_child_penawaran', $id)->update("child_penawaran", $data);

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

	public function saveEditRevisiPenawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;


		$this->db->trans_begin();
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$sebulan = mktime(0, 0, 0, date('n'), date('j') - 30, date('Y'));
		$tirtydays = date("Y-m-d", $sebulan);
		$tglnow = date('d');
		$blnnow = date('m');
		if ($blnnow != '1') {
			$blnkmrn = $blnnow - 1;
			$yearkemaren = date('Y');
		} else {
			$blnkmrn = "12";
			$yearnow = date('Y');
			$yearkemaren = $yearnow - 1;
		}
		$kurs_terpakai = $post['kurs_terpakai'];
		if ($kurs_terpakai == 'spot') {
			$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
			$nominal = $kurs[0]->kurs;
		} elseif ($kurs_terpakai == '10') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} elseif ($kurs_terpakai == '30') {
			$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
			$nominal = $kurs[0]->nominal;
		} else {
			$noinal = '1';
		}
		$id = $post['id_child_penawaran'];
		$dolar = $post['harga_penawaran'] / $nominal;
		$data = [
			'id_category3'			=> $post['id_category3'],
			'bentuk_material'		=> $post['bentuk_material'],
			'id_bentuk'				=> $post['id_bentuk'],
			'thickness'				=> $post['thickness'],
			'lotno'			     	=> $post['lotno'],
			'width'				    => $post['width'],
			'length'				=> $post['length'],
			'density'				=> $post['density'],
			'forecast'				=> $post['forecast'],
			'inven1'				=> $post['inven1'],
			'bottom'				=> str_replace(',', '', $post['bottom']),
			'dasar_harga'			=> $post['dasar_harga'],
			'komisi'				=> str_replace(',', '', $post['komisi']),
			'profit'				=> str_replace(',', '', $post['profit']),
			'kurs_terpakai'			=> $post['kurs_terpakai'],
			'keterangan'			=> $post['keterangan'],
			'harga_penawaran'		=> str_replace(',', '', $post['harga_penawaran']),
			'harga_penawaran_cust'	=> str_replace(',', '', $post['harga_penawaran_cust']),
			'harga_dolar'			=> $dolar,
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id(),
			'ket_detail'			=> $post['ket_detail'],
		];
		//Add Data
		$this->db->where('id_child_penawaran', $id)->update("child_penawaran", $data);

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
	public function deletePenawaran()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$this->db->trans_begin();
		$this->db->delete('child_penawaran', array('id_child_penawaran' => $id));

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
			$this->db->delete('child_inven_compotition', array('id_category3' => $id));
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
			$this->db->delete('child_inven_dimensi', array('id_category3' => $id));
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

	public function modal_closing_penawaran($id = null)
	{
		$data = [
			'id' => $id
		];
		$this->load->view('modal_closing_penawaran', $data);
	}

	public function close_penawaran()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$id		= $post['id'];
		$ket		= $post['ket'];

		$this->db->where('no_penawaran', $id)->update("tr_penawaran", array('status' => 'Y', 'keterangan' => $ket));

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

	public function	copyHeader($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $this->Inventory_4_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$customers = $this->Inventory_4_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'head' => $head,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Copy Penawaran');
		$this->template->render('CopyHeader');
	}

	public function ajukanApproval($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $this->Inventory_4_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$customers = $this->Inventory_4_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'head' => $head,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Ajukan Approval Penawaran');
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
			'status_approval'		=> 1,
			'keterangan_approval'			=> $post['keterangan'],
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
		$data = $this->Inventory_4_model->CariPenawaranApproval();
		$this->template->set('results', $data);
		$this->template->title('Request Approval');
		$this->template->render('index_approval');
	}

	public function	ApprovalRevisi($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $this->Inventory_4_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$customers = $this->Inventory_4_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'head' => $head,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Approval Revisi Penawaran');
		$this->template->render('ApprovalRevisi');
	}

	public function SaveApprovalRevisi()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran_lama'];
		$this->db->trans_begin();
		$data = [
			'ket_approval'				=> $post['ket_approval'],
			'status_revisi'				=> $post['status_approval'],
			'disetujui_on'				=> date('Y-m-d H:i:s'),
			'disetujui_by'				=> $this->auth->user_id()
		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_penawaran", $data);

		$spkmarktg = [
			'status_revisi'				=> $post['status_approval']
		];
		//Edit Data
		$this->db->where('no_penawaran', $code)->update("tr_spk_marketing", $spkmarktg);

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


	public function	revisi($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $this->Inventory_4_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$customers = $this->Inventory_4_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang', 'deleted', $deleted);
		$data = [
			'customers' => $customers,
			'karyawan' => $karyawan,
			'head' => $head,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Revisi Penawaran');
		$this->template->render('AjukanRevisi');
	}
	public function SavePengajuanRevisi()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code		= $post['no_penawaran_lama'];
		$this->db->trans_begin();
		$data = [
			'alasan_revisi'				=> $post['alasan'],
			'kategori_revisi'			=> $post['kategori_revisi'],
			'status_revisi'				=> 1,
			'pengajuan_on'				=> date('Y-m-d H:i:s'),
			'pengajuan_by'				=> $this->auth->user_id()
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

	public function PrintHeaderWord($id)
	{

		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Inventory_4_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Inventory_4_model->PrintDetail($id);
		$this->load->view('PrintHeaderWord', $data);
	}

	public function tes_nomor()
	{
		$no_surat = $this->Inventory_4_model->BuatNomorNew();
		print_r($no_surat);
	}

	public function get_data_penawaran() {
		$this->Inventory_4_model->get_data_penawaran();
	}
}
