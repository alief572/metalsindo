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

class Incoming extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Incoming.View';
	protected $addPermission  	= 'Incoming.Add';
	protected $managePermission = 'Incoming.Manage';
	protected $deletePermission = 'Incoming.Delete';

	protected $viewPermission2 	= 'Request_Pembayaran_AP.View';
	protected $addPermission2  	= 'Request_Pembayaran_AP.Add';
	protected $managePermission2 = 'Request_Pembayaran_AP.Manage';
	protected $deletePermission2 = 'Request_Pembayaran_AP.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->library(array('Mpdf', 'upload', 'Image_lib'));
		$this->load->model(array(
			'Incoming/Pr_model',
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
		$data = $this->db->query("SELECT a.*, b.name_suplier as name_suplier FROM tr_incoming as a INNER JOIN master_supplier as b on a.id_suplier=b.id_suplier ORDER BY a.id_incoming DESC")->result();
		$this->template->set('results', $data);
		$this->template->title('Incoming');
		$this->template->render('index');
	}


	public function index_logistik()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->db->query("SELECT a.*, b.name_suplier as name_suplier FROM tr_incoming as a INNER JOIN master_supplier as b on a.id_suplier=b.id_suplier ORDER BY a.id_incoming DESC")->result();
		$this->template->set('results', $data);
		$this->template->title('Input Biaya Logistik');
		$this->template->render('index_logistik');
	}

	public function index_jurnal_unbilled()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->db->query("SELECT a.*, b.name_suplier as name_suplier FROM tr_incoming as a INNER JOIN master_supplier as b on a.id_suplier=b.id_suplier WHERE a.status_jurnal='0' AND a.status_logistik='1'  ORDER BY a.id_incoming DESC")->result();
		$this->template->set('results', $data);
		$this->template->title('Index Jurnal Unbilled');
		$this->template->render('index_jurnal_unbilled');
	}


	public function index_request_hutang()
	{
		$this->auth->restrict($this->viewPermission2);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->db->query("SELECT a.*, b.name_suplier as name_suplier FROM tr_incoming as a INNER JOIN master_supplier as b on a.id_suplier=b.id_suplier WHERE (a.status_jurnal='1' AND a.status_logistik='1') OR (a.rec_ap = 1)  ORDER BY a.id_incoming DESC")->result();
		$this->template->set('results', $data);
		$this->template->title('Index Request AP');
		$this->template->render('index_request_hutang');
	}


	public function request()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission2);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		// $po = $data = $this->db->query("SELECT * FROM tr_incoming WHERE status_jurnal='1' AND id_data='$id' ")->result();
		$po = $data = $this->db->query("SELECT * FROM tr_incoming WHERE id_data='$id' ")->result();

		$data = [
			'po' => $po,
		];
		$this->template->set('results', $data);
		$this->template->title('Request Pembayaran AP');
		$this->template->render('Request');
	}

	public function SaveRequest()
	{

		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		$this->db->trans_begin();

		$ppn = $post['ppn'];

		$data = array(
			'no_request'			=> $this->Pr_model->generate_request(),
			'tgl_invoice'			=> date('Y-m-d'),
			'no_po'		    		=> $post['no_po'],
			'no_surat'		        => $post['no_surat'],
			'id_suplier'		    => $post['id_supplier'],
			'loi'		        	=> $post['loi'],
			'kurs'    				=> str_replace(",", "", $post['kurs']),
			'nilai_invoice_kurs'    => str_replace(",", "", $post['request_kurs']),
			'nilai_invoice'			=> str_replace(",", "", $post['request']),
			'nilai_ppn'				=> str_replace(",", "", $post['ppn']),
			'grand_total_kurs'		=> str_replace(",", "", $post['request_kurs']),
			'grand_total'			=> str_replace(",", "", $post['request']) + $ppn,
			'sisa_invoice_kurs'     => str_replace(",", "", $post['request_kurs']),
			'sisa_invoice_idr'		=> str_replace(",", "", $post['request']) + $ppn,
			'kategori'				=> 'hutang',
			'created_by'			=> $this->auth->user_id(),
			'created_on'			=> date('Y-m-d H:i:s'),
			'keterangan'			=> $post['keterangan'],
		);
		$this->db->insert('tr_request', $data);



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


	public function add()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$po = $data = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$gudang	= $this->db->query("select * FROM ms_gudang ")->result();
		$suplier	= $this->db->query("select * FROM master_supplier WHERE deleted = '0' ")->result();
		$suplier2	= $this->db->query("select a.id_suplier, b.name_suplier FROM tr_purchase_order a
		                                join master_supplier b on a.id_suplier = b.id_suplier GROUP BY a.id_suplier, b.name_suplier ORDER BY b.name_suplier ASC ")->result();
		$data = [
			'po' => $po,
			'gudang' => $gudang,
			'suplier' => $suplier2,
		];
		$this->template->set('results', $data);
		$this->template->title('INCOMING');
		$this->template->render('Add');
	}
	public function Update()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $data = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM tr_incoming as a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' ")->result();
		$po = $data = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$gudang	= $this->db->query("select * FROM ms_gudang ")->result();
		$data = [
			'po' => $po,
			'head' => $head,
			'gudang' => $gudang,
		];
		$this->template->set('results', $data);
		$this->template->title('INCOMING');
		$this->template->render('Edit');
	}
	public function edit()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $this->db->query("SELECT * FROM tr_purchase_order  WHERE no_po = '$id' ")->result();
		$comp	= $this->db->query("select a.*, b.nominal as nominal_harga FROM ms_compotition as a inner join child_history_lme as b on b.id_compotition=a.id_compotition where a.deleted='0' and b.status='0' ")->result();
		$detail = $this->db->query("SELECT * FROM dt_trans_po  WHERE no_po = '$id' ")->result();
		$supplier = $data = $this->db->query("SELECT a.* FROM master_supplier as a INNER JOIN dt_trans_pr as b on b.suplier = a.id_suplier INNER JOIN tr_purchase_request as c on b.no_pr = c.no_pr WHERE c.status = '2' ")->result();
		$customers = $this->Pr_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Pr_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Pr_model->get_data('mata_uang', 'deleted' . $deleted);
		$data = [
			'head' => $head,
			'comp' => $comp,
			'detail' => $detail,
			'supplier' => $supplier,
			'customers' => $customers,
			'karyawan' => $karyawan,
			'mata_uang' => $mata_uang,
		];
		$this->template->set('results', $data);
		$this->template->title('Purchase Order');
		$this->template->render('Edit');
	}
	public function Lihat()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $data = $this->db->query("SELECT a.* FROM tr_incoming as a  WHERE a.id_data='" . $id . "' ")->result();
		$po = $data = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$detail = $data = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM dt_incoming a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' ")->result();
		$gudang	= $this->db->query("select * FROM ms_gudang ")->result();
		$data = [
			'po' => $po,
			'head' => $head,
			'gudang' => $gudang,
			'detail' => $detail,
		];
		$this->template->set('results', $data);
		$this->template->title('INCOMING');
		$this->template->render('View');
	}
	function CariKurs()
	{

		$loi	= $_GET['loi'];
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
		$k =  number_format($nomkurs, 2);
		$k10 =  number_format($nomkurs10, 2);
		$k30 =  number_format($nomkurs30, 2);
		if ($loi == 'Import') {
			echo "
				<table class='col-sm-12' border='1' cellspacing='0'>
					<tr>
						<th><center>Kurs On The Spot</center></th>
						<th><center>Kurs 10 Hari</center></th>
						<th><center>Kurs 30 Hari</center></th>
					</tr>
					<tr>
						<td><center>Rp. $k  ,-</center></td>
						<td><center>Rp. $k10  ,-</center></td>
						<td><center>Rp. $k30  ,-</center></td>
					</tr>
				<table>
		";
		} else {
		};
	}
	public function PrintHeader1($id)
	{
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Pr_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Pr_model->PrintDetail($id);
		$this->load->view('PrintHeader', $data);
	}
	public function PrintHeader($id)
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		$data['header'] = $this->Pr_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Pr_model->PrintDetail($id);
		$this->load->view('PrintHeader', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(0, 0, 0, 0));
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
		$head = $this->Pr_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$customers = $this->Pr_model->get_data('master_customers', 'deleted', $deleted);
		$karyawan = $this->Pr_model->get_data('ms_karyawan', 'deleted', $deleted);
		$mata_uang = $this->Pr_model->get_data('mata_uang', 'deleted', $deleted);
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
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$deleted = '0';
		$detail = $this->Pr_model->getpenawaran($id);
		$header = $this->Pr_model->getHeaderPenawaran($id);
		$data = [
			'detail' => $detail,
			'header' => $header
		];
		$this->template->set('results', $data);
		$this->template->title('Penawaran');
		$this->template->render('detail');
	}

	public function editPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$penawaran = $this->Pr_model->get_data('child_penawaran', 'id_child_penawaran', $id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('editPenawaran');
	}

	public function TambahData()
	{
		$id 	= $_GET['id'];
		$no 	= $_GET['no'];
		$no_po 	= $_GET['nopo'];


		$mt     = $this->db->query("SELECT * FROM tr_purchase_order WHERE no_po = '" . $no_po . "'  ")->row();
		$no_surat = $mt->no_surat;
		$material = $this->db->query("SELECT * FROM dt_trans_po WHERE no_po = '" . $no_po . "' AND close_po = 'N' ")->result();
		$customer	= $this->db->query("select * FROM master_customers ")->result();
		$gudang	= $this->db->query("select * FROM ms_gudang WHERE id_gudang IN(1,3,5) ")->result();
		$list_tipe_material = $this->db->select('a.*')->from('ms_bentuk a')->where_in('a.id_bentuk', ['B2000001', 'B2000002'])->where('a.deleted', 0)->get()->result();
		foreach ($material as $material) {
			$no_pr  = $material->idpr;
			$id_dt_po  = $material->id_dt_po;



			$totalweight = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '" . $no_pr . "'  ")->row();
			$id_material  = $material->idmaterial;

			$idroll = $this->db->query("SELECT MAX(id_roll) as id_roll FROM stock_material WHERE id_category3='$id_material'")->row();



			$bntk = $this->db->query("SELECT id_bentuk as bentuk FROM ms_inventory_category3 WHERE id_category3='$id_material'")->row();

			$bentuk = $bntk->bentuk;
			$dens = $this->db->query("SELECT density FROM view_material WHERE id_category3='$id_material' AND bentuk='$bentuk'")->row();

			$thick = $this->db->query("SELECT nilai_dimensi as thickness FROM view_material WHERE id_category3='$id_material' AND bentuk='$bentuk' AND nama='THICKNESS'")->row();

			$incoming = $this->db->query("SELECT SUM(width_recive) AS incoming FROM dt_incoming WHERE id_dt_po='$id_dt_po' ")->row();



			$roll = substr($idroll->id_roll, -3) + 1;
			$idroll = $id_material . "-" . str_pad($roll, 3, "0", STR_PAD_LEFT);

			$no++;
			echo "
		<tr id='trmaterial_" . $id . "_" . $no . "'> 
		
		<td				hidden	><input  type='date' 		value='" . $tanggal . "'				class='form-control input-sm' id='dt_tanggal_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][tanggal]' >
		<input  type='text' 		value='" . $no_po . "'		            class='form-control input-sm' id='no_po" . $id . "_" . $no . "' 			        required name='dt[" . $id . "][detail][" . $no . "][no_po]' 	readonly>
		<input  type='text' 		value='" . $material->idmaterial . "'	class='form-control input-sm' id='dt_idmaterial_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][id_material]' 	readonly>
		<input  type='text' 		value='" . $material->id_dt_po . "'		class='form-control input-sm' id='dt_iddtpo_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][iddtpo]' 	readonly>
		<input  type='text' 		value='" . $material->panjang . "'		class='form-control input-sm text-right' id='dt_length_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][length]' 		readonly>
		<input  type='number' 		value='" . $material->lebar . "'		class='form-control input-sm text-right' id='dt_width_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][width]'  		readonly>
		<input  type='text' 		value='" . number_format($material->width, 2) . "'		class='form-control input-sm text-right' id='dt_weight_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][weight]' 		readonly>
		<input  type='text' 		value='" . number_format($material->totalwidth, 2) . "'	class='form-control input-sm text-right' id='dt_qtyorder_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][qtyorder]'     readonly>
		<input  type='text' 		value='1'									class='form-control input-sm text-right autoNumeric' id='dt_qtyrecive_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][qtyrecive]' 	>
		<input  type='text' 											class='form-control input-sm' id='dt_panjang2_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][panjang2]' 		readonly>
		
		</td>
		
		
		<td hidden ><input  type='text' 		value='" . $no_surat . "'		        class='form-control input-sm' id='no_surat" . $id . "_" . $no . "' 			        required name='dt[" . $id . "][detail][" . $no . "][no_surat]' 	readonly></td>
		
		<td width ='130'>
		
		" . $material->namamaterial . "
		<input  type='hidden' 		value='" . $material->namamaterial . "'	class='form-control input-sm' id='dt_namamaterial_" . $id . "_" . $no . "' 	required name='dt[" . $id . "][detail][" . $no . "][nama_material]' readonly></td>
		
		<td>
		Thickness : " . $thick->thickness . " <br>
		Density   : " . $dens->density . " <br>
		Width     : " . number_format($material->width, 2) . " <br>
		Length     : " . number_format($material->panjang, 2) . " <br>
		Ttl PO    : " . number_format($material->totalwidth, 2) . "<br>
		Blm Diterima: " . number_format(($material->totalwidth - $incoming->incoming) * -1, 2) . " <br>

		
		<input  type='hidden' 		value='" . number_format($thick->thickness, 2) . "'		class='form-control input-sm text-right' id='dt_thickness_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][thickness]' 		readonly>
		
		<input  type='hidden' 		value='" . number_format($dens->density, 2) . "'		class='form-control input-sm text-right' id='dt_density_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][density]' 		readonly>
		
		</td>
		
		<td><input  type='text' 		value='" . $idroll . "'	class='form-control input-sm' id='id_roll_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][id_roll]' 	readonly></td>
		
		<td	width='50'><input type='text' class='form-control input-sm text-right autoNumeric widthrecive' id='dt_widthrecive" . $id . "_" . $no . "' required name='dt[" . $id . "][detail][" . $no . "][widthrecive]'  onBlur='cariPanjang($id,$no)' data-numb1='" . $id . "' data-numb2='" . $no . "'  ></td>
		<td	width ='130'><input  type='text' 											class='form-control input-sm' id='dt_lotno_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][loto]' 		></td>
		
		
			
		<td style='font-size:90%'><select style='font-size:90%' class='form-control chosen-select' id='dt_gudang_" . $id . "_" . $no . "'
		name='dt[" . $id . "][detail][" . $no . "][gudang]'  onchange='customerSelect($id,$no)'>";
			echo "<option value=''>Pilih Gudang</option>";
			foreach ($gudang as $gudangx) {
				//$sel = ($gudangx->id_gudang == 3)?'selected':'';
				echo "<option value='$gudangx->id_gudang'>$gudangx->nama_gudang</option>";
			};
			echo "</select></td>
		<td style='font-size:90%'><select style='font-size:90%' class='form-control chosen-select' id='dt_customer_" . $id . "_" . $no . "'
		name='dt[" . $id . "][detail][" . $no . "][customer]' >";
			echo "<option value=''>Pilih Customer</option>";
			foreach ($customer as $customerx) {
				//$sel = ($customerx->id_gudang == 3)?'selected':'';		
				echo "<option value='$customerx->name_customer'>$customerx->name_customer</option>";
			};
			echo "</select></td>
		
		<td><input  type='text' class='form-control input-sm autoNumeric' id='dt_aktual_" . $id . "_" . $no . "' 	onBlur='cariSelisih($id,$no)'		required name='dt[" . $id . "][detail][" . $no . "][aktual]' 	Placeholder='Berat Aktual'	></td>

		<td>
			<input type='text' class='form-control input-sm autoNumeric' id='dt_aktual_".$id."_".$no."' name='dt[".$id."][detail][".$no."][qty_sheet]' placeholder='Qty Sheet'>
		</td>
		
		<td						><input  type='text' 											class='form-control input-sm autoNumeric' id='dt_selisih_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][selisih]' 	readonly	></td>
		<td				       hidden ><input  type='text' 		value='" . number_format($material->hargasatuan, 3) . "'									class='form-control input-sm text-right' id='dt_hargasatuan_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][hargasatuan]' 	></td>
		
		<td align='center'>
			<button type='button' class='btn btn-sm btn-danger cancelSubPart' data-no1='" . $id . "' data-no2='" . $no . "' title='Delete Part'><i class='fa fa-close'></i></button>
			<button type='button' class='btn btn-sm btn-primary repeatSubPart' data-no1='" . $id . "' data-no2='" . $no . "'  data-lot='" . $roll . "' data-id='" . $material->id_dt_po . "' id='tombol" . $no . "' 		 title='Repeat Part'><i class='fa fa-retweet'></i></button>
		</td>
		</tr>";
		}
	}

	public function TambahDataRepeat()
	{
		$id 	= $_GET['id'];
		$no_sebelumnya = $_GET['no'];
		$no 	= $_GET['no'] + 100;
		$no_po 		= substr($_GET['nopo'], 0, 8);
		$no_po_id 	= $_GET['nopo'];
		//$idroll 	= $_GET['idroll']+$_GET['no'];

		$roll = $_GET['idroll'] + 1;
		$idroll = $id_material . "-" . str_pad($roll, 3, "0", STR_PAD_LEFT);

		$mt     = $this->db->query("SELECT * FROM tr_purchase_order WHERE no_po = '" . $no_po . "'  ")->row();
		$no_surat = $mt->no_surat;
		$gudang	= $this->db->query("select * FROM ms_gudang WHERE id_gudang IN(1,3,5)")->result();
		$customer	= $this->db->query("select * FROM master_customers ")->result();
		$material = $this->db->query("SELECT * FROM dt_trans_po WHERE id_dt_po = '" . $no_po_id . "'  ")->result();
		$list_tipe_material = $this->db->select('a.*')->from('ms_bentuk a')->where_in('a.id_bentuk', ['B2000001', 'B2000002'])->where('a.deleted', 0)->get()->result();
		foreach ($material as $material) {
			$no_pr  = $material->idpr;
			$id_material = $material->idmaterial;
			$idroll = $id_material . "-" . str_pad($roll, 3, "0", STR_PAD_LEFT);

			$bntk = $this->db->query("SELECT id_bentuk as bentuk FROM ms_inventory_category3 WHERE id_category3='$id_material'")->row();

			$bentuk = $bntk->bentuk;
			$dens = $this->db->query("SELECT density FROM view_material WHERE id_category3='$id_material' AND bentuk='$bentuk'")->row();

			$thick = $this->db->query("SELECT nilai_dimensi as thickness FROM view_material WHERE id_category3='$id_material' AND bentuk='$bentuk' AND nama='THICKNESS'")->row();

			$no++;
			echo "
		<tr id='trmaterial_" . $id . "_" . $no . "'> 
		<td				hidden	><input  type='date' 		value='" . $tanggal . "'				class='form-control' id='dt_tanggal_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][tanggal]' >
		</td>
		<td				hidden  ><input  type='text' 		value='" . $no_po . "'		            class='form-control' id='no_po" . $id . "_" . $no . "' 			        required name='dt[" . $id . "][detail][" . $no . "][no_po]' 	readonly></td>
		<td				hidden ><input  type='hidden' 		value='" . $no_surat . "'		        class='form-control' id='no_surat" . $id . "_" . $no . "' 			        required name='dt[" . $id . "][detail][" . $no . "][no_surat]' 	readonly>
								<input  type='text' 		value='" . $no_sebelumnya . "'		        class='form-control' id='no_sebelumnya" . $id . "_" . $no . "' 			        required name='dt[" . $id . "][detail][" . $no . "][no_sebelumnya]' 	readonly>
		
		</td>
		<td				  ><input  type='hidden' 		value='" . $material->id_dt_po . "'		class='form-control' id='dt_iddtpo_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][iddtpo]' 	readonly></td>
		<td				hidden	><input  type='text' 		value='" . $material->idmaterial . "'	class='form-control' id='dt_idmaterial_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][id_material]' 	readonly></td>
		<td						><input  type='hidden' 		value='" . $material->namamaterial . "'	class='form-control' id='dt_namamaterial_" . $id . "_" . $no . "' 	required name='dt[" . $id . "][detail][" . $no . "][nama_material]' readonly></td>
		<td				hidden	><input  type='text' 		value='" . $material->panjang . "'		class='form-control' id='dt_length_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][length]' 		readonly></td>
		<td				hidden	><input  type='number' 		value='" . $material->lebar . "'		class='form-control' id='dt_width_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][width]'  		readonly></td>
		
		<td				hidden	>
		
		<input  type='hidden' 		value='" . number_format($thick->thickness, 2) . "'		class='form-control input-sm text-right' id='dt_thickness_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][thickness]' 		readonly>		
		<input  type='hidden' 		value='" . number_format($dens->density, 2) . "'		class='form-control input-sm text-right' id='dt_density_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][density]' 		readonly>
		<input  type='number' 		value='" . $material->width . "'		class='form-control' id='dt_weight_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][weight]' 		readonly>
		
		</td>
		
		<td				hidden		><input  type='hidden' 		value='" . $material->totalwidth . "'class='form-control' id='dt_qtyorder_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][qtyorder]'     readonly></td>
		
		<td				hidden><input  type='text' 		value='1'									class='form-control text-right input-sm autoNumeric' id='dt_qtyrecive_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][qtyrecive]' 	></td>
		
		<td                 	><input  type='text' 		value='" . $idroll . "'	class='form-control input-sm' id='id_roll_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][id_roll]' 	readonly></td>
		
		
		<td						><input  type='text' 											class='form-control text-right input-sm autoNumeric widthrecive' id='dt_widthrecive" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][widthrecive]' 	 onBlur='cariPanjang($id,$no)'		data-numb1='" . $id . "' data-numb2='" . $no . "'	></td>
		
		<td						><input  type='text' 											class='form-control input-sm ' id='dt_lotno_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][loto]' 		></td>
		
		<td						hidden><input  type='text' 											class='form-control input-sm' id='dt_panjang2_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][panjang2]' 		readonly></td>
		
		
		<td style='font-size:90%'><select style='font-size:90%' class='form-control chosen-select' id='dt_gudang_" . $id . "_" . $no . "'
		name='dt[" . $id . "][detail][" . $no . "][gudang]' onchange='customerSelect($id,$no)'>";
			echo "<option value=''>Pilih Gudang</option>";
			foreach ($gudang as $gudangx) {
				//$sel = ($gudangx->id_gudang == 3)?'selected':'';
				echo "<option value='$gudangx->id_gudang'>$gudangx->nama_gudang</option>";
			};
			echo "</select></td>
		
		
		<td style='font-size:90%'><select style='font-size:90%' class='form-control chosen-select' id='dt_customer_" . $id . "_" . $no . "'
		name='dt[" . $id . "][detail][" . $no . "][customer]' >";
			echo "<option value=''>Pilih Customer</option>";
			foreach ($customer as $customerx) {
				//$sel = ($customerx->id_gudang == 3)?'selected':'';

				echo "<option value='$customerx->name_customer'>$customerx->name_customer</option>";
			};
			echo "</select></td>
		
		
		<td						><input  type='text' 											class='form-control input-sm autoNumeric' id='dt_aktual_" . $id . "_" . $no . "' 		onBlur='cariSelisih($id,$no)'	required name='dt[" . $id . "][detail][" . $no . "][aktual]' 	Placeholder='Berat Aktual'	></td>
		
		<td						><input  type='text' 											class='form-control input-sm autoNumeric' id='dt_selisih_" . $id . "_" . $no . "' 			required name='dt[" . $id . "][detail][" . $no . "][selisih]'  	readonly	></td>
		<td				       hidden ><input  type='text' 		value='" . number_format($material->hargasatuan, 3) . "'									class='form-control input-sm text-right' id='dt_hargasatuan_" . $id . "_" . $no . "' 		required name='dt[" . $id . "][detail][" . $no . "][hargasatuan]' 	></td>
		
		<td align='center'>
			<button type='button' class='btn btn-sm btn-danger cancelSubPart' data-no1='" . $id . "' data-no2='" . $no . "' title='Delete Part'><i class='fa fa-close'></i></button>
			<button type='button' class='btn btn-sm btn-primary repeatSubPart' data-no1='" . $id . "' data-no2='" . $no . "' data-id='" . $material->id_dt_po . "' data-lot='" . $roll . "' id='tombol" . $no . "'  title='Repeat Part'><i class='fa fa-retweet'></i></button>
		</td> 
		</tr>";
		}
	}


	public function View($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$header = $this->db->query("SELECT * FROM tr_purchase_request WHERE no_pr = '$id' ")->result();
		$detail = $this->db->query("SELECT * FROM dt_trans_pr WHERE no_pr = '$id' ")->result();
		$data = [
			'header' => $header,
			'detail' => $detail,
		];
		$this->template->set('results', $data);
		$this->template->title('View P.R');
		$this->template->render('View');
	}

	public function viewPenawaran($id)
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$penawaran = $this->Pr_model->get_data('child_penawaran', 'id_child_penawaran', $id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
		$this->template->set('results', $data);
		$this->template->title('Edit Penawaran');
		$this->template->render('viewPenawaran');
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
		$headpenawaran = $this->Pr_model->get_data('tr_penawaran', 'no_penawaran', $id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'inventory_3' => $inventory_3,
			'headpenawaran' => $headpenawaran
		];
		$this->template->set('results', $data);
		$this->template->title('Add Penawaran');
		$this->template->render('AddPenawaran');
	}


	function get_pib()
	{
		$pib = $_GET['pib'];
		$kurs = $this->db->query("SELECT kurs_bayar FROM tr_pib_payment WHERE kd_pembayaran = '$pib' ")->row();
		$dt = $kurs->kurs_bayar;
		echo "$dt";
	}

	function GetMaterial()
	{
		$loop = $_GET['jumlah'] + 1;
		$tanggal = date('Y-m-d');
		$no_po = $_GET['no_po'];
		$material = $this->db->query("SELECT * FROM dt_trans_po WHERE no_po = '$no_po'  ")->result();
		foreach ($material as $material) {
			$loop++;
			$list = $this->db->query("SELECT SUM(qty_recive) as jumlahdatang FROM dt_incoming WHERE id_dt_po = '" . $material->id_dt_po . "'  ")->result();
			$qty = $material->qty;
			$qty_recive = $list[0]->jumlahdatang;
			$hasilqty = $qty - $qty_recive;
			if ($hasilqty >= '1') {
				echo "
		<tr id='trmaterial_$loop'>
		<th				hidden	><input  type='text' 		value='" . $material->id_dt_po . "'		class='form-control' id='dt_iddtpo_" . $loop . "' 			required name='dt[" . $loop . "][iddtpo]' 	readonly></th>
		<th				hidden	><input  type='text' 		value='" . $material->idmaterial . "'	class='form-control' id='dt_idmaterial_" . $loop . "' 		required name='dt[" . $loop . "][idmaterial]' 	readonly></th>
		<th						><input  type='date' 		value='" . $tanggal . "'				class='form-control' id='dt_tanggal_" . $loop . "' 	required name='dt[" . $loop . "][tanggal]' ></th>
		<th						><input  type='text' 		value='" . $material->namamaterial . "'	class='form-control' id='dt_namamaterial_" . $loop . "' 	required name='dt[" . $loop . "][namamaterial]' readonly></th>
		<th						><input  type='text' 		value='" . $material->panjang . "'		class='form-control' id='dt_length_" . $loop . "' 			required name='dt[" . $loop . "][length]' 		readonly></th>
		<th						><input  type='number' 		value='" . $material->lebar . "'		class='form-control' id='dt_width_" . $loop . "' 			required name='dt[" . $loop . "][width]'  		readonly></th>
		<th						><input  type='number' 		value='" . $material->width . "'		class='form-control' id='dt_weight_" . $loop . "' 			required name='dt[" . $loop . "][weight]' 		readonly></th>
		<th						><input  type='number' 		value='" . $hasilqty . "'				class='form-control' id='dt_qtyorder_" . $loop . "' 		required name='dt[" . $loop . "][qtyorder]'     readonly></th>
		<th						><input  type='number' 											class='form-control' id='dt_qtyrecive_" . $loop . "' 		required name='dt[" . $loop . "][qtyrecive]' 	></th>
		<th						><input  type='text' 											class='form-control' id='dt_lotno_" . $loop . "' 			required name='dt[" . $loop . "][loto]' 		></th>
		</tr>
		";
			} else {
			};
		}
	}
	function FormPo()
	{
		$id = $_GET['id'];
		$id_suplier = $_GET['id_suplier'];
		$no = 0;
		// $listpo = $this->db->query("SELECT a.no_po, a.no_surat FROM tr_purchase_order a WHERE a.id_suplier='".$id_suplier."' ")->result();
		$listpo = $this->db->query("SELECT 
										a.no_po, 
										a.no_surat,
										a.loi
									FROM 
										dt_trans_po b 
										LEFT JOIN tr_purchase_order a ON b.no_po=a.no_po 
									WHERE 
										a.id_suplier='" . $id_suplier . "' AND a.status = '2' GROUP BY a.no_po ")->result();

		echo "
	<div id='po_" . $id . "'>
		<input type='hidden' id='pancingan_" . $id . "' value='1'>
		<div class='col-md-4'>
					<label >No. PO</label>
		</div>
		<div class='col-md-4'>
				<select id='dt_nopo_" . $id . "' name='dt[" . $id . "][nopo]' class='form-control input-md chosen-select check_ros' onchange='return TambahMaterial(" . $id . ")' required>
						<option value=''>--Pilih--</option>";
		foreach ($listpo as $listpo) {
			$check_ros = $this->db->select('a.id')->from('tr_ros a')->like('a.no_po', $listpo->no_surat, 'both')->get()->num_rows();
			if ($listpo->loi == 'Import') {
				if ($check_ros > 0) {
					echo "<option value='$listpo->no_po' >$listpo->no_surat</option>";
				}
			} else {
				echo "<option value='$listpo->no_po' >$listpo->no_surat</option>";
			}
		}
		echo "</select>
		</div>
		<div class='col-md-4'>
			<button type='button' class='btn btn-sm btn-danger delete_header' title='Hapus Data' data-role='qtip' data-nomor='" . $id . "' onClick='return HapusItem(" . $id . ")'><i class='fa fa-close'></i>Delete</button>
		</div>
		<br>
		<div class='form-group row' >
			<table class='table table-bordered table-striped'>
			<thead>
			<tr class='bg-blue'>
			<th width='5' hidden>No PO</th>					 
			<th width='5'>Material</th> 
			<th width='5'>Keterangan</th>
			<th width='5'>Id</th>
			<th width='60'>Berat</th>
			<th width='5'>Nomor Lot</th>
			<th width='5'>Gudang</th>
			<th width='5'>Customer</th>
			<th width='5'>Berat Aktual</th>
			<th width='5'>Qty / Sheet</th>
			<th width='5'>Selisih</th>
			<th width='5' hidden>Harga Satuan</th>
			<th width='5'>Action</th>
			</tr>
			</thead>
			<tbody id='data_request_" . $id . "'>
			</tbody>
			</table>
		</div>
	</div>";
	}
	function GantiTombol()
	{
		$id = $_GET['id'] + 1;
		echo "<button type='button' class='btn btn-sm btn-success' title='Ambil' id='tbh_ata' onClick='return addPO(" . $id . ");'><i class='fa fa-plus'></i>Add</button>
		";
	}
	function HitungHarga()
	{
		$dt_hargasatuan = $_GET['dt_hargasatuan'];
		$dt_qty = $_GET['dt_qty'];
		$loop = $_GET['id'];
		$isi =  $dt_hargasatuan * $dt_qty;
		echo "<input readonly type='text' value='" . $isi . "' 	class='form-control' id='dt_jumlahharga_" . $loop . "' 	required name='dt[" . $loop . "][jumlahharga]' >";
	}
	function TotalWeight()
	{
		$dt_width = $_GET['dt_width'];
		$dt_qty = $_GET['dt_qty'];
		$loop = $_GET['id'];
		$isi =  $dt_width * $dt_qty;
		echo "<input readonly type='text' value='" . $isi . "' 	class='form-control' id='dt_totalwidth_" . $loop . "' 	required name='dt[" . $loop . "][totalwidth]' >";
	}
	function CariIdMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->idmaterial;
		echo "<input readonly type='text' value='" . $isi . "' 	class='form-control' id='dt_idmaterial_" . $loop . "' 	required name='dt[" . $loop . "][idmaterial]' >";
	}
	function CariNamaMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->nama_material;
		echo "<input readonly type='text' value='" . $isi . "' 	class='form-control' id='dt_namamaterial_" . $loop . "' 	required name='dt[" . $loop . "][namamaterial]' >";
	}
	function CariDescripitionMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->keterangan;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_description_" . $loop . "' 	required name='dt[" . $loop . "][description]' >";
	}
	function CariPanjangMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->length;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_panjang_" . $loop . "' 	required name='dt[" . $loop . "][panjang]' >";
	}
	function CariLebarMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->width;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_lebar_" . $loop . "' 	required name='dt[" . $loop . "][lebar]' >";
	}
	function FormInputKurs()
	{
		$loi = $_GET['loi'];
		if ($loi == "Import") {
			echo "
		<div class='form-group row'>
			<div class='col-md-4'>
				<label>Kurs</label>
			</div>
			<div class='col-md-8'>
				<input type='number' class='form-control' id='nominal_kurs'  required name='nominal_kurs'  placeholder='Nominal Kurs'>
			</div>
		</div>
		";
		} else {
			echo "
		<div class='form-group row' hidden>
			<div class='col-md-4'>
				<label>Kurs</label>
			</div>
			<div class='col-md-8'>
				<input type='number' class='form-control' id='nominal_kurs'  required name='nominal_kurs' readonly placeholder='Nominal Kurs'>
			</div>
		</div>
		";
		}
	}
	function CariQtyMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->qty;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_qty_" . $loop . "' onkeyup='HitungHarga(" . $loop . ")' 	required name='dt[" . $loop . "][qty]' >";
	}
	function CariweightMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->weight;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_width_" . $loop . "' onkeyup='HitungHarga(" . $loop . ")' 	required name='dt[" . $loop . "][width]' >";
	}
	function CariTweightMaterial()
	{
		$idpr = $_GET['idpr'];
		$loop = $_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->totalweight;
		echo "<input  type='text' value='" . $isi . "' 	class='form-control' id='dt_totalwidth_" . $loop . "' onkeyup='HitungHarga(" . $loop . ")' 	required name='dt[" . $loop . "][totalwidth]' >";
	}
	function CariIdBentuk()
	{
		$id_category3 = $_GET['idmaterial'];
		$loop = $_GET['id'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$id_bentuk = $kategory3[0]->id_bentuk;
		echo "<input readonly type='text' class='form-control' value='" . $id_bentuk . "' id='dt_idbentuk_" . $loop . "' required name='dt[" . $loop . "][idbentuk]' >";
	}
	function CariSupplier()
	{
		$id_category3 = $_GET['idmaterial'];
		$loop = $_GET['id'];
		$supplier	= $this->db->query("SELECT a.*, b.name_suplier as supname FROM child_inven_suplier as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.id_category3 = '$id_category3' ")->result();
		echo "<select class='form-control' id='dt_suplier_" . $loop . "' name='dt[" . $loop . "][suplier]'>
		<option value=''>--Pilih--</option>";
		foreach ($supplier as $supplier) {
			echo "<option value='" . $supplier->id_suplier . "'>" . $supplier->supname . "</option>";
		}
		echo "</select>";
	}
	function CariTHarga()
	{
		$hargatotal = $_GET['hargatotal'];
		$jumlahharga = $_GET['jumlahharga'];
		$isi = $hargatotal + $jumlahharga;
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='hargatotal'  onkeyup required name='hargatotal' >";
	}
	function CariTDiskon()
	{
		$diskontotal = $_GET['diskontotal'];
		$diskon = $_GET['diskon'] / 100;
		$jumlahharga = $_GET['jumlahharga'];
		$val1 = $jumlahharga * $diskon;
		$isi = $val1 + $diskontotal;
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='diskontotal'  onkeyup required name='diskontotal' >";
	}
	function CariTPajak()
	{
		$taxtotal = $_GET['taxtotal'];
		$pajak = $_GET['pajak'] / 100;
		$jumlahharga = $_GET['jumlahharga'];
		$val1 = $jumlahharga * $pajak;
		$isi = $val1 + $taxtotal;
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='taxtotal'  onkeyup required name='taxtotal' >";
	}
	function CariTSum()
	{
		$taxtotal = $_GET['taxtotal'];
		$pajak = $_GET['pajak'] / 100;
		$jumlahharga = $_GET['jumlahharga'];
		$val1 = $jumlahharga * $pajak;
		$isi1 = $val1 + $taxtotal;
		$diskontotal = $_GET['diskontotal'];
		$diskon = $_GET['diskon'] / 100;
		$val2 = $jumlahharga * $diskon;
		$isi2 = $val2 + $diskontotal;
		$hargatotal = $_GET['hargatotal'];
		$isi3 = $hargatotal + $jumlahharga;
		$isi = $isi1 + $isi2 + $isi3;
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='subtotal'  onkeyup required name='subtotal' >";
	}
	function CariMinHarga()
	{
		$hargatotal = $_GET['hargatotal'];
		$jumlahharga = $_GET['jumlahharga'];
		$isi = $hargatotal - $jumlahharga;
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='hargatotal'  onkeyup required name='hargatotal' >";
	}
	function CariMinDiskon()
	{
		$diskontotal = $_GET['diskontotal'];
		$diskon = $_GET['diskon'] / 100;
		$jumlahharga = $_GET['jumlahharga'];
		$val1 = $jumlahharga * $diskon;
		$isi = $val1 - $diskontotal;
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='diskontotal'  onkeyup required name='diskontotal' >";
	}
	function CariMinPajak()
	{
		$taxtotal = $_GET['taxtotal'];
		$pajak = $_GET['pajak'] / 100;
		$jumlahharga = $_GET['jumlahharga'];
		$val1 = $jumlahharga * $pajak;
		$isi = $val1 - $taxtotal;
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='taxtotal'  onkeyup required name='taxtotal' >";
	}
	function CariMinSum()
	{
		$taxtotal = $_GET['taxtotal'];
		$pajak = $_GET['pajak'] / 100;
		$jumlahharga = $_GET['jumlahharga'];
		$val1 = $jumlahharga * $pajak;
		$isi1 = $val1 - $taxtotal;
		$diskontotal = $_GET['diskontotal'];
		$diskon = $_GET['diskon'] / 100;
		$val2 = $jumlahharga * $diskon;
		$isi2 = $val2 - $diskontotal;
		$hargatotal = $_GET['hargatotal'];
		$isi3 = $hargatotal - $jumlahharga;
		$isi = $isi1 + $isi2 + $isi3;
		echo "<input readonly type='text' value='" . $isi . "' class='form-control' id='subtotal'  onkeyup required name='subtotal' >";
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

	public function Approved()
	{
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'status' 		=> '2',
		];

		$this->db->trans_begin();
		$this->db->where('no_po', $id)->update("tr_purchase_order", $data);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$status	= array(
				'pesan'		=> 'Gagal Approve P.R. Thanks ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
				'pesan'		=> 'Success Approve P.R. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($status);
	}
	function get_inven2()
	{
		$inventory_1 = $_GET['inventory_1'];
		$data = $this->Pr_model->level_2($inventory_1);
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
		$data = $this->Pr_model->level_3($inventory_2);

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

	public function SaveNew()
	{
		$Arr_Kembali	= array();
		$post = $this->input->post();


		// print_r($post);
		// exit;

		$code = $this->Pr_model->generate_code();
		$id_data = $this->Pr_model->BuatID();
		$no_surat = $this->Pr_model->BuatNomor();
		$data			= $this->input->post();
		$session 		= $this->session->userdata('app_session');
		$Detail 	= $data['dt'];
		$ArrHeader	 = [
			'id_data'			=> $id_data,
			'id_incoming'		=> $no_surat,
			'id_suplier'		=> $post['id_suplier'],
			'id_gudang'			=> $post['id_gudang'],
			'tanggal'			=> $post['tanggal'],
			'pic'				=> $post['pic'],
			'pib'				=> $post['pib'],
			'no_invoice'		=> $post['no_invoice'],
			'keterangan'		=> $post['ket'],
			'sj_supplier'		=> $post['sj_supplier'],
			'created_date'		=> date('Y-m-d H:i:s')
		];
		$ArrDetail	= array();
		$ArrUpdate	= array();
		$ArrDetail2	= array();
		foreach ($Detail as $val => $valx) {
			$urut				= sprintf('%02s', $val);
			$ArrDetail[$val]['id_data']				= $id_data;
			$ArrDetail[$val]['id_incoming']			= $no_surat;
			$ArrDetail[$val]['id_detail_incoming']	= $id_data . '-' . $urut;

			foreach ($valx['detail'] as $val2 => $valx2) {
				$ArrDetail2[$val2 . $val]['id_data']				= $id_data;
				$ArrDetail2[$val2 . $val]['id_incoming']			= $no_surat;
				$ArrDetail2[$val2 . $val]['id_detail_incoming']	= $id_data . '-' . $urut;
				$ArrDetail2[$val2 . $val]['id_dt_po']	 			= $valx2['iddtpo'];
				$ArrDetail2[$val2 . $val]['id_material'] 			= $valx2['id_material'];
				$ArrDetail2[$val2 . $val]['nama_material'] 		= $valx2['nama_material'];
				$ArrDetail2[$val2 . $val]['length'] 				= $valx2['panjang2'];
				$ArrDetail2[$val2 . $val]['width'] 				= str_replace(',', '', $valx2['weight']);
				$ArrDetail2[$val2 . $val]['weight'] 				= str_replace(',', '', $valx2['weight']);
				$ArrDetail2[$val2 . $val]['qty_order'] 			= str_replace(',', '', $valx2['qtyorder']);
				$ArrDetail2[$val2 . $val]['qty_recive'] 			= str_replace(',', '', $valx2['qtyrecive']);
				$ArrDetail2[$val2 . $val]['tgl_datang'] 			= $post['tanggal'];
				$ArrDetail2[$val2 . $val]['lotno'] 				= $valx2['loto'];
				$ArrDetail2[$val2 . $val]['width_recive'] 		= str_replace(',', '', $valx2['widthrecive']);
				$ArrDetail2[$val2 . $val]['id_gudang'] 			= $valx2['gudang'];
				$ArrDetail2[$val2 . $val]['id_roll'] 				= $valx2['id_roll'];
				$ArrDetail2[$val2 . $val]['panjang'] 				= $valx2['panjang2'];
				$ArrDetail2[$val2 . $val]['actual_berat'] 		= $valx2['aktual'];
				$ArrDetail2[$val2 . $val]['selisih'] 			    = $valx2['selisih'];
				$ArrDetail2[$val2 . $val]['thickness'] 			= str_replace(',', '', $valx2['thickness']);
				$ArrDetail2[$val2 . $val]['qty_sheet'] 			= str_replace(',', '', $valx2['qty_sheet']);

				$id_dt_po = $valx2['iddtpo'];
				$cek = $this->db->query("SELECT totalwidth, qty_terima, berat_terima FROM dt_trans_po WHERE id_dt_po ='$id_dt_po'")->row();

				$beratterima = str_replace(',', '', $valx2['widthrecive']);
				$cekberat = $cek->berat_terima;
				$terima   = $cekberat + $beratterima;
				$order  = $cek->totalwidth;

				$ArrUpdate[$val2 . $val]['id_dt_po']				= $valx2['iddtpo'];
				if ($order > $terima) {
					$ArrUpdate[$val2 . $val]['sts_incoming'] = NULL;
				} else {
					$ArrUpdate[$val2 . $val]['sts_incoming'] = '1';
				}
				$ArrUpdate[$val2 . $val]['berat_terima'] = $terima;
			}
			foreach ($valx['detail'] as $val2 => $valx2) {
				$idmaterial     =   $valx2['id_material'];

				$thick          = $this->db->query("SELECT thickness, id_bentuk FROM stock_material WHERE id_category3 ='$idmaterial'")->row();
				$thickness      = $thick->thickness;
				$bentuk			= $thick->id_bentuk;

				$ArrStok[$val2 . $val]['id_category3']		= $valx2['id_material'];
				$ArrStok[$val2 . $val]['nama_material'] 		= $valx2['nama_material'];
				$ArrStok[$val2 . $val]['lotno'] 				= $valx2['loto'];
				$ArrStok[$val2 . $val]['qty'] 				= str_replace(',', '', $valx2['qtyrecive']);
				$ArrStok[$val2 . $val]['length'] 				= str_replace(',', '', $valx2['length']);
				$ArrStok[$val2 . $val]['weight'] 				= str_replace(',', '', $valx2['widthrecive']);
				$ArrStok[$val2 . $val]['totalweight'] 		= str_replace(',', '', $valx2['widthrecive']);
				$ArrStok[$val2 . $val]['aktif'] 				= 'Y';
				$ArrStok[$val2 . $val]['id_gudang'] 			= $valx2['gudang'];
				$ArrStok[$val2 . $val]['id_bentuk'] 			= $bentuk;
				$ArrStok[$val2 . $val]['thickness'] 			= str_replace(',', '', $valx2['thickness']);
				$ArrStok[$val2 . $val]['width'] 		        = str_replace(',', '', $valx2['weight']);
				$ArrStok[$val2 . $val]['no_po']               = $valx2['iddtpo'];
				$ArrStok[$val2 . $val]['id_incoming']         = $id_data;
				$ArrStok[$val2 . $val]['id_roll'] 			= $valx2['id_roll'];
				$ArrStok[$val2 . $val]['panjang'] 			= $valx2['panjang2'];
				$ArrStok[$val2 . $val]['actual_berat'] 		= $valx2['aktual'];
				$ArrStok[$val2 . $val]['selisih'] 			= $valx2['selisih'];
				$ArrStok[$val2 . $val]['sisa_spk'] 			= str_replace(',', '', $valx2['widthrecive']);
				$ArrStok[$val2 . $val]['customer'] 			= $valx2['customer'];
				$ArrStok[$val2 . $val]['no_surat'] 			= 'INCOMING';
			}
		}

		// print_r($ArrStok);
		// exit;
		$this->db->trans_start();
		$this->db->insert('tr_incoming', $ArrHeader);
		if (!empty($ArrDetail)) {
			$this->db->insert_batch('dt_incoming_po', $ArrDetail);
		}

		if (!empty($ArrUpdate)) {
			$this->db->update_batch('dt_trans_po', $ArrUpdate, 'id_dt_po');
		}


		if (!empty($ArrDetail) && !empty($ArrDetail2)) {
			$this->db->insert_batch('dt_incoming', $ArrDetail2);
		}
		if (!empty($ArrDetail) && !empty($ArrStok)) {
			$this->db->insert_batch('stock_material', $ArrStok);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}
	public function SaveNewOld()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Pr_model->generate_code();
		$id_data = $this->Pr_model->BuatID();
		$no_surat = $this->Pr_model->BuatNomor();
		$no_po = $post['no_po'];
		$look_po	= $this->db->query("SELECT * FROM tr_purchase_order WHERE no_po = '$no_po' ")->result();
		$nosu_po = $look_po[0]->no_surat;
		$this->db->trans_begin();
		$data = [
			'id_data'			=> $id_data,
			'id_incoming'		=> $no_surat,
			'no_po'				=> $post['no_po'],
			'no_surat_po'		=> $nosu_po,
			'id_gudang'			=> $post['id_gudang'],
			'tanggal'			=> $post['tanggal'],
			'pic'				=> $post['pic'],
			'pib'			=> $post['pib'],
			'no_invoice'				=> $post['no_invoice'],
			'keterangan'		=> $post['ket']
		];
		//Add Data
		$this->db->insert('tr_incoming', $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;
			if (empty($used[qtyrecive])) {
			} else {
				$dt =  array(
					'id_data'			=> $id_data,
					'id_incoming'			=> $no_surat,
					'id_dt_incoming'		=> $no_surat . '-' . $numb1,
					'id_dt_po'					=> $used[iddtpo],
					'id_material'			=> $used[idmaterial],
					'tgl_datang'			=> $used[tanggal],
					'nama_material'			=> $used[namamaterial],
					'length'				=> $used[length],
					'width'					=> $used[width],
					'weight'				=> $used[weight],
					'qty_order'			=> $used[qtyorder],
					'qty_recive'			=> $used[qtyrecive],
					'lotno'					=> $used[loto]
				);
				$this->db->insert('dt_incoming', $dt);
			};
		}
		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;
			$id_material = $used['idmaterial'];
			$querybentuk = $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '" . $id_material . "' ")->result();
			$bentuk		 = $querybentuk[0]->id_bentuk;
			$querythickness 	= $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '" . $id_material . "' ")->result();
			$thickness		 	= $querythickness[0]->nilai_dimensi;
			if (empty($used[qtyrecive])) {
			} else {
				$stok =  array(
					'id_category3'			=> $used['idmaterial'],
					'nama_material'			=> $used['namamaterial'],
					'width'					=> $used['width'],
					'lotno'					=> $used['loto'],
					'qty'					=> $used['qtyrecive'],
					'id_bentuk'				=> $bentuk,
					'length'				=> $used['length'],
					'thickness'				=> $thickness,
					'weight'				=> $used['weight'],
					'totalweight'			=> $used['qtyrecive'] * $used['weight'],
					'aktif'					=> 'Y',
					'id_gudang'				=> $post['id_gudang']
				);
				$this->db->insert('stock_material', $stok);
			};
		}
		$close = [
			'status'			=> '3'
		];
		$this->db->where('no_po', $no_po)->update("tr_purchase_order", $close);
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

	public function SaveUpdate()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $this->Pr_model->generate_code();
		$id_data = $post['id_data'];
		$no_surat = $post['id_incoming'];
		$no_po = $post['no_po'];
		$look_po	= $this->db->query("SELECT * FROM tr_purchase_order WHERE no_po = '$no_po' ")->result();
		$nosu_po = $look_po[0]->no_surat;
		$this->db->trans_begin();
		$data = [
			'keterangan'		=> $post['ket']
		];
		//Add Data
		$this->db->where('id_data', $id_data)->update("tr_incoming", $data);

		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;
			if (empty($used[qtyrecive])) {
			} else {
				$dt =  array(
					'id_data'				=> $id_data,
					'id_incoming'			=> $no_surat,
					'id_dt_incoming'		=> $no_surat . '-' . $numb1,
					'id_dt_po'				=> $used[iddtpo],
					'id_material'			=> $used[idmaterial],
					'tgl_datang'			=> $used[tanggal],
					'nama_material'			=> $used[namamaterial],
					'length'				=> $used[length],
					'width'					=> $used[width],
					'weight'				=> $used[weight],
					'qty_order'				=> $used[qtyorder],
					'qty_recive'			=> $used[qtyrecive],
					'lotno'					=> $used[loto]
				);
				$this->db->insert('dt_incoming', $dt);
			};
		}
		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;
			$id_material = $used['idmaterial'];
			$querybentuk = $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '" . $id_material . "' ")->result();
			$bentuk		 = $querybentuk[0]->id_bentuk;
			$querythickness 	= $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '" . $id_material . "' ")->result();
			$thickness		 	= $querythickness[0]->nilai_dimensi;
			if (empty($used[qtyrecive])) {
			} else {
				$stok =  array(
					'id_category3'			=> $used['idmaterial'],
					'nama_material'			=> $used['namamaterial'],
					'width'					=> $used['width'],
					'lotno'					=> $used['loto'],
					'qty'					=> $used['qtyrecive'],
					'id_bentuk'				=> $bentuk,
					'length'				=> $used['length'],
					'thickness'				=> $thickness,
					'weight'				=> $used['weight'],
					'totalweight'			=> $used['qtyrecive'] * $used['weight'],
					'aktif'					=> 'Y',
					'id_gudang'				=> $post['id_gudang'],
					'no_surat'				=> 'INCOMING'
				);
				$this->db->insert('stock_material', $stok);
			};
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

	public function SaveEdit()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		$code = $post['no_po'];
		$no_surat =  $post['no_surat'];
		$this->db->trans_begin();
		$data = [
			'no_po'				=> $code,
			'no_surat'			=> $no_surat,
			'id_suplier'		=> $post['id_suplier'],
			'loi'				=> $post['loi'],
			'nominal_kurs'		=> $post['nominal_kurs'],
			'tanggal'			=> $post['tanggal'],
			'expect_tanggal'	=> $post['expect_tanggal'],
			'term'				=> $post['term'],
			'cif'				=> $post['cif'],
			'hargatotal'		=> $post['hargatotal'],
			'diskontotal'		=> $post['diskontotal'],
			'taxtotal'			=> $post['taxtotal'],
			'subtotal'			=> $post['subtotal'],
			'status'			=> '1',
			'created_on'		=> date('Y-m-d H:i:s'),
			'created_by'		=> $this->auth->user_id()
		];
		//Add Data 
		$this->db->where('no_po', $code)->update("tr_purchase_order", $data);
		$this->db->delete('tr_purchase_order', array('no_po' => $code));
		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;
			$dt =  array(
				'no_po'					=> $code,
				'id_dt_po'				=> $code . '-' . $numb1,
				'idpr'					=> $used[idpr],
				'idmaterial'			=> $used[idmaterial],
				'namamaterial'			=> $used[namamaterial],
				'description'			=> $used[description],
				'qty'					=> $used[qty],
				'width'					=> $used[width],
				'totalwidth'			=> $used[totalwidth],
				'hargasatuan'			=> $used[hargasatuan],
				'lebar'					=> $used[lebar],
				'panjang'				=> $used[panjang],
				'diskon'				=> $used[diskon],
				'pajak'					=> $used[pajak],
				'jumlahharga'			=> $used[jumlahharga],
				'note'					=> $used[note],
			);
			$this->db->insert('dt_trans_po', $dt);
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
	public function PrintH()
	{
		ob_clean();
		ob_start();
		$this->auth->restrict($this->managePermission);
		$id = $this->uri->segment(3);
		// $data['header'] = $this->db->query("SELECT a.*, b.name_suplier as name_suplier, b.address_office as address_office, b.telephone as telephone,b.fax as fax FROM tr_purchase_order as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.no_po = '".$id."' ")->result();
		// $data['detail']  = $this->db->query("SELECT * FROM dt_trans_po WHERE no_po = '".$id."' ")->result();
		// $data['detailsum'] = $this->db->query("SELECT SUM(width) FROM dt_trans_po WHERE no_po = '".$id."' ")->result();
		$data['head'] = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM tr_incoming as a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' ")->result();
		$data['po']  = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$data['detail']  = $this->db->query("SELECT * FROM dt_incoming WHERE id_data='" . $id . "' ")->result();
		$data['gudang']  = $this->db->query("select * FROM ms_gudang ")->result();

		$this->load->view('print', $data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(0, 0, 0, 0));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Penawran.pdf', 'I');
	}

	function print_incoming_fix()
	{
		// $sroot 		= $_SERVER['DOCUMENT_ROOT'];
		// include $sroot."/application/libraries/MPDF57/mpdf.php"; 
		$data_session = $this->session->userdata;
		$session = $this->session->userdata('app_session');
		// print_r($session);
		// exit;
		$mpdf = new mPDF('utf-8', 'A4', 'P');
		$mpdf->SetImportUse();
		// $nomordoc = $this->uri->segment(3);
		// $gethd = $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice='$nomordoc'")->row();
		// $tgl = $gethd->tgl_invoice;
		// $Jml_Ttl = $gethd->total_invoice;
		// $Id_klien = $gethd->id_customer;
		// $Nama_klien = $gethd->nm_customer;
		// $Bln = substr($tgl, 5, 2);
		// $Thn = substr($tgl, 0, 4);
		// $data_header = $this->db->query("SELECT * FROM tr_invoice_header WHERE no_invoice ='$nomordoc'")->row();
		// $alamat_cust = $this->db->query("SELECT * FROM master_customer WHERE id_customer = '$gethd->id_customer'")->row();
		// $mso = $this->db->query("SELECT * FROM mso_proses_header WHERE id_quotation = '$gethd->no_ipp'")->row();
		// $quot = $this->db->query("SELECT * FROM quotation_process WHERE id = '$gethd->no_ipp'")->row();
		// $count = $this->db->query("SELECT COUNT(no_invoice) as total FROM tr_invoice_detail WHERE no_invoice ='$nomordoc'")->row();
		// $count1 = $count->total;
		// $total = $this->invoicing_model->GetInvoiceHeader($nomordoc);
		// $detail = $this->invoicing_model->GetInvoiceDetail($nomordoc);
		// $data['inv'] = $data_header;
		// $data['quot'] = $quot;
		// $data['total'] = $this->invoicing_model->GetInvoiceHeader($nomordoc);
		// $data['results'] = $this->invoicing_model->GetInvoiceDetail($nomordoc);
		$data['user'] = $session['username'];

		$id = $this->uri->segment(3);
		// $data['header'] = $this->db->query("SELECT a.*, b.name_suplier as name_suplier, b.address_office as address_office, b.telephone as telephone,b.fax as fax FROM tr_purchase_order as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.no_po = '".$id."' ")->result();
		// $data['detail']  = $this->db->query("SELECT * FROM dt_trans_po WHERE no_po = '".$id."' ")->result();
		// $data['detailsum'] = $this->db->query("SELECT SUM(width) FROM dt_trans_po WHERE no_po = '".$id."' ")->result();
		$head = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM tr_incoming as a LEFT JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' ")->row();
		$suplier	= $this->db->query("SELECT * FROM master_supplier WHERE id_suplier = '" . $head->id_suplier . "' ")->result();
		$data['head'] = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM tr_incoming as a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' ")->result();
		$data['po']  = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$data['detail']  = $this->db->query("SELECT * FROM dt_incoming WHERE id_data='" . $id . "' ")->result();
		$data['gudang']  = $this->db->query("select * FROM ms_gudang ")->result();
		$show = $this->load->view('incoming/print', $data, true);

		$header = '
          <br>

        	<table width="100%" border="0"  style="font-size:7.5pt !important;max-height:100px;border-spacing:-1px">
			<tr>
  	      		<!--<td width="8%" style="text-align: center;">-->
				<td style="text-align:center; font-weight: bold; font-size:12pt">
  	      			<img src="assets/images/logo.png" style="height: 40px;width: auto;">PT METALSINDO PASIFIC
  	      		</td>
  	      	</tr>
			</table>
			<br>
			<div id="footer">
			<table id="footer" border="0" width="100%" align="center">
			<tr>
				<td width="700" align="center">
					<h1 style="text-align: center;" >INCOMING</h1>
				</td>
			</tr>
			</table>
			</div>
		  <br>
		  <br>
          <table border="0" width="100%">
            <tr><b>
                  <td width="15%" style="font-size:8pt !important;vertical-align:top"><b>No.Dokumen</b></td>
				 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @$head->id_incoming . '</b></td>
				  <td width="15%" style="font-size:8pt !important;vertical-align:top"><b>Supplier </b></td>
				 <td width="3%" style="font-size:8pt !important;vertical-align:top"><b> :</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @$suplier[0]->name_suplier . '</b></td>
		 </b> </tr>
		
		  <tr><b>
		         <td width="10%" style="font-size:8pt !important;vertical-align:top"><b>Tanggal Transaksi</b></td>
				 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . date('d F Y', strtotime(@$head->tanggal)) . '</b></td>
				 <td width="10%" style="font-size:8pt !important;vertical-align:top"><b>Gudang</b></td>
				 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @$head->namagudang . '</b></td>


		 </b> </tr>
		    <tr><b>
                 <td width="10%" style="font-size:8pt !important;vertical-align:top"><b>PIC</b></td>
                 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . @$head->pic . '</b></td>
				 <td width="10%" style="font-size:8pt !important;vertical-align:top"><b>Keterangan</b></td>
                 <td width="1%" style="font-size:8pt !important;vertical-align:top"><b>:</b></td>
				 <td width="35%" style="font-size:8pt !important;vertical-align:top"><b>' . $head->keterangan . '</b></td>

		 </b> </tr>
		 </table>
		    <br>

		  <hr>
		  ';
		$this->mpdf->SetHTMLHeader($header, '0', true);
		$tglprint = date("d-m-Y H:i:s");
		$this->mpdf->SetHTMLFooter('
        <hr>
       	<div id="footer">
        <table>
            <tr><td>PT METALSINDO PASIFIC - Printed By ' . ucwords($session['username']) . ' On ' . $tglprint . ' </td></tr>
        </table>
        </div>
        ');
		$this->mpdf->AddPageByArray(['orientation' => 'P', 'margin-top' => 60, 'margin-bottom' => 15, 'margin-left' => 5, 'margin-right' => 10, 'margin-header' => 0, 'margin-footer' => 0,]);
		$this->mpdf->WriteHTML($show);
		$this->mpdf->Output();
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
			'created_on'			=> date('Y-m-d H:i:s'),
			'created_by'			=> $this->auth->user_id()
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
	public function saveEditPenawaran()
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
			$noinal = '1';
		}
		$id = $post['id_child_penawaran'];
		$dolar = $post['harga_penawaran'] / $nominal;
		$data = [
			'id_category3'			=> $post['id_category3'],
			'bentuk_material'		=> $post['bentuk_material'],
			'id_bentuk'				=> $post['id_bentuk'],
			'thickness'				=> $post['thickness'],
			'density'				=> $post['density'],
			'forecast'				=> $post['forecast'],
			'inven1'				=> $post['inven1'],
			'bottom'				=> $post['bottom'],
			'dasar_harga'			=> $post['dasar_harga'],
			'komisi'				=> $post['komisi'],
			'profit'				=> $post['profit'],
			'kurs_terpakai'			=> $post['kurs_terpakai'],
			'keterangan'			=> $post['keterangan'],
			'harga_penawaran'		=> $post['harga_penawaran'],
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
		$code = $this->Pr_model->generate_id();
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
		$comp = $this->Pr_model->compotition($inventory_2);
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
		$dim = $this->Pr_model->bentuk($id_bentuk);
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
		$comp = $this->Pr_model->compotition_edit($inventory_2);
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
		$dim = $this->Pr_model->bentuk_edit($id_bentuk);
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
		$code = $this->Pr_model->generate_id();
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
		$code = $this->Pr_model->generate_id();
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
		$comp = $this->Pr_model->compotition($inventory_2);
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

	public function checkSupplier()
	{
		$supplier = $this->input->post('supplier');

		$check = $this->db->select('suplier_location')->get_where('master_supplier', array('id_suplier' => $supplier))->result();
		$lokasi = (!empty($check[0]->suplier_location)) ? $check[0]->suplier_location : '';

		$data = [
			'lokasi' => $lokasi
		];

		echo json_encode($data);
	}


	public function Timbang()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $data = $this->db->query("SELECT a.* FROM tr_incoming as a  WHERE a.id_data='" . $id . "' ")->result();
		$po = $data = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$detail = $data = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM dt_incoming a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' ")->result();
		$gudang	= $this->db->query("select * FROM ms_gudang ")->result();
		$data = [
			'po' => $po,
			'head' => $head,
			'gudang' => $gudang,
			'detail' => $detail,
		];
		$this->template->set('results', $data);
		$this->template->title('INCOMING');
		$this->template->render('Timbang');
	}


	public function Logistik()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $data = $this->db->query("SELECT a.* FROM tr_incoming as a  WHERE a.id_data='" . $id . "' ")->result();
		$po = $data = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$detail = $data = $this->db->query("SELECT a.*, sum(a.width_recive) as total_berat, b.nama_gudang as namagudang FROM dt_incoming a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' GROUP BY a.id_material,a.width")->result();
		$gudang	= $this->db->query("select * FROM ms_gudang ")->result();
		$data = [
			'po' => $po,
			'head' => $head,
			'gudang' => $gudang,
			'detail' => $detail,
		];
		$this->template->set('results', $data);
		$this->template->title('INCOMING');
		$this->template->render('Logistik');
	}


	public function SaveTimbang()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();

		// print_r($post);
		// exit;

		$data			= $this->input->post();
		$session 		= $this->session->userdata('app_session');
		$Detail 	= $data['dt'];


		$ArrDetail	= array();
		$ArrUpdate	= array();
		$ArrDetail2	= array();


		foreach ($Detail as $val2 => $valx2) {

			$ArrUpdate[$val2 . $val]['id_roll'] 				= $valx2['id_roll'];
			$ArrUpdate[$val2 . $val]['actual_berat'] 		    = str_replace(',', '', $valx2['actual_berat']);
			$ArrUpdate[$val2 . $val]['selisih'] 			    = str_replace(',', '', $valx2['selisih']);

			$Arrdelete[$val2 . $val]['keterangan'] 			= $valx2['id_roll'];
			$Arrdelete[$val2 . $val]['aktif'] 		        = 'N';
		}

		foreach ($Detail as $val2 => $valx2) {
			$idmaterial     =   $valx2['idmaterial'];

			$thick          = $this->db->query("SELECT thickness, id_bentuk FROM stock_material WHERE id_category3 ='$idmaterial'")->row();
			$thickness      = $thick->thickness;
			$bentuk			= $thick->id_bentuk;

			$ArrStok[$val2 . $val]['id_category3']		= $valx2['idmaterial'];
			$ArrStok[$val2 . $val]['nama_material'] 		= $valx2['nama_material'];
			$ArrStok[$val2 . $val]['lotno'] 				= $valx2['lotno'];
			$ArrStok[$val2 . $val]['qty'] 				= '1';
			$ArrStok[$val2 . $val]['length'] 				= str_replace(',', '', $valx2['panjang']);
			$ArrStok[$val2 . $val]['weight'] 				= str_replace(',', '', $valx2['actual_berat']);
			$ArrStok[$val2 . $val]['totalweight'] 		= str_replace(',', '', $valx2['actual_berat']);
			$ArrStok[$val2 . $val]['aktif'] 				= 'Y';
			$ArrStok[$val2 . $val]['id_gudang'] 			= '6';
			$ArrStok[$val2 . $val]['id_bentuk'] 			= $bentuk;
			$ArrStok[$val2 . $val]['thickness'] 			= str_replace(',', '', $valx2['thickness']);
			$ArrStok[$val2 . $val]['width'] 		        = str_replace(',', '', $valx2['width']);
			$ArrStok[$val2 . $val]['no_po']               = $valx2['id_dt_po'];
			$ArrStok[$val2 . $val]['id_incoming']         = $valx2['id_incoming'];;
			$ArrStok[$val2 . $val]['keterangan'] 			= $valx2['id_roll'];
			$ArrStok[$val2 . $val]['actual_berat'] 		= 0;
			$ArrStok[$val2 . $val]['selisih'] 			= 0;
		}


		$this->db->trans_start();

		// if(!empty($ArrDetail)){
		// $this->db->insert_batch('dt_incoming_po', $ArrDetail);
		// }

		if (!empty($ArrUpdate)) {
			$this->db->update_batch('dt_incoming', $ArrUpdate, 'id_roll');
			$this->db->update_batch('stock_material', $ArrUpdate, 'id_roll');
			$this->db->update_batch('stock_material', $Arrdelete, 'keterangan');
		}


		// if(!empty($ArrDetail)){
		// $this->db->insert_batch('dt_incoming', $ArrDetail2);
		// }

		if (!empty($ArrStok)) {
			$this->db->insert_batch('stock_material', $ArrStok);
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

	public function SaveLogistik()
	{
		$this->auth->restrict($this->addPermission);
		$post = $this->input->post();


		$this->db->trans_begin();

		$id = $post['id_data'];

		$detail = $this->db->query("SELECT a.*, sum(a.width_recive) as total_berat, b.nama_gudang as namagudang FROM dt_incoming a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' GROUP BY a.id_material,a.width")->result();

		$no = 0;
		foreach ($detail as $det) {
			$no++;
		}

		//$biaya = $post['biaya']/$no;


		$biayalogistik = str_replace(",", "", $post['biaya']);


		$numb1 = 0;
		foreach ($_POST['dt'] as $used) {
			$numb1++;

			if ($post['loi'] == 'Lokal') {
				$kurs = 1;
			} else {
				$kurs = str_replace(",", "", $post['kurs']);
			}


			$qtyterima   = $used[width_recive];

			if ($post['pengiriman'] == 'lcl') {
				$biayalog 	= (0.2 * $qtyterima) * $kurs;
				$biaya 		= 0.2 * $qtyterima;
				$biayakurs   = $biayalog;
			} elseif ($post['pengiriman'] == 'fcl') {
				$biayalog =  (0.05 * $qtyterima) * $kurs;
				$biaya 		= 0.05 * $qtyterima;
				$biayakurs   = $biayalog;
			} else {
				$biayalog 	= 0;
				$biaya 		= 0;
				$biayakurs   = 0;
			}




			$id_dt_po 	= $used[id_dt_po];
			$width       = $used[width];
			$idmat       = $used[idmaterial];

			$po 			= $this->db->query("SELECT * FROM dt_trans_po WHERE id_dt_po='$id_dt_po'")->row();
			$hargasatuan = $po->hargasatuan;


			$total_harga_barang_kurs = $hargasatuan * $qtyterima;
			$total_harga_barang = $hargasatuan * $qtyterima * $kurs;

			$stock_mat 			= $this->db->query("SELECT SUM(sisa_spk) as weight FROM stock_material WHERE id_category3='$idmat' AND width='$width' AND aktif='Y' AND id_gudang='1'")->row();


			$no_po = substr($id_dt_po, 0, 8);


			if (!empty($stock_mat)) {

				$stock = $stock_mat->weight;
			} else {

				$stock = 0;
			}


			$costbook_lama 		= $this->db->query("SELECT id, nilai_costbook as nilai FROM ms_costbook WHERE id_category3='$idmat' AND width='$width'")->row();
			$idcostbook			= $costbook_lama->id;

			// print_r($idcostbook);
			// exit;

			if (!empty($costbook_lama)) {

				$hargacostbook = $costbook_lama->nilai;
			} else {

				$hargacostbook = 0;
			}

			// print_r($hargacostbook);
			// exit;


			$totalhargastock = $stock * $hargacostbook;

			$totalharga_all = $totalhargastock + $total_harga_barang;
			$totalbarang    = $stock + $qtyterima;
			$costbook1      = ($totalharga_all + $biaya) / $totalbarang;
			$costbook       = $costbook1;
			$dt =  array(
				'id_data'				=> $id_data,
				'id_dt_po'				=> $used[id_dt_po],
				'id_material'			=> $used[idmaterial],
				'nama_material'			=> $used[nama_material],
				'thickness'				=> $used[thickness],
				'density'				=> $used[density],
				'width'					=> $used[width],
				'qty_order'				=> $used[qty_order],
				'qty_recive'			=> $used[width_recive],
				'lotno'					=> $used[lotno],
				'kurs'					=> $kurs,
				'harga_beli_satuan'	    => $hargasatuan,
				'total_harga_beli'	    => $total_harga_barang,
				'stock_material'		=> $stock,
				'harga_costbook_lama'   => $hargacostbook,
				'total_stock'		    => $stock,
				'total_harga_stock'     => $totalhargastock,
				'total_harga'		    => $totalharga_all,
				'costbook'		        => $costbook,
				'biaya_logistik'		=> $biaya,
				'no_po'		            => $no_po,

			);
			$this->db->insert('dt_incoming_costbook', $dt);


			if (!empty($costbook_lama)) {

				$datacost = [

					'costbook_lama'		    => $costbooklama,
					'nilai_costbook'		=> $costbook,
					'modified_by'			=> $this->auth->user_id(),
					'modified_on'			=> date('Y-m-d H:i:s'),

				];

				// $this->db->where('id',$idcostbook)->update("ms_costbook",$datacost);

			} else {

				$dtcost =  array(
					'id_category3'			=> $used[idmaterial],
					'costbook_lama'		    => $costbooklama,
					'nilai_costbook'		=> $costbook,
					'width'					=> $width,
					'created_by'			=> $this->auth->user_id(),
					'created_on'			=> date('Y-m-d H:i:s'),
				);

				// $this->db->insert('ms_costbook',$dtcost);

			}

			$incoming_kurs = $hargasatuan * $qtyterima;
			$incoming_idr  = $hargasatuan * $qtyterima * $kurs;
			$logistik_kurs = $biaya;
			$logistik_idr  = $biayakurs;

			$incoming_kurs_inc += $hargasatuan * $qtyterima;
			$incoming_idr_inc  += $hargasatuan * $qtyterima * $kurs;


			// $this->db->query("UPDATE stock_material SET costbook=$costbook WHERE id_category3='$idmat' AND width='$width' AND aktif='Y' AND id_gudang='1'");

			// $this->db->query("UPDATE stock_material SET aktif='Y' WHERE id_incoming='$id_data' AND id_gudang='1'");

			$this->db->query("UPDATE tr_purchase_order SET incoming_kurs=incoming_kurs+$incoming_kurs, incoming_idr=incoming_idr+$incoming_idr,logistik_kurs=logistik_kurs+$biayakurs,logistik_idr=logistik_idr+$biaya WHERE no_po='$no_po'");
		};


		$purchase = $this->db->query("SELECT a.* FROM dt_incoming_po a WHERE a.id_data='" . $id . "'")->result();

		$nomor = 0;
		foreach ($purchase as $pc) {
			$nomor++;

			$nomorpo = $pc->no_po;

			$hdpo 			= $this->db->query("SELECT sisa_um_kurs,sisa_um_idr,kurs_uangmuka FROM tr_purchase_order WHERE no_po='$nomorpo'")->row();

			$uang_muka_kurs    = $hdpo->sisa_um_kurs;
			$sisa_um_idr		  = $hdpo->sisa_um_idr;
			$kurs_uangmuka	  = $hdpo->kurs_uangmuka;


			// print_r($no_po);
			// exit;

			$selisih_kurs1	  = $hdpo->sisa_um_kurs * $hdpo->kurs_uangmuka;
			$selisih_kurs2	  = $hdpo->sisa_um_kurs * $kurs;
			$selisih_kurs	  = $selisih_kurs1 - $selisih_kurs2;


			$um_kurs_incoming  		+= $hdpo->sisa_um_kurs;
			$sisa_um_idr_incoming 	+= $hdpo->sisa_um_idr;
			$selisih_incoming   		+= $selisih_kurs;
			$uang_muka_potong_idr   	+= $hdpo->sisa_um_kurs * $kurs;


			$this->db->query("UPDATE tr_purchase_order SET kurs_datang=$kurs,um_potong_idr=$selisih_kurs2, um_potong_kurs=$uang_muka_kurs, sisa_um_kurs=sisa_um_kurs-$uang_muka_kurs, sisa_um_idr=sisa_um_idr-$sisa_um_idr WHERE no_po='$nomorpo'");


			$logistik_kurs_inc += $biaya;
			$logistik_idr_inc  += $biayakurs;
		}


		// print_r($no_po);
		// exit;




		$unbilled_kurs = ($incoming_kurs_inc + $logistik_kurs_inc) - $um_kurs_incoming;
		$unbilled_idr  = ($incoming_idr_inc + $logistik_idr_inc) - $uang_muka_potong_idr;

		$inventory_kurs = $incoming_kurs_inc + $logistik_kurs_inc;
		$inventory_idr  = $incoming_idr_inc + $logistik_idr_inc;

		$data = [
			'kurs'		    		=> $kurs,
			'logistik'				=> $biayalogistik,
			'update_logistik'		=> $this->auth->user_id(),
			'update_on'				=> date('Y-m-d H:i:s'),
			'status_logistik'		=> '1',
			'incoming_kurs'			=> $incoming_kurs_inc,
			'incoming_idr'			=> $incoming_idr_inc,
			'logistik_kurs'			=> $logistik_kurs_inc,
			'logistik_idr'			=> $logistik_idr_inc,
			'uang_muka_kurs'		=> $um_kurs_incoming,
			'uang_muka_idr'		    => $sisa_um_idr_incoming,
			'uang_muka_potong_idr'  => $uang_muka_potong_idr,
			'selisih_kurs'		    => $selisih_incoming,
			'unbilled_kurs'			=> $unbilled_kurs,
			'unbilled_idr'			=> $unbilled_idr,
			'inventory_kurs'	    => $inventory_kurs,
			'inventory_idr'			=> $inventory_idr,

		];
		$this->db->where('id_data', $id)->update("tr_incoming", $data);



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

	public function jurnal_ap()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->Pr_model->CariRequestPayment();
		$this->template->set('results', $data);
		$this->template->title('Jurnal Pengakuan AP');
		$this->template->render('index_jurnal_ap');
	}

	public function index_customer()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-users');
		$data = $this->db->query("SELECT a.*, b.name_customer as name_customer FROM tr_incoming_customer as a INNER JOIN master_customers as b on a.id_suplier=b.id_customer ORDER BY a.id DESC")->result();
		$this->template->set('results', $data);
		$this->template->title('Incoming Customer');
		$this->template->render('index_customer');
	}
	public function incoming_customer()
	{
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$deleted = 'JASA SLITTING';
		$id_customer = $this->Pr_model->get_data('master_customers', 'deleted', $deleted);
		$id_material = $this->db->query("SELECT a.* FROM ms_inventory_category3 as a WHERE (a.maker='JASA SLITTING' OR a.maker='REWIND') ")->result();
		$id_gudang = $this->Pr_model->get_data('ms_gudang');
		$data = [
			'customer' => $id_customer,
			'material' => $id_material,
			'gudang' => $id_gudang
		];
		$this->template->set('results', $data);
		$this->template->title('Add Incoming');
		$this->template->render('incoming_customer');
	}
	public function saveIncomingCustomer()
	{
		$Arr_Kembali	= array();
		$post = $this->input->post();

		$code = $this->Pr_model->generate_code_cust();
		$id_data = $this->Pr_model->BuatID_cust();
		$no_surat = $this->Pr_model->BuatNomor_cust();
		$data			= $this->input->post();
		$session 		= $this->session->userdata('app_session');

		$ArrHeader	 = [
			'id_data'			=> $id_data,
			'id_incoming'		=> $no_surat,
			'id_suplier'		=> $post['id_customer'],
			'id_gudang'			=> $post['id_gudang'],
			'tanggal'			=> $post['tanggal'],
			'pic'				=> $post['pic'],
			'pib'				=> $post['pib'],
			'no_invoice'		=> $post['no_invoice'],
			'keterangan'		=> $post['ket'],
			'created_date'		=> date('Y-m-d H:i:s')
		];


		$this->db->trans_start();
		$this->db->insert('tr_incoming_customer', $ArrHeader);


		if (empty($_POST['data1'])) {
		} else {
			$numb2 = 0;
			$urut  = 0;
			foreach ($_POST['data1'] as $d1) {
				$numb2++;
				$urut++;

				$idmaterial     = $d1['id_material'];
				$mat            = $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 ='$idmaterial'")->row();
				$thickness      = $thick->thickness;
				$bentuk		  = $mat->id_bentuk;

				$data1 =  array(
					'id_category3'		=> $d1['id_material'],
					'nama_material' 	=> $mat->nama,
					'lotno' 			=> $d1['lotno'],
					'length'			=> 0,
					'qty' 				=> 1,
					'weight' 			=> str_replace(',', '', $d1['berat_coil']),
					'totalweight' 		=> str_replace(',', '', $d1['berat_coil']),
					'aktif' 			=> 'Y',
					'id_gudang' 		=> $d1['gudang'],
					'id_bentuk' 		=> $bentuk,
					'thickness' 		=> $mat->thickness,
					'width' 		    => str_replace(',', '', $d1['width']),
					'no_po'             => '',
					'id_incoming'       => $id_data,
					'sisa_spk' 			=> str_replace(',', '', $d1['berat_coil']),
					'customer' 			=> $post['id_customer'],
					'no_surat' 			=> 'INCOMING CUSTOMER'
				);


				$data2 = array(
					'id_data'			=> $id_data,
					'id_incoming'		=> $no_surat,
					'id_detail_incoming' => $id_data . '-' . $urut,
					'id_dt_po' 			=> '',
					'id_material'		=> $d1['id_material'],
					'nama_material' 	=> $mat->nama,
					'length'			=> 0,
					'width'				=> str_replace(',', '', $d1['width']),
					'weight' 			=> str_replace(',', '', $d1['berat_coil']),
					'qty_order' 		=> 1,
					'qty_recive'		=> 1,
					'tgl_datang'		=> $post['tanggal'],
					'lotno'				=> $d1['lotno'],
					'width_recive'		=> str_replace(',', '', $d1['berat_coil']),
					'id_gudang' 		=> $d1['gudang'],
					'thickness'			=> $mat->thickness

				);


				//Add Data

				$this->db->insert('dt_incoming_customer', $data2);
				$this->db->insert('stock_material', $data1);
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Save gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Save berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}

	public function LihatCustomer()
	{
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
		$session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$head = $data = $this->db->query("SELECT a.* FROM tr_incoming_customer as a  WHERE a.id_data='" . $id . "' ")->result();
		$po = $data = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$detail = $data = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM dt_incoming_customer a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='" . $id . "' ")->result();
		$gudang	= $this->db->query("select * FROM ms_gudang ")->result();
		$data = [
			'po' => $po,
			'head' => $head,
			'gudang' => $gudang,
			'detail' => $detail,
		];
		$this->template->set('results', $data);
		$this->template->title('INCOMING');
		$this->template->render('ViewCustomer');
	}

	public function check_ros()
	{
		$post = $this->input->post();

		$get_po = $this->db->get_where('tr_purchase_order', ['no_po' => $post['no_po']])->row();

		$no_surat = $get_po->no_surat;

		$get_ros = $this->db->select('a.*')->from('tr_ros a')->like('a.no_po', $no_surat, 'both')->get()->row();

		$get_ros_detail = $this->db
			->select('SUM(a.qty_packing_list) as total_incoming')
			->from('tr_ros_detail a')
			->where('a.no_ros', $get_ros->id)
			->get()
			->row();

		$total_incoming = $get_ros_detail->total_incoming;

		echo json_encode([
			'total_incoming' => $total_incoming,
			'no_pib' => $get_ros->no_pengajuan_pib,
			'keterangan' => $get_ros->keterangan
		]);
	}

	public function get_incoming()
	{
		$this->Pr_model->get_incoming();
	}
}
