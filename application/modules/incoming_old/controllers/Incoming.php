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

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('Mpdf', 'upload', 'Image_lib'));
        $this->load->model(array('Incoming/Pr_model',
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
        $data = $this->db->query("SELECT * FROM tr_incoming")->result();
        $this->template->set('results', $data);
        $this->template->title('Incoming');
        $this->template->render('index');
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
		$data = [
			'po' => $po,
			'gudang' => $gudang,
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
		$head = $data = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM tr_incoming as a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='".$id."' ")->result();
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
		$customers = $this->Pr_model->get_data('master_customers','deleted',$deleted);
		$karyawan = $this->Pr_model->get_data('ms_karyawan','deleted',$deleted);
		$mata_uang = $this->Pr_model->get_data('mata_uang','deleted'.$deleted);
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
		$head = $data = $this->db->query("SELECT a.*, b.nama_gudang as namagudang FROM tr_incoming as a INNER JOIN ms_gudang as b ON a.id_gudang = b.id_gudang WHERE a.id_data='".$id."' ")->result();
		$po = $data = $this->db->query("SELECT * FROM tr_purchase_order WHERE status='2' ")->result();
		$detail = $data = $this->db->query("SELECT * FROM dt_incoming WHERE id_data='".$id."' ")->result();
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
		
	$loi	=$_GET['loi'];
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0,0,0,date('n'),date('j')-10,date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$tglnow = date('d');
		$blnnow = date('m');
		if ($blnnow != '1'){ 
		$blnkmrn = $blnnow-1;
		$yearkemaren = date('Y');
		}else{
		$blnkmrn = "12";
		$yearnow = date('Y');
		$yearkemaren = $yearnow-1;
		}	
	$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
	$kurs10hari	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
	$kurs30hari	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
	$nomkurs = $kurs[0]->kurs;
	$nomkurs10 = $kurs10hari[0]->nominal;
	$nomkurs30 = $kurs30hari[0]->nominal;
	$k =  number_format($nomkurs,2);
	$k10 =  number_format($nomkurs10,2);
	$k30 =  number_format($nomkurs30,2);
	if($loi == 'Import'){
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
	}else{};
	}
	public function PrintHeader1($id){
        $this->auth->restrict($this->managePermission);
        $id = $this->uri->segment(3);
		$data['header'] = $this->Pr_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Pr_model->PrintDetail($id);
		$this->load->view('PrintHeader',$data);
	}
	public function PrintHeader($id){
		ob_clean();
		ob_start();
        $this->auth->restrict($this->managePermission);
        $id = $this->uri->segment(3);
		$data['header'] = $this->Pr_model->getHeaderPenawaran($id);
		$data['detail']  = $this->Pr_model->PrintDetail($id);
		$this->load->view('PrintHeader',$data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P','A4','en',true,'UTF-8',array(0, 0, 0, 0));
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
		$head = $this->Pr_model->get_data('tr_penawaran','no_penawaran',$id);
		$customers = $this->Pr_model->get_data('master_customers','deleted',$deleted);
		$karyawan = $this->Pr_model->get_data('ms_karyawan','deleted',$deleted);
		$mata_uang = $this->Pr_model->get_data('mata_uang','deleted',$deleted);
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
		$penawaran = $this->Pr_model->get_data('child_penawaran','id_child_penawaran',$id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
        $this->template->set('results', $data);
        $this->template->title('Edit Penawaran');
        $this->template->render('editPenawaran');

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
		$penawaran = $this->Pr_model->get_data('child_penawaran','id_child_penawaran',$id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'penawaran' => $penawaran,
			'inventory_3' => $inventory_3,
		];
        $this->template->set('results', $data);
        $this->template->title('Edit Penawaran');
        $this->template->render('viewPenawaran');

    }
	
	public function viewBentuk($id){
		$this->auth->restrict($this->viewPermission);
		$id 	= $this->input->post('id');
		$bentuk = $this->db->get_where('ms_bentuk',array('id_bentuk' => $id))->result();
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
		$headpenawaran = $this->Pr_model->get_data('tr_penawaran','no_penawaran',$id);
		$inventory_3 = $this->Pr_model->get_data_category();
		$data = [
			'inventory_3' => $inventory_3,
			'headpenawaran' => $headpenawaran
		];
        $this->template->set('results', $data);
        $this->template->title('Add Penawaran');
        $this->template->render('AddPenawaran');

    } 

function GetMaterial()
    {
		$loop=$_GET['jumlah']+1;
		$tanggal = date('Y-m-d');
		$no_po=$_GET['no_po'];
		$material = $this->db->query("SELECT * FROM dt_trans_po WHERE no_po = '$no_po'  ")->result();
		foreach($material as $material){
		$loop++;
		$list = $this->db->query("SELECT SUM(qty_recive) as jumlahdatang FROM dt_incoming WHERE id_dt_po = '".$material->id_dt_po."'  ")->result();
		$qty = $material->qty;
		$qty_recive = $list[0]->jumlahdatang;
		$hasilqty = $qty-$qty_recive;
		if($hasilqty >= '1'){
		echo "
		<tr id='trmaterial_$loop'>
		<th				hidden	><input  type='text' 		value='".$material->id_dt_po."'		class='form-control' id='dt_iddtpo_".$loop."' 			required name='dt[".$loop."][iddtpo]' 	readonly></th>
		<th				hidden	><input  type='text' 		value='".$material->idmaterial."'	class='form-control' id='dt_idmaterial_".$loop."' 		required name='dt[".$loop."][idmaterial]' 	readonly></th>
		<th						><input  type='date' 		value='".$tanggal."'				class='form-control' id='dt_tanggal_".$loop."' 	required name='dt[".$loop."][tanggal]' ></th>
		<th						><input  type='text' 		value='".$material->namamaterial."'	class='form-control' id='dt_namamaterial_".$loop."' 	required name='dt[".$loop."][namamaterial]' readonly></th>
		<th						><input  type='text' 		value='".$material->panjang."'		class='form-control' id='dt_length_".$loop."' 			required name='dt[".$loop."][length]' 		readonly></th>
		<th						><input  type='number' 		value='".$material->lebar."'		class='form-control' id='dt_width_".$loop."' 			required name='dt[".$loop."][width]'  		readonly></th>
		<th						><input  type='number' 		value='".$material->width."'		class='form-control' id='dt_weight_".$loop."' 			required name='dt[".$loop."][weight]' 		readonly></th>
		<th						><input  type='number' 		value='".$hasilqty."'				class='form-control' id='dt_qtyorder_".$loop."' 		required name='dt[".$loop."][qtyorder]'     readonly></th>
		<th						><input  type='number' 											class='form-control' id='dt_qtyrecive_".$loop."' 		required name='dt[".$loop."][qtyrecive]' 	></th>
		<th						><input  type='text' 											class='form-control' id='dt_lotno_".$loop."' 			required name='dt[".$loop."][loto]' 		></th>
		</tr>
		";}else{
		};
		}
	}
function LockMatrial()
    {
		$loop=$_GET['id'];
		$idpr=$_GET['idpr'];
		$idmaterial = $_GET['idmaterial'];
		$namamaterial = $_GET['namaterial'];
		$description = $_GET['description'];
		$qty = $_GET['qty']; 
		$width = $_GET['width']; 
		$totalwidth = $_GET['totalwidth']; 
		$hargasatuan = $_GET['hargasatuan'];
		$panjang = $_GET['panjang']; 
		$lebar = $_GET['lebar'];
		$diskon = $_GET['diskon']; 
		$pajak = $_GET['pajak'];
		$jumlahharga = $_GET['jumlahharga'];
		$note = $_GET['note'];
		echo "
		<td hidden><input readonly 	type='text' 	value='".$idpr."'		class='form-control' id='dt_idpr_".$loop."' 	required name='dt[".$loop."][idpr]' ></td>
		<td hidden><input readonly 	type='text' 	value='".$idmaterial."'		class='form-control' id='dt_idmaterial_".$loop."' 	required name='dt[".$loop."][idmaterial]' ></td>
		<td ><input		readonly  	type='text' 	value='".$namamaterial."'	class='form-control' id='dt_namamaterial_".$loop."' required name='dt[".$loop."][namamaterial]' ></td>
		<td ><input		readonly  	type='text' 	value='".$description."'	class='form-control' id='dt_description_".$loop."' 	required name='dt[".$loop."][description]' ></td>
		<td ><input		readonly  	type='number' 	value='".$width."'			class='form-control' id='dt_width_".$loop."' 			required name='dt[".$loop."][width]'  ></td>
		<td ><input		readonly  	type='number' 	value='".$qty."'			class='form-control' id='dt_qty_".$loop."' 			required name='dt[".$loop."][qty]'  ></td>
		<td ><input		readonly  	type='number' 	value='".$totalwidth."'			class='form-control' id='dt_totalwidth_".$loop."' 			required name='dt[".$loop."][totalwidth]'  ></td>
		<td ><input		readonly  	type='number' 	value='".$panjang."'			class='form-control' id='dt_panjang_".$loop."' 			required name='dt[".$loop."][panjang]'  ></td>
		<td ><input		readonly  	type='number' 	value='".$lebar."'			class='form-control' id='dt_lebar_".$loop."' 			required name='dt[".$loop."][lebar]'  ></td>
		<td	><input		readonly  	type='number' 	value='".$hargasatuan."'	class='form-control' id='dt_hargasatuan_".$loop."' 	required name='dt[".$loop."][hargasatuan]' ></td>
		<td	><input		readonly 	type='number' 	value='".$diskon."'			class='form-control' id='dt_diskon_".$loop."' 		required name='dt[".$loop."][diskon]' ></td>
		<td	><input		readonly	type='number' 	value='".$pajak."'			class='form-control' id='dt_pajak_".$loop."' 		required name='dt[".$loop."][pajak]' ></td>
		<td ><input		readonly 	type='number' 	value='".$jumlahharga."'	class='form-control' id='dt_jumlahharga_".$loop."' 	required name='dt[".$loop."][jumlahharga]' ></td>
		<td	><input		readonly  	type='text' 	value='".$note."'			class='form-control' id='dt_note_".$loop."' 		required name='dt[".$loop."][note]' ></td>
		<td><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return CancelItem($loop);'><i class='fa fa-close'></i></button></td>
		";
	}
function HitungHarga()
    {
        $dt_hargasatuan=$_GET['dt_hargasatuan'];
		$dt_qty=$_GET['dt_qty'];
		$loop=$_GET['id'];
		$isi =  $dt_hargasatuan*$dt_qty;
		echo "<input readonly type='text' value='".$isi."' 	class='form-control' id='dt_jumlahharga_".$loop."' 	required name='dt[".$loop."][jumlahharga]' >";
	}
function TotalWeight()
    {
        $dt_width=$_GET['dt_width'];
		$dt_qty=$_GET['dt_qty'];
		$loop=$_GET['id'];
		$isi =  $dt_width*$dt_qty;
		echo "<input readonly type='text' value='".$isi."' 	class='form-control' id='dt_totalwidth_".$loop."' 	required name='dt[".$loop."][totalwidth]' >";
	}
function CariIdMaterial()
    {
        $idpr=$_GET['idpr'];
		$loop=$_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->idmaterial; 
		echo "<input readonly type='text' value='".$isi."' 	class='form-control' id='dt_idmaterial_".$loop."' 	required name='dt[".$loop."][idmaterial]' >";
	}
function CariNamaMaterial()
    {
        $idpr=$_GET['idpr'];
		$loop=$_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->nama_material; 
		echo "<input readonly type='text' value='".$isi."' 	class='form-control' id='dt_namamaterial_".$loop."' 	required name='dt[".$loop."][namamaterial]' >";
	}
function CariDescripitionMaterial() 
    {
        $idpr=$_GET['idpr'];
		$loop=$_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->keterangan; 
		echo "<input  type='text' value='".$isi."' 	class='form-control' id='dt_description_".$loop."' 	required name='dt[".$loop."][description]' >";
	}
function CariPanjangMaterial() 
    {
        $idpr=$_GET['idpr'];
		$loop=$_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->length; 
		echo "<input  type='text' value='".$isi."' 	class='form-control' id='dt_panjang_".$loop."' 	required name='dt[".$loop."][panjang]' >";
	}
function CariLebarMaterial() 
    {
        $idpr=$_GET['idpr'];
		$loop=$_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->width; 
		echo "<input  type='text' value='".$isi."' 	class='form-control' id='dt_lebar_".$loop."' 	required name='dt[".$loop."][lebar]' >";
	}
function FormInputKurs() 
    {
		$loi=$_GET['loi'];
		if($loi == "Import"){
		echo "
		<div class='form-group row'>
			<div class='col-md-4'>
				<label>Kurs</label>
			</div>
			<div class='col-md-8'>
				<input type='number' class='form-control' id='nominal_kurs'  required name='nominal_kurs'  placeholder='Nominal Kurs'>
			</div>
		</div>
		";}else{
		echo "
		<div class='form-group row' hidden>
			<div class='col-md-4'>
				<label>Kurs</label>
			</div>
			<div class='col-md-8'>
				<input type='number' class='form-control' id='nominal_kurs'  required name='nominal_kurs' readonly placeholder='Nominal Kurs'>
			</div>
		</div>
		";}
	}
function CariQtyMaterial()
    {
        $idpr=$_GET['idpr'];
		$loop=$_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->qty; 
		echo "<input  type='text' value='".$isi."' 	class='form-control' id='dt_qty_".$loop."' onkeyup='HitungHarga(".$loop.")' 	required name='dt[".$loop."][qty]' >";
	}
function CariweightMaterial()
    {
        $idpr=$_GET['idpr'];
		$loop=$_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->weight; 
		echo "<input  type='text' value='".$isi."' 	class='form-control' id='dt_width_".$loop."' onkeyup='HitungHarga(".$loop.")' 	required name='dt[".$loop."][width]' >";
	}
function CariTweightMaterial()
    {
        $idpr=$_GET['idpr'];
		$loop=$_GET['id'];
		$material = $this->db->query("SELECT * FROM dt_trans_pr WHERE id_dt_pr = '$idpr'  ")->result();
		$isi = $material[0]->totalweight; 
		echo "<input  type='text' value='".$isi."' 	class='form-control' id='dt_totalwidth_".$loop."' onkeyup='HitungHarga(".$loop.")' 	required name='dt[".$loop."][totalwidth]' >";
	}
function CariIdBentuk()
    {
        $id_category3=$_GET['idmaterial'];
		$loop=$_GET['id'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$id_bentuk = $kategory3[0]->id_bentuk;
		echo "<input readonly type='text' class='form-control' value='".$id_bentuk."' id='dt_idbentuk_".$loop."' required name='dt[".$loop."][idbentuk]' >";
	}
function CariSupplier()
    {
        $id_category3=$_GET['idmaterial'];
		$loop=$_GET['id'];
		$supplier	= $this->db->query("SELECT a.*, b.name_suplier as supname FROM child_inven_suplier as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.id_category3 = '$id_category3' ")->result();
		echo "<select class='form-control' id='dt_suplier_".$loop."' name='dt[".$loop."][suplier]'>
		<option value=''>--Pilih--</option>";
		foreach($supplier as $supplier){
			echo"<option value='".$supplier->id_suplier ."'>".$supplier->supname ."</option>";
		}
		echo"</select>";
	}
function CariTHarga()
    {
        $hargatotal=$_GET['hargatotal'];
		$jumlahharga=$_GET['jumlahharga'];
		$isi=$hargatotal+$jumlahharga;
		echo "<input readonly type='text' value='".$isi."' class='form-control' id='hargatotal'  onkeyup required name='hargatotal' >";
	}
function CariTDiskon()
    {
        $diskontotal=$_GET['diskontotal'];
		$diskon=$_GET['diskon']/100;
		$jumlahharga=$_GET['jumlahharga'];
		$val1=$jumlahharga*$diskon;
		$isi=$val1+$diskontotal;
		echo "<input readonly type='text' value='".$isi."' class='form-control' id='diskontotal'  onkeyup required name='diskontotal' >";
	}
function CariTPajak()
    {
        $taxtotal=$_GET['taxtotal'];
		$pajak=$_GET['pajak']/100;
		$jumlahharga=$_GET['jumlahharga'];
		$val1=$jumlahharga*$pajak;
		$isi=$val1+$taxtotal;
		echo "<input readonly type='text' value='".$isi."' class='form-control' id='taxtotal'  onkeyup required name='taxtotal' >";
	}
function CariTSum()
    {
        $taxtotal=$_GET['taxtotal'];
		$pajak=$_GET['pajak']/100;
		$jumlahharga=$_GET['jumlahharga'];
		$val1=$jumlahharga*$pajak;
		$isi1=$val1+$taxtotal;
		$diskontotal=$_GET['diskontotal'];
		$diskon=$_GET['diskon']/100;
		$val2=$jumlahharga*$diskon;
		$isi2=$val2+$diskontotal;
		$hargatotal=$_GET['hargatotal'];
		$isi3=$hargatotal+$jumlahharga;
		$isi=$isi1+$isi2+$isi3;
		echo "<input readonly type='text' value='".$isi."' class='form-control' id='subtotal'  onkeyup required name='subtotal' >";
	}
function CariMinHarga()
    {
        $hargatotal=$_GET['hargatotal'];
		$jumlahharga=$_GET['jumlahharga'];
		$isi=$hargatotal-$jumlahharga;
		echo "<input readonly type='text' value='".$isi."' class='form-control' id='hargatotal'  onkeyup required name='hargatotal' >";
	}
function CariMinDiskon()
    {
        $diskontotal=$_GET['diskontotal'];
		$diskon=$_GET['diskon']/100;
		$jumlahharga=$_GET['jumlahharga'];
		$val1=$jumlahharga*$diskon;
		$isi=$val1-$diskontotal;
		echo "<input readonly type='text' value='".$isi."' class='form-control' id='diskontotal'  onkeyup required name='diskontotal' >";
	}
function CariMinPajak()
    {
        $taxtotal=$_GET['taxtotal'];
		$pajak=$_GET['pajak']/100;
		$jumlahharga=$_GET['jumlahharga'];
		$val1=$jumlahharga*$pajak;
		$isi=$val1-$taxtotal;
		echo "<input readonly type='text' value='".$isi."' class='form-control' id='taxtotal'  onkeyup required name='taxtotal' >";
	}
function CariMinSum()
    {
        $taxtotal=$_GET['taxtotal'];
		$pajak=$_GET['pajak']/100;
		$jumlahharga=$_GET['jumlahharga'];
		$val1=$jumlahharga*$pajak;
		$isi1=$val1-$taxtotal;
		$diskontotal=$_GET['diskontotal'];
		$diskon=$_GET['diskon']/100;
		$val2=$jumlahharga*$diskon;
		$isi2=$val2-$diskontotal;
		$hargatotal=$_GET['hargatotal'];
		$isi3=$hargatotal-$jumlahharga;
		$isi=$isi1+$isi2+$isi3;
		echo "<input readonly type='text' value='".$isi."' class='form-control' id='subtotal'  onkeyup required name='subtotal' >";
	}
function cari_inven1()
    {
        $id_category3=$_GET['id_category3'];
		$kategory3	= $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '$id_category3' ")->result();
		$inven1 = $kategory3[0]->id_category1;
		echo "<input type='text' class='form-control' id='inven1' value='$inven1'  required name='inven1' placeholder='Bentuk Material'>";
	}
	public function delDetail(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		// print_r($id);
		// exit();
		$data = [
			'deleted' 		=> '1',
			'deleted_by' 	=> $this->auth->user_id()
		];

		$this->db->trans_begin();
		$this->db->where('id_dimensi',$id)->update("ms_dimensi",$data);

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);
		}

  		echo json_encode($status);
	}

	public function Approved(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$data = [
			'status' 		=> '2',
		];

		$this->db->trans_begin();
		$this->db->where('no_po',$id)->update("tr_purchase_order",$data);

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Approve P.R. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Approve P.R. Thanks ...',
			  'status'	=> 1
			);
		}

  		echo json_encode($status);
	}
		function get_inven2()
    {
        $inventory_1=$_GET['inventory_1'];
        $data=$this->Pr_model->level_2($inventory_1);
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
        $inventory_2=$_GET['inventory_2'];
        $data=$this->Pr_model->level_3($inventory_2);

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
               $this->db->insert('tr_incoming',$data);
			 
		$numb1 =0;
		foreach($_POST['dt'] as $used){
		$numb1++;
			if(empty($used[qtyrecive])){
			}else{
				$dt =  array(
							'id_data'			=> $id_data,
							'id_incoming'			=> $no_surat,
							'id_dt_incoming'		=> $no_surat.'-'.$numb1,
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
                    $this->db->insert('dt_incoming',$dt);
			};
		    }
		$numb1 =0;
		foreach($_POST['dt'] as $used){
		$numb1++;
		$id_material = $used['idmaterial'];
		$querybentuk = $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '".$id_material."' ")->result();
		$bentuk		 = $querybentuk[0]->id_bentuk;
		$querythickness 	= $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '".$id_material."' ")->result();
		$thickness		 	= $querythickness[0]->nilai_dimensi;
		if(empty($used[qtyrecive])){
			}else{               
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
							'totalweight'			=> $used['qtyrecive']*$used['weight'],
							'aktif'					=> 'Y',
							'id_gudang'				=> $post['id_gudang']
                            );
                    $this->db->insert('stock_material',$stok);
			};
		    }
					$close = [
							'status'			=> '3'
                            ]; 
		        $this->db->where('no_po',$no_po)->update("tr_purchase_order",$close);
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'code' => $code,
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
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
			$this->db->where('id_data',$id_data)->update("tr_incoming",$data);
			 
		$numb1 =0;
		foreach($_POST['dt'] as $used){
		$numb1++;
			if(empty($used[qtyrecive])){
			}else{
				$dt =  array(
							'id_data'				=> $id_data,
							'id_incoming'			=> $no_surat,
							'id_dt_incoming'		=> $no_surat.'-'.$numb1,
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
                    $this->db->insert('dt_incoming',$dt);
			};
		    }
		$numb1 =0;
		foreach($_POST['dt'] as $used){
		$numb1++;
		$id_material = $used['idmaterial'];
		$querybentuk = $this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '".$id_material."' ")->result();
		$bentuk		 = $querybentuk[0]->id_bentuk;
		$querythickness 	= $this->db->query("SELECT * FROM child_inven_dimensi WHERE id_category3 = '".$id_material."' ")->result();
		$thickness		 	= $querythickness[0]->nilai_dimensi;
		if(empty($used[qtyrecive])){
			}else{               
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
							'totalweight'			=> $used['qtyrecive']*$used['weight'],
							'aktif'					=> 'Y',
							'id_gudang'				=> $post['id_gudang']
                            );
                    $this->db->insert('stock_material',$stok);
			};
		    }
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'code' => $code,
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
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
			$this->db->where('no_po',$code)->update("tr_purchase_order",$data);
			$this->db->delete('tr_purchase_order', array('no_po' => $code));
		$numb1 =0;
		foreach($_POST['dt'] as $used){
		$numb1++;
                $dt =  array(
							'no_po'					=> $code,
							'id_dt_po'				=> $code.'-'.$numb1,
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
                    $this->db->insert('dt_trans_po',$dt);
			
		    }
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'code' => $code,
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
			  'code' => $code,
			  'status'	=> 1
			);
		}

  		echo json_encode($status);

    }
	public function PrintH(){
		ob_clean();
		ob_start();
        $this->auth->restrict($this->managePermission);
        $id = $this->uri->segment(3);
		$data['header'] = $this->db->query("SELECT a.*, b.name_suplier as name_suplier, b.address_office as address_office, b.telephone as telephone,b.fax as fax FROM tr_purchase_order as a INNER JOIN master_supplier as b on a.id_suplier = b.id_suplier WHERE a.no_po = '".$id."' ")->result();
		$data['detail']  = $this->db->query("SELECT * FROM dt_trans_po WHERE no_po = '".$id."' ")->result();
		$data['detailsum'] = $this->db->query("SELECT SUM(width) FROM dt_trans_po WHERE no_po = '".$id."' ")->result();
		$this->load->view('print',$data);
		$html = ob_get_contents();

		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P','A4','en',true,'UTF-8',array(0, 0, 0, 0));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Penawran.pdf', 'I');
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
			$this->db->where('no_penawaran',$code)->update("tr_penawaran",$data);

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'code' => $code,
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
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
		$sepuluh_hari = mktime(0,0,0,date('n'),date('j')-10,date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$sebulan = mktime(0,0,0,date('n'),date('j')-30,date('Y'));
		$tirtydays = date("Y-m-d", $sebulan);
		$tglnow = date('d');
		$blnnow = date('m');
		if ($blnnow != '1'){ 
		$blnkmrn = $blnnow-1;
		$yearkemaren = date('Y');
		}else{
		$blnkmrn = "12";
		$yearnow = date('Y');
		$yearkemaren = $yearnow-1;
		}
		$kurs_terpakai = $post['kurs_terpakai'];
		if($kurs_terpakai=='spot'){
		$kurs	= $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR' ")->result();
		$nominal = $kurs[0]->kurs;
		}elseif($kurs_terpakai=='10'){
		$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN  '$tendays' AND '$hariini' AND kode_kurs='IDR' ")->result();
		$nominal = $kurs[0]->nominal;
		}elseif($kurs_terpakai=='30'){
		$kurs	= $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) =  '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR' ")->result();
		$nominal = $kurs[0]->nominal;
		}else{
		$noinal = '1';
		}
		$id = $post['id_child_penawaran'];
		$dolar = $post['harga_penawaran']/$nominal;
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
               $this->db->where('id_child_penawaran',$id)->update("child_penawaran",$data);

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'code' => $id_bentuk,
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
			  'code' => $id_bentuk,
			  'status'	=> 1
			);
		}

  		echo json_encode($status);

    }
