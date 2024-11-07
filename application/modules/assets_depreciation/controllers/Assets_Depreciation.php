<?php if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class Assets_depreciation extends Admin_Controller
{

	protected $viewPermission = 'Assets_Depreciation.View';
	protected $addPermission = 'Assets_Depreciation.Add';
	protected $managePermission = 'Assets_Depreciation.Manage';
	protected $deletePermission = 'Assets_Depreciation.Delete';

	public function __construct()
	{
		parent::__construct();

		$this->load->library(array('Mpdf', 'upload'));
		$this->load->model(array(
			'Assets_depreciation/Assets_depreciation_model'
		));

		date_default_timezone_set('Asia/Bangkok');
		$this->template->page_icon('fa fa-table');
	}

	public function index()
	{
		// $this->auth->restrict($this->viewPermission);
		$this->template->title('List Assets');
		$cabang		= $this->db->query("SELECT * FROM cabang WHERE sts_aktif = 'aktif'")->result_array();
		$kategori	= $this->db->query("SELECT * FROM asset_category")->result_array();

		$this->db->select('DATE_FORMAT(a.tgl_perolehan, "%Y") as tahun_perolehan');
		$this->db->from('asset a');
		$this->db->group_by('DATE_FORMAT(a.tgl_perolehan, "%Y")');
		$this->db->order_by('a.tgl_perolehan', 'desc');
		$get_year_asset = $this->db->get()->result();

		$this->db->select('a.id, a.nm_category');
		$this->db->from('asset_category a');
		$get_list_category = $this->db->get()->result();

		$dataArr = array(
			'cabang' => $cabang,
			'kategori' => $kategori,
			'list_asset_year' => $get_year_asset,
			'list_category' => $get_list_category
		);
		$this->template->render('index', $dataArr);
	}

	public function get_data_asset()
	{
		$draw = $this->input->post('draw');
		$start = $this->input->post('start');
		$length = $this->input->post('length');
		$search = $this->input->post('search');
		$category_sp = $this->input->post('category_sp');
		$tahun_sp = $this->input->post('tahun_sp');
		$bulan_sp = $this->input->post('bulan_sp');



		$this->db->select('a.*, b.nama as nm_coa');
		$this->db->from('asset a');
		$this->db->join(DBACC . '.coa_master b', 'b.no_perkiraan = a.id_coa');
		$this->db->where('a.deleted', 'N');
		if (!empty($category_sp)) {
			$this->db->where('a.nm_category', $category_sp);
		}
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.nm_asset', $search['value'], 'both');
			$this->db->or_like('a.kd_asset', $search['value'], 'both');
			$this->db->or_like('DATE_FORMAT(a.tgl_perolehan, "%d-%M-%Y")', $search['value'], 'both');
			$this->db->or_like('a.id_coa', $search['value'], 'both');
			$this->db->or_like('b.nama', $search['value'], 'both');
			$this->db->or_like('a.depresiasi', $search['value'], 'both');
			$this->db->or_like('a.value', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.id');
		$this->db->order_by('a.id', 'desc');
		$this->db->limit($length, $start);

		$get_data = $this->db->get();

		// print_r($this->db->last_query());
		// exit;

		$this->db->select('a.*, b.nama as nm_coa');
		$this->db->from('asset a');
		$this->db->join(DBACC . '.coa_master b', 'b.no_perkiraan = a.id_coa');
		$this->db->where('a.deleted', 'N');
		if (!empty($category_sp)) {
			$this->db->where('a.nm_category', $category_sp);
		}
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.nm_asset', $search['value'], 'both');
			$this->db->or_like('a.kd_asset', $search['value'], 'both');
			$this->db->or_like('DATE_FORMAT(a.tgl_perolehan, "%d-%M-%Y")', $search['value'], 'both');
			$this->db->or_like('a.id_coa', $search['value'], 'both');
			$this->db->or_like('b.nama', $search['value'], 'both');
			$this->db->or_like('a.depresiasi', $search['value'], 'both');
			$this->db->or_like('a.value', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.id');
		$this->db->order_by('a.id', 'desc');

		$get_data_all = $this->db->get();

		$hasil = [];

		$no = $start + 1;
		foreach ($get_data->result() as $item) {

			$tahun_tgl_perolehan = date('y', strtotime($item->tgl_perolehan));
			$tahun = (!empty($tahun_sp)) ? date('y', strtotime($tahun_sp)) : date('y');

			$exp_tahun = (($tahun - $tahun_tgl_perolehan) * 12);

			$bulan_tgl_perolehan = date('m', strtotime($item->tgl_perolehan));
			$bulan = (!empty($bulan_sp)) ? date('m', strtotime('2024-' . sprintf('%02s', $bulan_sp . '-01'))) : date('m');

			$exp_bulan = ($bulan - $bulan_tgl_perolehan);

			$akumulasi_depresiasi = ($item->value * ($exp_tahun + $exp_bulan));
			if ($akumulasi_depresiasi < 0) {
				$akumulasi_depresiasi = 0;
			}
			if($akumulasi_depresiasi > $item->nilai_asset) { 
				$akumulasi_depresiasi = $item->nilai_asset;
			}

			$asset_val = ($item->nilai_asset - $akumulasi_depresiasi);
			if ($asset_val < 0) {
				$asset_val = 0;
			}

			$button = '<button type="button" class="btn btn-info" id="detail" data-id="' . $item->id . '"><i class="fa fa-eye"></i></button>';

			$hasil[] = [
				'no' => $no,
				'kode_asset' => $item->kd_asset,
				'asset_name' => $item->nm_asset,
				'tgl_perolehan' => date('d-M-Y', strtotime($item->tgl_perolehan)),
				'category' => $item->nm_category,
				'kelompok_penyusutan' => $item->id_coa . ' | ' . $item->nm_coa,
				'depreciation' => number_format($item->depresiasi) . ' Tahun',
				'aquisition' => number_format($item->nilai_asset, 2),
				'depreciation_val' => number_format($item->value, 2),
				'akumulasi_depresiasi' => number_format($akumulasi_depresiasi, 2),
				'asset_val' => number_format($asset_val, 2),
				'option' => $button
			];

			$no++;
		}

		echo json_encode([
			'draw' => intval($draw),
			'recordsTotal' => $get_data_all->num_rows(),
			'recordsFiltered' => $get_data_all->num_rows(),
			'data' => $hasil
		]);
	}

	public function data_side()
	{
		$this->Assets_depreciation_model->getDataJSON();
	}

	public function modal_edit()
	{
		$this->load->view('modal_edit');
	}

	public function modal_jurnal()
	{
		$this->load->view('modal_jurnal');
	}

	public function modal_view()
	{
		$this->load->view('modal_view');
	}

	public function modal()
	{
		$id = $this->uri->segment(3);
		$query = "SELECT a.*, b.nm_dept FROM asset a LEFT JOIN department_center b ON a.cost_center=b.id WHERE a.id='" . $id . "' LIMIT 1 ";
		$result = $this->db->query($query)->result();
		$dataArr = array(
			'list_dept' => $this->Assets_depreciation_model->getList('department'),
			'list_catg' => $this->Assets_depreciation_model->getList('asset_category'),
			'list_cab' 	=> $this->Assets_depreciation_model->getList('asset_branch'),
			'list_pajak' => $this->Assets_depreciation_model->getList('asset_category_pajak'),
			'list_coa_asset' => $this->Assets_depreciation_model->getList(DBACC . '.coa_master'),
			'data' 		=> $result
		);

		$this->template->render('modal', $dataArr);
	}

	public function InsertJurnal()
	{
		$ArrJurnal_D = $this->Assets_depreciation_model->getList('asset_jurnal');
		$ArrJurnal_K = $this->Assets_depreciation_model->getList('asset_jurnal');

		$ArrDebit = array();
		$ArrKredit = array();
		$ArrJavh = array();
		$Loop = 0;
		foreach ($ArrJurnal_D as $val => $valx) {
			$Loop++;

			if ($valx['category'] == 1) {
				$coaD 	= "6831-02-01";
				$ketD	= "BIAYA PENYUSUTAN KENDARAAN";
				$coaK 	= "1309-05-01";
				$ketK	= "AKUMULASI PENYUSUTAN KENDARAAN";
			}
			if ($valx['category'] == 2) {
				$coaD 	= "6831-06-01";
				$ketD	= "BIAYA PENYUSUTAN HARTA LAINNYA";
				$coaK 	= "1309-08-01";
				$ketK	= "AKUMULASI PENYUSUTAN HARTA LAINNYA";
			}
			if ($valx['category'] == 3) {
				$coaD 	= "6831-01-01";
				$ketD	= "BIAYA PENYUSUTAN BANGUNAN";
				$coaK 	= "1309-07-01";
				$ketK	= "AKUMULASI PENYUSUTAN BANGUNAN";
			}

			$ArrDebit[$Loop]['tipe'] 			= "JV";
			$ArrDebit[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrDebit[$Loop]['tanggal'] 		= date('Y-m-d');
			$ArrDebit[$Loop]['no_perkiraan'] 	= $coaD;
			$ArrDebit[$Loop]['keterangan'] 		= $ketD;
			$ArrDebit[$Loop]['no_reff'] 		= "";
			$ArrDebit[$Loop]['debet'] 			= $valx['sisa_nilai'];
			$ArrDebit[$Loop]['kredit'] 			= 0;

			$ArrKredit[$Loop]['tipe'] 			= "JV";
			$ArrKredit[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrKredit[$Loop]['tanggal'] 		= date('Y-m-d');
			$ArrKredit[$Loop]['no_perkiraan'] 	= $coaK;
			$ArrKredit[$Loop]['keterangan'] 	= $ketK;
			$ArrKredit[$Loop]['no_reff'] 		= "";
			$ArrKredit[$Loop]['debet'] 			= 0;
			$ArrKredit[$Loop]['kredit'] 		= $valx['sisa_nilai'];

			$ArrJavh[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrJavh[$Loop]['tgl'] 			= date('Y-m-d');
			$ArrJavh[$Loop]['jml'] 				= $valx['sisa_nilai'];
			$ArrJavh[$Loop]['kdcab'] 			= $valx['kdcab'];
			$ArrJavh[$Loop]['jenis'] 			= "V";
			$ArrJavh[$Loop]['keterangan'] 		= "PENYUSUTAN ASSET";
			$ArrJavh[$Loop]['bulan'] 			= ltrim(date('m'), 0);
			$ArrJavh[$Loop]['tahun'] 			= date('Y');
			$ArrJavh[$Loop]['user_id'] 			= "System";
			$ArrJavh[$Loop]['tgl_jvkoreksi'] 	= date('Y-m-d');

			$this->Jurnal_model->update_Nomor_Jurnal($valx['kdcab'], 'JM');
		}

		// echo "<pre>";
		// print_r($ArrDebit);
		// print_r($ArrKredit);
		// print_r($ArrJavh);
		// exit;

		$this->db->trans_start();
		$this->db->insert_batch('jurnal', $ArrDebit);
		$this->db->insert_batch('jurnal', $ArrKredit);
		$this->db->insert_batch('javh', $ArrJavh);	
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$this->db->query("INSERT INTO asset_jurnal_log (tanggal, ket) VALUES ('" . date('Y-m-d H:i:s') . "', 'FAILED')");
		} else {
			$this->db->trans_commit();
			$this->db->query("INSERT INTO asset_jurnal_log (tanggal, ket) VALUES ('" . date('Y-m-d H:i:s') . "', 'SUCCESS')");
		}
	}

	public function saved_jurnal()
	{
		$session 		= $this->session->userdata('app_session');
		$ArrDel = $this->db->query("SELECT nomor FROM jurnal WHERE jenis_trans = 'asset jurnal' AND SUBSTRING_INDEX(tanggal, '-', 2) = '" . date('Y-m') . "' GROUP BY nomor ")->result_array();

		$dtListArray = array();
		foreach ($ArrDel as $val => $valx) {
			$dtListArray[$val] = $valx['nomor'];
		}

		$dtImplode	= "('" . implode("','", $dtListArray) . "')";

		$date_now	= date('Y-m-d');
		$bln		= ltrim(date('m'), 0);
		$thn		= date('Y');
		$bulanx		= date('m');

		if (!empty($this->input->post('tgl_jurnal'))) {
			$date_now	= $this->input->post('tgl_jurnal') . "-01";
			$DtExpl		= explode('-', $date_now);
			$bln		= ltrim($DtExpl[1], 0);
			$thn		= $DtExpl[0];
			$bulanx		= $DtExpl[1];
		}
		// print_r($dtImplode);
		// exit;

		$ArrJurnal_D = $this->Assets_depreciation_model->getList('asset_jurnal');
		$ArrDebit = array();
		$ArrKredit = array();
		$ArrJavh = array();
		$Loop = 0;
		foreach ($ArrJurnal_D as $val => $valx) {
			$Loop++;

			if ($valx['category'] == 1) {
				$coaD 	= "6831-02-01";
				$ketD	= "BIAYA PENYUSUTAN KENDARAAN";
				$coaK 	= "1309-05-01";
				$ketK	= "AKUMULASI PENYUSUTAN KENDARAAN";
			}
			if ($valx['category'] == 2) {
				$coaD 	= "6831-06-01";
				$ketD	= "BIAYA PENYUSUTAN HARTA LAINNYA";
				$coaK 	= "1309-08-01";
				$ketK	= "AKUMULASI PENYUSUTAN HARTA LAINNYA";
			}
			if ($valx['category'] == 3) {
				$coaD 	= "6831-01-01";
				$ketD	= "BIAYA PENYUSUTAN BANGUNAN";
				$coaK 	= "1309-07-01";
				$ketK	= "AKUMULASI PENYUSUTAN BANGUNAN";
			}

			$ArrDebit[$Loop]['tipe'] 			= "JV";
			$ArrDebit[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrDebit[$Loop]['tanggal'] 		= $date_now;
			$ArrDebit[$Loop]['no_perkiraan'] 	= $coaD;
			$ArrDebit[$Loop]['keterangan'] 		= $ketD;
			$ArrDebit[$Loop]['no_reff'] 		= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrDebit[$Loop]['debet'] 			= $valx['sisa_nilai'];
			$ArrDebit[$Loop]['kredit'] 			= 0;
			$ArrDebit[$Loop]['jenis_trans'] 	= 'asset jurnal';

			$ArrKredit[$Loop]['tipe'] 			= "JV";
			$ArrKredit[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrKredit[$Loop]['tanggal'] 		= $date_now;
			$ArrKredit[$Loop]['no_perkiraan'] 	= $coaK;
			$ArrKredit[$Loop]['keterangan'] 	= $ketK;
			$ArrKredit[$Loop]['no_reff'] 		= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrKredit[$Loop]['debet'] 			= 0;
			$ArrKredit[$Loop]['kredit'] 		= $valx['sisa_nilai'];
			$ArrKredit[$Loop]['jenis_trans'] 	= 'asset jurnal';

			$ArrJavh[$Loop]['nomor'] 			= $this->Jurnal_model->get_Nomor_Jurnal_Memorial($valx['kdcab'], date('Y-m-d'));
			$ArrJavh[$Loop]['tgl'] 				= $date_now;
			$ArrJavh[$Loop]['jml'] 				= $valx['sisa_nilai'];
			$ArrJavh[$Loop]['kdcab'] 			= $valx['kdcab'];
			$ArrJavh[$Loop]['jenis'] 			= "V";
			$ArrJavh[$Loop]['keterangan'] 		= "PENYUSUTAN ASSET";
			$ArrJavh[$Loop]['bulan'] 			= $bln;
			$ArrJavh[$Loop]['tahun'] 			= $thn;
			$ArrJavh[$Loop]['user_id'] 			= "System";
			$ArrJavh[$Loop]['tgl_jvkoreksi'] 	= $date_now;

			$this->Jurnal_model->update_Nomor_Jurnal($valx['kdcab'], 'JM');
		}

		// echo "<pre>";
		// print_r($ArrDebit);
		// print_r($ArrKredit);
		// print_r($ArrJavh);
		exit;

		$this->db->trans_start();
		$this->db->query("DELETE FROM jurnal WHERE nomor IN " . $dtImplode . " ");
		$this->db->query("DELETE FROM javh WHERE nomor IN " . $dtImplode . " ");
		$this->db->insert_batch('jurnal', $ArrDebit);
		$this->db->insert_batch('jurnal', $ArrKredit);
		$this->db->insert_batch('javh', $ArrJavh);
		$this->db->query("UPDATE asset_generate SET flag='Y' WHERE bulan='" . $bulanx . "' AND tahun='" . $thn . "' ");
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$this->db->query("INSERT INTO asset_jurnal_log (tanggal, ket, jurnal_by, bulan, tahun, kdcab) VALUES ('" . date('Y-m-d H:i:s') . "', 'FAILED', '" . $this->session->userdata['app_session']['username'] . "', '" . $bulanx . "', '" . $thn . "', '" . $session['kdcab'] . "')");
			$Arr_Data	= array(
				'pesan'		=> 'Asset gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$this->db->query("INSERT INTO asset_jurnal_log (tanggal, ket, jurnal_by, bulan, tahun, kdcab) VALUES ('" . date('Y-m-d H:i:s') . "', 'SUCCESS', '" . $this->session->userdata['app_session']['username'] . "', '" . $bulanx . "', '" . $thn . "', '" . $session['kdcab'] . "')");
			$Arr_Data	= array(
				'pesan'		=> 'Asset berhasil disimpan. Terimakasih ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}

	public function saved()
	{

		$Arr_Kembali	= array();
		$data			= $this->input->post();

		$session 		= $this->session->userdata('app_session');
		$nmCategory		= $this->Assets_depreciation_model->getWhere('asset_category', 'id', $data['category']);

		$category		= $data['category'];
		$penyusutan		= $data['penyusutan'];
		$kelompok_penyusutan		= $data['kelompok_penyusutan'];
		$category_pajak	= $data['category_pajak'];
		$KdCategory		= sprintf('%02s', $category);
		$KdCategoryPjk	= sprintf('%02s', $category_pajak);
		$Ym				= date('ym');
		$tgl_oleh		= date('Y-m-d');

		$foto		= $data['foto'];
		$branch		= $data['branch'];

		// $nama 			= $_FILES['file']['name'];
		// $ukuran			= $_FILES['file']['size'];
		// $file_tmp  			= $_FILES['file']['tmp_name'];

		// echo $nama; exit;

		if (!empty($data['tanggal'])) {
			$Year			= date('ym', strtotime($data['tanggal']));
			$Ym				= $Year;
			$tgl_oleh		= date('Y-m-d', strtotime($data['tanggal']));
		}

		$qQuery			= "SELECT max(kd_asset) as maxP FROM asset WHERE category='" . $category . "' AND kd_asset LIKE '" . $branch . "-" . $Ym . $KdCategory . $KdCategoryPjk . "-%' ";
		$restQuery		= $this->db->query($qQuery)->result_array();

		// AST-1011908-02-0001

		$angkaUrut2		= $restQuery[0]['maxP'];
		$urutan2		= (int)substr($angkaUrut2, 17, 3);
		$urutan2++;
		$urut2			= sprintf('%03s', $urutan2);

		$kode_assets	= $branch . "-" . $Ym . $KdCategory . $KdCategoryPjk . "-" . $urut2;

		$config = array(
			'upload_path' 		=> './assets/foto/',
			'allowed_types' 		=> 'gif|jpg|png|jpeg|JPG|PNG',
			'file_name' 			=> $kode_assets,
			'file_ext_tolower' 	=> TRUE,
			'overwrite' 			=> TRUE,
			'max_size' 			=> 2000048,
			'remove_spaces' 		=> TRUE
		);

		$this->load->library('upload', $config);
		$this->upload->initialize($config);
		if (!$this->upload->do_upload('foto')) {
			$result = $this->upload->display_errors();
		} else {

			$ext 		= end((explode(".", $_FILES['foto']['name'])));
			$pic 		= $kode_assets . "." . strtolower($ext);
			$paths 		= $_SERVER['DOCUMENT_ROOT'] . '/assets/foto/' . $pic;
			if (file_exists($paths)) {
				unlink($paths);
			}
			$data_foto  = array('upload_data' => $this->upload->data('foto'));
		}

		$detailDataDash	= array();
		// echo $kode_assets; exit;

		$lopp 	= 0;
		$lopp2 	= 0;
		for ($no = 1; $no <= $data['qty']; $no++) {
			$Nomor	= sprintf('%03s', $no);
			$lopp++;
			$detailData[$lopp]['kd_asset'] 		= $kode_assets . $Nomor;
			$detailData[$lopp]['nm_asset'] 		= strtolower($data['nm_asset']);
			$detailData[$lopp]['kd_manual'] 	= strtolower($data['kd_manual']);
			$detailData[$lopp]['tgl_perolehan'] = $tgl_oleh;
			$detailData[$lopp]['category'] 		= $data['category'];
			$detailData[$lopp]['category_pajak'] = $data['category_pajak'];
			$detailData[$lopp]['nm_category'] 	= strtoupper($nmCategory[0]['nm_category']);
			$detailData[$lopp]['nilai_asset'] 	= str_replace(',', '', $data['nilai_asset']);
			$detailData[$lopp]['qty'] 			= $data['qty'];
			$detailData[$lopp]['asset_ke'] 		= $no;
			$detailData[$lopp]['depresiasi'] 	= $data['depresiasi'];
			$detailData[$lopp]['value'] 		= str_replace(',', '', $data['value']);
			$detailData[$lopp]['kdcab'] 		= $branch;
			$detailData[$lopp]['foto'] 			= $pic;
			$detailData[$lopp]['penyusutan'] 	= $penyusutan;
			$detailData[$lopp]['id_coa'] 	= $kelompok_penyusutan;
			$detailData[$lopp]['lokasi_asset'] 	= $data['lokasi_asset'];
			$detailData[$lopp]['cost_center'] 	= $data['cost_center'];
			$detailData[$lopp]['created_by'] 	= $this->session->userdata['app_session']['username'];
			$detailData[$lopp]['created_date'] 	= date('Y-m-d h:i:s');

			$jmlx   	= $data['depresiasi'] * 12;
			$date_now 	= date('Y-m-d');
			$date_now_real 	= date('Y-m-d');

			if (!empty($data['tanggal'])) {
				$date_now 	= $data['tanggal'];
			}

			for ($x = 1; $x <= $jmlx; $x++) {
				$lopp2 += $x;

				//bulan depat mulai menyusut
				// $Tanggal 	= date('Y-m', mktime(0,0,0,substr($date_now,5,2)+ $x,1,substr($date_now,0,4)));
				//bulan sekarang langsung disusutkan
				$TglNow		= date('Y-m', strtotime($date_now_real));
				$Tanggal 	= date('Y-m', mktime(0, 0, 0, substr($date_now, 5, 2) + $x, 0, substr($date_now, 0, 4)));
				$flagx		= 'X';
				if ($penyusutan == 'Y') {
					$flagx		= 'N';
					if ($Tanggal < $TglNow) {
						$flagx	= 'Y';
					}
				}

				$detailDataDash[$lopp2]['kd_asset'] 	= $kode_assets . $Nomor;
				$detailDataDash[$lopp2]['nm_asset'] 	= strtolower($data['nm_asset']);
				$detailDataDash[$lopp2]['kd_manual'] 	= strtolower($data['kd_manual']);
				$detailDataDash[$lopp2]['category'] 	= $data['category'];
				$detailDataDash[$lopp2]['category_pajak'] 	= $data['category_pajak'];
				$detailDataDash[$lopp2]['nm_category'] 	= strtoupper($nmCategory[0]['nm_category']);
				$detailDataDash[$lopp2]['bulan'] 		= substr($Tanggal, 5, 2);
				$detailDataDash[$lopp2]['tahun'] 		= substr($Tanggal, 0, 4);
				$detailDataDash[$lopp2]['lokasi_asset'] = $data['lokasi_asset'];
				$detailDataDash[$lopp2]['cost_center'] 	= $data['cost_center'];
				$detailDataDash[$lopp2]['nilai_susut'] 	= str_replace(',', '', $data['value']);
				$detailDataDash[$lopp2]['kdcab'] 		= $branch;
				$detailDataDash[$lopp2]['flag'] 		= $flagx;
			}
		}
		$this->db->trans_start();
		$this->db->insert_batch('asset', $detailData);
		$this->db->insert_batch('asset_generate', $detailDataDash);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Asset gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Asset berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}

	//move asset
	public function move_asset()
	{

		$Arr_Kembali	= array();
		$data			= $this->input->post();

		$session 		= $this->session->userdata('app_session');
		$nmCategory		= $this->Assets_depreciation_model->getWhere('asset_category', 'id', $data['category']);

		$category		= $data['category'];
		$KdCategory		= sprintf('%02s', $category);
		$Ym				= date('ym');
		$tgl_oleh		= date('Y-m-d');

		$branch				= $data['branch'];
		$kd_asset			= $data['kd_asset'];
		$lokasi_asset_new	= $data['lokasi_asset_new'];
		$cost_center_new	= $data['cost_center_new'];

		$ArrUpHeader = array(
			'kdcab' 	=> $branch,
			'lokasi_asset' 	=> $lokasi_asset_new,
			'cost_center'	=> $cost_center_new,
			'modified_by' 	=> $this->session->userdata['app_session']['username'],
			'modified_date' => date('Y-m-d h:i:s')
		);

		$ArrUpGen = array(
			'kdcab' 	=> $branch,
			'lokasi_asset' 	=> $lokasi_asset_new,
			'cost_center'	=> $cost_center_new
		);

		// echo $cost_center_new; exit;



		// print_r($detailData);
		// print_r($detailDataDash);
		// exit;

		$this->db->trans_start();
		$this->db->where('kd_asset', $kd_asset);
		$this->db->update('asset', $ArrUpHeader);

		$this->db->where(array('kd_asset' => $kd_asset, 'flag' => 'N'));
		$this->db->update('asset_generate', $ArrUpGen);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Asset gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Asset berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}

	//delete asset
	public function delete_asset()
	{

		$Arr_Kembali	= array();
		$data			= $this->input->post();

		$session 		= $this->session->userdata('app_session');
		$nmCategory		= $this->Assets_depreciation_model->getWhere('asset_category', 'id', $data['category']);

		$kd_asset		= $this->uri->segment(3);

		$ArrUpHeader = array(
			'deleted_by' 	=> $this->session->userdata['app_session']['username'],
			'deleted_date' => date('Y-m-d h:i:s')
		);

		$ArrUpGen = array(
			'flag' 	=> 'L'
		);

		$this->db->trans_start();
		$this->db->where('kd_asset', $kd_asset);
		$this->db->update('asset', $ArrUpHeader);

		$this->db->where(array('kd_asset' => $kd_asset, 'flag' => 'N'));
		$this->db->update('asset_generate', $ArrUpGen);
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Asset gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Asset berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}

	public function list_center()
	{
		$id = $this->uri->segment(3);
		$query	 	= "SELECT * FROM department_center WHERE id_dept='" . $id . "' ORDER BY cost_center ASC";
		$Q_result	= $this->db->query($query)->result();
		$option 	= "<option value='0'>Select an Option</option>";
		foreach ($Q_result as $row) {
			$option .= "<option value='" . $row->id . "'>" . strtoupper($row->cost_center) . "</option>";
		}
		echo json_encode(array(
			'option' => $option
		));
	}

	public function get_jangka_waktu()
	{
		$id = $this->uri->segment(3);
		$query	 	= "SELECT * FROM asset_category_pajak WHERE id='" . $id . "' ";
		$Q_result	= $this->db->query($query)->result();
		$data 	 	= $Q_result[0]->jangka_waktu;
		echo json_encode(array(
			'jangka_waktu' => $data
		));
	}

	public function edit()
	{
		$Arr_Kembali	= array();
		$data			= $this->input->post();
		$session 		= $this->session->userdata('app_session');

		$helpx			= $data['helpa'];

		if ($helpx == 'Y') {
			$nmCategory		= $this->Assets_depreciation_model->getWhere('asset_category', 'id', $data['category']);

			$category		= $data['category'];
			$kd_asset		= substr($data['kd_asset'], 0, 18);
			// echo $kd_asset."<br>";

			$KdCategory		= sprintf('%02s', $category);
			$Ym				= date('ym');

			$qQuery			= "SELECT max(kd_asset) as maxP FROM asset WHERE category='" . $category . "' AND kd_asset LIKE 'AST-" . $session['kdcab'] . $Ym . "-" . $KdCategory . "-%' ";
			$restQuery		= $this->db->query($qQuery)->result_array();

			$category		= $data['category'];

			$KdCategory		= sprintf('%02s', $category);
			$angkaUrut2		= $restQuery[0]['maxP'];
			$urutan2		= (int)substr($angkaUrut2, 15, 3);
			$urutan2++;
			$urut2			= sprintf('%03s', $urutan2);

			$kode_assets	= "AST-" . $session['kdcab'] . $Ym . "-" . $KdCategory . "-" . $urut2;

			// echo $kode_assets;

			$lopp = 0;
			for ($no = 1; $no <= $data['qty']; $no++) {
				$Nomor	= sprintf('%02s', $no);
				$lopp++;
				$detailData[$lopp]['kd_asset'] 		= $kode_assets . $Nomor;
				$detailData[$lopp]['nm_asset'] 		= $data['nm_asset'];
				$detailData[$lopp]['category'] 		= $data['category'];
				$detailData[$lopp]['nm_category'] 	= strtoupper($nmCategory[0]['nm_category']);
				$detailData[$lopp]['nilai_asset'] 	= str_replace(',', '', $data['nilai_asset']);
				$detailData[$lopp]['qty'] 			= $data['qty'];
				$detailData[$lopp]['asset_ke'] 		= $no;
				$detailData[$lopp]['depresiasi'] 	= $data['depresiasi'];
				$detailData[$lopp]['value'] 		= str_replace(',', '', $data['value']);
				$detailData[$lopp]['kdcab'] 		= $session['kdcab'];
				$detailData[$lopp]['lokasi_asset'] 	= $data['lokasi_asset'];
				$detailData[$lopp]['created_by'] 	= $this->session->userdata['app_session']['username'];
				$detailData[$lopp]['created_date'] 	= date('Y-m-d h:i:s');
			}

			// print_r($detailData);

			$Data_Del	= array(
				'deleted' 		=> "Y",
				'deleted_by' 	=> $this->session->userdata['app_session']['username'],
				'deleted_date' 	=> date('Y-m-d h:i:s')
			);
		} elseif ($helpx == 'N') {
			$idx			= $data['id'];
			$lokasi_asset	= $data['lokasi_asset'];

			$Data_Update	= array(
				'lokasi_asset' 	=> $lokasi_asset,
				'modified_by' 	=> $this->session->userdata['app_session']['username'],
				'modified_date' => date('Y-m-d h:i:s')
			);

			// print_r($Data_Update);
		}

		// exit;

		$this->db->trans_start();
		if ($helpx == 'Y') {
			$this->db->insert_batch('asset', $detailData);

			$this->db->where('kd_asset LIKE ', $kd_asset . '%');
			$this->db->update('asset', $Data_Del);
		} elseif ($helpx == 'N') {
			$this->db->where('id', $idx)->update('asset', $Data_Update);
		}
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Asset gagal disimpan ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Asset berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}

	public function download_excel_all_default($category = null, $bulan_sp = null, $tahun_sp = null)
	{
		//membuat objek PHPExcel
		set_time_limit(0);
		ini_set('memory_limit', '1024M');

		$this->load->library("PHPExcel");
		// $this->load->library("PHPExcel/Writer/Excel2007");
		$objPHPExcel	= new PHPExcel();

		$style_header = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$style_header2 = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'e0e0e0'),
			),
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$styleArray = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray3 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray4 = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('rgb' => '000000')
				)
			)
		);
		$styleArray1 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);
		$styleArray2 = array(
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
			)
		);

		$Arr_Bulan	= array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		$sheet 		= $objPHPExcel->getActiveSheet();

		$Row		= 1;
		$NewRow		= $Row + 1;
		$Col_Akhir	= $Cols	= getColsChar(11);
		$sheet->setCellValue('A' . $Row, 'DATA ASSETS');
		$sheet->getStyle('A' . $Row . ':' . $Col_Akhir . $NewRow)->applyFromArray($style_header2);
		$sheet->mergeCells('A' . $Row . ':' . $Col_Akhir . $NewRow);

		$NewRow	= $NewRow + 2;
		$NextRow = $NewRow;

		$sheet->setCellValue('A' . $NewRow, '#');
		$sheet->getStyle('A' . $NewRow . ':A' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('A' . $NewRow . ':A' . $NextRow);
		$sheet->getColumnDimension('A')->setWidth(10);

		$sheet->setCellValue('B' . $NewRow, 'KODE ASSET');
		$sheet->getStyle('B' . $NewRow . ':B' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('B' . $NewRow . ':B' . $NextRow);
		$sheet->getColumnDimension('B')->setWidth(20);

		$sheet->setCellValue('C' . $NewRow, 'ASSET NAME');
		$sheet->getStyle('C' . $NewRow . ':C' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('C' . $NewRow . ':C' . $NextRow);
		$sheet->getColumnDimension('C')->setAutoSize(true);

		$sheet->setCellValue('D' . $NewRow, 'TGL PEROLEHAN');
		$sheet->getStyle('D' . $NewRow . ':D' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('D' . $NewRow . ':D' . $NextRow);
		$sheet->getColumnDimension('D')->setAutoSize(true);

		$sheet->setCellValue('E' . $NewRow, 'CATEGORY');
		$sheet->getStyle('E' . $NewRow . ':E' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('E' . $NewRow . ':E' . $NextRow);
		$sheet->getColumnDimension('E')->setWidth(10);

		$sheet->setCellValue('F' . $NewRow, 'KELOMPOK PENYUSUTAN');
		$sheet->getStyle('F' . $NewRow . ':F' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('F' . $NewRow . ':F' . $NextRow);
		$sheet->getColumnDimension('F')->setWidth(10);

		$sheet->setCellValue('G' . $NewRow, 'DEPRESIASI (YEAR)');
		$sheet->getStyle('G' . $NewRow . ':G' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('G' . $NewRow . ':G' . $NextRow);
		$sheet->getColumnDimension('G')->setWidth(10);

		$sheet->setCellValue('H' . $NewRow, 'AQUISITION');
		$sheet->getStyle('H' . $NewRow . ':H' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('H' . $NewRow . ':H' . $NextRow);
		$sheet->getColumnDimension('H')->setWidth(10);

		$sheet->setCellValue('I' . $NewRow, 'DEPRECIATION VAL');
		$sheet->getStyle('I' . $NewRow . ':I' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('I' . $NewRow . ':I' . $NextRow);
		$sheet->getColumnDimension('I')->setWidth(10);

		$sheet->setCellValue('J' . $NewRow, 'AKUMULASI DEPRESIASI');
		$sheet->getStyle('J' . $NewRow . ':J' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('J' . $NewRow . ':J' . $NextRow);
		$sheet->getColumnDimension('J')->setWidth(10);

		$sheet->setCellValue('K' . $NewRow, 'ASSET VAL');
		$sheet->getStyle('K' . $NewRow . ':K' . $NextRow)->applyFromArray($style_header);
		$sheet->mergeCells('K' . $NewRow . ':K' . $NextRow);
		$sheet->getColumnDimension('K')->setWidth(10);

		$where_kategori = "";
		if ($category != '0') {
			$where_kategori = " AND a.nm_category = '" . $category . "' ";
		}

		$SQL = "
		SELECT
			a.id,
			a.tgl_perolehan,
			a.kd_asset,
			a.nm_asset,
			a.category,
			a.penyusutan,
			a.nm_category,
			a.nilai_asset,
			a.depresiasi,
			a.`value`,
			'' as department,
			a.kdcab,
			a.cost_center as cost_center_id,
			a.tgl_perolehan,
			d.no_perkiraan AS no_perkiraan,
			d.nama AS ket_coa,
			c.cost_center
		FROM
			asset a
			LEFT JOIN " . DBACC . ".coa_master d ON a.id_coa = d.no_perkiraan
			LEFT JOIN department_center c ON c.id = a.cost_center
		WHERE 1=1
			AND a.deleted = 'N'
			" . $where_kategori . "
			GROUP BY a.id
			ORDER BY a.id
		";

		$result = $this->db->query($SQL)->result_array();

		if ($result) {
			$awal_row	= $NextRow;
			$no = 0;
			foreach ($result as $key => $row_Cek) {

				$tahun_tgl_perolehan = date('y', strtotime($row_Cek['tgl_perolehan']));
				$tahun = (!empty($tahun_sp)) ? date('y', strtotime($tahun_sp)) : date('y');

				$exp_tahun = (($tahun - $tahun_tgl_perolehan) * 12);

				$bulan_tgl_perolehan = date('m', strtotime($row_Cek['tgl_perolehan']));
				$bulan = (!empty($bulan_sp)) ? date('m', strtotime('2024-' . sprintf('%02s', $bulan_sp . '-01'))) : date('m');

				$exp_bulan = ($bulan - $bulan_tgl_perolehan);

				$akumulasi_depresiasi = ($row_Cek['value'] * ($exp_tahun + $exp_bulan));
				if ($akumulasi_depresiasi < 0) {
					$akumulasi_depresiasi = 0;
				}
				if($akumulasi_depresiasi > $row_Cek['nilai_asset']) { 
					$akumulasi_depresiasi = $row_Cek['nilai_asset'];
				}

				$asset_val = ($row_Cek['nilai_asset'] - $akumulasi_depresiasi);
				if ($asset_val < 0) {
					$asset_val = 0;
				}
				
				$no++;
				$awal_row++;
				$awal_col	= 0;

				$awal_col++;
				$detail_name	= $no;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $detail_name);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$kd_asset	= strtoupper($row_Cek['kd_asset']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $kd_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$nm_asset	= strtoupper($row_Cek['nm_asset']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$tgl_perolehan	= $row_Cek['tgl_perolehan'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $tgl_perolehan);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$nm_category	= strtoupper($row_Cek['nm_category']);
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nm_category);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$KEL_PENYUSUTAN = (!empty($row_Cek['no_perkiraan'])) ? strtoupper($row_Cek['no_perkiraan'] . ' | ' . $row_Cek['ket_coa']) : '';
				$awal_col++;
				$Cols			= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $KEL_PENYUSUTAN);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray3);

				$awal_col++;
				$depresiasi		= $row_Cek['depresiasi'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $depresiasi);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$nilai_asset		= $row_Cek['nilai_asset'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $nilai_asset);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				$value		= $row_Cek['value'];
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $value);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				// $akumulasi_depresiasi = number_format($akumulasi_depresiasi, 2);
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $akumulasi_depresiasi);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);

				$awal_col++;
				// $asset_val = number_format($asset_val, 2);
				$Cols		= getColsChar($awal_col);
				$sheet->setCellValue($Cols . $awal_row, $asset_val);
				$sheet->getStyle($Cols . $awal_row)->applyFromArray($styleArray4);
			}
		}


		$sheet->setTitle('ASSETS');
		//mulai menyimpan excel format xlsx, kalau ingin xls ganti Excel2007 menjadi Excel5
		$objWriter		= PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		//sesuaikan headernya
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		//ubah nama file saat diunduh
		header('Content-Disposition: attachment;filename="data-assets-all.xls"');
		//unduh file
		$objWriter->save("php://output");
	}
}