public function deletePenawaran(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		$this->db->trans_begin();
		$this->db->delete('child_penawaran', array('id_child_penawaran' => $id));

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. Thanks ...',
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
		$numb1 =0;
		foreach($_POST['hd1'] as $h1){
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
                     $this->db->where('id_category3',$id)->update("ms_inventory_category3",$header1);

		    }

		if(empty($_POST['data1'])){
		}else{
		$this->db->delete('child_inven_suplier', array('id_category3' => $id));
		$numb2 =0;

		foreach($_POST['data1'] as $d1){
		$numb2++;
              $data1 =  array(
			                    'id_category3'=>$id,
								'id_suplier'=>$d1[id_supplier],
								'lead'=>$d1[lead],
								'minimum'=>$d1[minimum],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_suplier',$data1);

		    }
		}

		if(empty($_POST['compo'])){
		}else{
		$this->db->delete('child_inven_compotition', array('id_category3' => $id));
		$numb3 =0;
		foreach($_POST['compo'] as $c1){
		$numb3++;
              $comp =  array(
			                    'id_category3'=>$id,
								'id_compotition'=>$c1[id_compotition],
								'nilai_compotition'=>$c1[jumlah_kandungan],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_compotition',$comp);

		    }
		}

		if(empty($_POST['dimens'])){
		}else{
		$this->db->delete('child_inven_dimensi', array('id_category3' => $id));
		$numb4 =0;
		foreach($_POST['dimens'] as $dm){
		$numb4++;
              $dms =  array(
			                    'id_category3'=>$id,
								'id_dimensi'=>$dm[id_dimensi],
								'nilai_dimensi'=>$dm[nilai_dimensi],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_dimensi',$dms);

		    }
		}
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'code' => $id_bentuk,
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
			  'code' => $id_bentuk,
			  'status'	=> 1
			);
		}

  		echo json_encode($status);

    }
		function get_compotition_new()
    {
        $inventory_2=$_GET['inventory_2'];
        $comp=$this->Pr_model->compotition($inventory_2);
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
        $id_bentuk=$_GET['id_bentuk'];
        $dim=$this->Pr_model->bentuk($id_bentuk);
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
        $inventory_2=$_GET['inventory_2'];
        $comp=$this->Pr_model->compotition_edit($inventory_2);
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
        $id_bentuk=$_GET['id_bentuk'];
        $dim=$this->Pr_model->bentuk_edit($id_bentuk);
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
		$id =$_POST['hd1']['1']['id_category3'];
		$numb1 =0;
		//$head = $_POST['hd1'];
		foreach($_POST['hd1'] as $h1){
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
              $this->db->where('id_category3',$id)->update("ms_inventory_category3",$data);

		    }
		$this->db->delete('child_inven_suplier', array('id_category3' => $id));
		if(empty($_POST['data1'])){
		}else{
		$numb2 =0;
		foreach($_POST['data1'] as $d1){
		$numb2++;
              $data1 =  array(
			                    'id_category3'=>$code,
								'id_suplier'=>$d1[id_supplier],
								'lead'=>$d1[lead],
								'minimum'=>$d1[minimum],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_suplier',$data1);

		    }
		}
		if(empty($_POST['compo'])){
		}else{
		$numb3 =0;
		foreach($_POST['compo'] as $c1){
		$numb3++;
              $comp =  array(
			                    'id_category3'=>$code,
								'id_compotition'=>$c1[id_compotition],
								'nilai_compotition'=>$c1[jumlah_kandungan],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_compotition',$comp);

		    }
		}
		if(empty($_POST['dimens'])){
		}else{
		$numb4 =0;
		foreach($_POST['dimens'] as $dm){
		$numb4++;
              $dms =  array(
			                    'id_category3'=>$code,
								'id_dimensi'=>$dm[id_dimensi],
								'nilai_dimensi'=>$dm[nilai_dimensi],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_dimensi',$dms);

		    }
		}
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
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
		$id =$_POST['hd1']['1']['id_category3'];
		$numb1 =0;
		//$head = $_POST['hd1'];
		foreach($_POST['hd1'] as $h1){
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
              $this->db->where('id_category3',$id)->update("ms_inventory_category3",$data);

		    }
		if(empty($_POST['data1'])){
		}else{
		$numb2 =0;
		foreach($_POST['data1'] as $d1){
		$numb2++;
              $data1 =  array(
			                    'id_category3'=>$id,
								'id_suplier'=>$d1[id_supplier],
								'lead'=>$d1[lead],
								'minimum'=>$d1[minimum],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_suplier',$data1);

		    }
		}
		if(empty($_POST['compo'])){
		}else{
		$numb3 =0;
		foreach($_POST['compo'] as $c1){
		$numb3++;
              $comp =  array(
			                    'id_category3'=>$id,
								'id_compotition'=>$c1[id_compotition],
								'nilai_compotition'=>$c1[jumlah_kandungan],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_compotition',$comp);

		    }
		}
		if(empty($_POST['dimens'])){
		}else{
		$numb4 =0;
		foreach($_POST['dimens'] as $dm){
		$numb4++;
              $dms =  array(
			                    'id_category3'=>$id,
								'id_dimensi'=>$dm[id_dimensi],
								'nilai_dimensi'=>$dm[nilai_dimensi],
								'deleted' =>'0',
							    'created_on' => date('Y-m-d H:i:s'),
								'created_by' => $this->auth->user_id(),
                            );
            //Add Data
              $this->db->insert('child_inven_dimensi',$dms);

		    }
		}
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$status	= array(
			  'pesan'		=>'Gagal Save Item. Thanks ...',
			  'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$status	= array(
			  'pesan'		=>'Success Save Item. invenThanks ...',
			  'status'	=> 1
			);
		}

  		echo json_encode($status);

    }

		function get_compotition()
    {
        $inventory_2=$_GET['inventory_2'];
        $comp=$this->Pr_model->compotition($inventory_2);
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

}
