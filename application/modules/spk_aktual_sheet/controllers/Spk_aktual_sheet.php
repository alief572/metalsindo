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

class Spk_aktual_sheet extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Spk_produksi_aktual.View';
    protected $addPermission  	= 'Spk_produksi_aktual.Add';
    protected $managePermission = 'Spk_produksi_aktual.Manage';
    protected $deletePermission = 'Spk_produksi_aktual.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('Mpdf', 'upload', 'Image_lib'));
        $this->load->model(array('Spk_aktual_sheet/Inventory_4_model',
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
        $produksi = $this->Inventory_4_model->CariSPKReguler();
		 $aktual = 	$this->db->query("SELECT * FROM tr_spk_aktual WHERE jenis='sheet' ")->result();
		$data = [
			'produksi' => $produksi,
			'aktual' => $aktual
		];
        $this->template->set('results', $data);
        $this->template->title('Aktual Produksi');
        $this->template->render('index');
    }
	
	public function Approve_new(){
		$this->auth->restrict($this->deletePermission);
		$id = $this->input->post('id');
		
		
		
		$this->db->trans_begin();
		$gudang = $this->db->query("SELECT * FROM tr_spk_produksi_sheet WHERE id_tr_spk_produksi ='$id' ")->result();
		$cariid = $this->db->query("SELECT MAX(id_stock) as maxid FROM stock_material ")->result();
		$idmax =$cariid[0]->maxid;
		$id_stock=$gudang[0]->id_stock;
		$materialstock = $this->db->query("SELECT * FROM stock_material WHERE id_stock ='$id_stock' ")->result();
		$jumlahstock=$materialstock[0]->qty;
		$beratstock=$materialstock[0]->weight;
		
		//update status gudang
		$get_potongan = $this->db->get_where('stock_material', array('id_stock'=>$id_stock))->result();
		$detail_potonganx = (!empty($get_potongan[0]->detail_potongan))?json_decode($get_potongan[0]->detail_potongan, true):array();
		// print_r($detail_potonganx);
		
		$ArrOld = null;
		if(!empty($detail_potonganx)){
			foreach($detail_potonganx AS $val => $valx){
				if($id <> $valx['kode']){
					if(!empty($valx['kode'])){
						$ArrOld[] = [
							'kode' => $valx['kode'],
							'id_stock' => $valx['id_stock'],
							'berat' => $valx['berat'],
							'id_gudang' => 1
						];
					}
				}
				else{
					if(!empty($valx['kode'])){
						$ArrOld[] = [
							'kode' => $valx['kode'],
							'id_stock' => $valx['id_stock'],
							'berat' => $valx['berat'],
							'id_gudang' => 2
						];
						
						$beratstock = $valx['berat'];
					}
				}
			}
		}
		
		if($jumlahstock <= '1'){
			$data = [
				'status_approve' 	=> '2',
				'id_stock_app' 		=> $idmax+1
			];
			$this->db->where('id_tr_spk_produksi',$id)->update("tr_spk_produksi_sheet",$data);
			
			
			
			$this->db->where('id_stock',$id_stock);
			$this->db->update('stock_material',array('detail_potongan'=>json_encode($ArrOld)));
			
			$data3= [
						'id_category3'			=> $materialstock[0]->id_category3,
						'nama_material'			=> $materialstock[0]->nama_material,
						'width'					=> $materialstock[0]->width,
						'lotno'					=> $materialstock[0]->lotno,
						'qty'					=> '1',
						'id_bentuk'				=> $materialstock[0]->id_bentuk,
						'length'				=> $materialstock[0]->length,
						'thickness'				=> $materialstock[0]->thickness,
						'weight'				=> $beratstock,
						'totalweight'			=> $beratstock,
						'aktif'					=> 'Y',
						'id_gudang'				=> '2',
						'created_on'			=> date('Y-m-d H:i:s'),
						'created_by'			=> $this->auth->user_id()
					];
			$this->db->insert('stock_material',$data3);
			// $data2 = [
				// 'id_gudang' 		=> '2'
			// ];
			// $this->db->where('id_stock',$id_stock)->update("stock_material",$data2);
		}else{
			$data = [
				'status_approve' 		=> '2',
				'id_stock_app' 			=> $idmax+1
			];
			$this->db->where('id_tr_spk_produksi',$id)->update("tr_spk_produksi_sheet",$data);
			
			$data2 = [
				'qty' 		=> $jumlahstock-1
			];
			$this->db->where('id_stock',$id_stock)->update("stock_material",$data2);
			
			$data3= [
						'id_category3'			=> $materialstock[0]->id_category3,
						'nama_material'			=> $materialstock[0]->nama_material,
						'width'					=> $materialstock[0]->width,
						'lotno'					=> $materialstock[0]->lotno,
						'qty'					=> '1',
						'id_bentuk'				=> $materialstock[0]->id_bentuk,
						'length'				=> $materialstock[0]->length,
						'thickness'				=> $materialstock[0]->thickness,
						'weight'				=> $beratstock,
						'totalweight'			=> $beratstock,
						'aktif'					=> 'Y',
						'id_gudang'				=> '2',
						'created_on'			=> date('Y-m-d H:i:s'),
						'created_by'			=> $this->auth->user_id()
					];
			$this->db->insert('stock_material',$data3);
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
			  'pesan'		=>'Success Save Item. Thanks ...',
			  'status'	=> 1
			);
		}

  		echo json_encode($status);
	}
	
	
	public function EditHeader()
    {
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		
		$aktual = $this->db->query("SELECT a.* FROM tr_spk_aktual a where a.id_spk_aktual = '".$id."'")->num_rows();
		
		// print_r($id);
		// exit;
		
		if($id =='View' || $id=='Edit'){
		$id2 = 	$this->uri->segment(4);		
		$tr_spk = $this->Inventory_4_model->get_data('tr_spk_aktual','id_spk_aktual',$id2);
		$dt_spk =$this->db->query("SELECT a.* , b.tgl_produksi, b.id_material FROM dt_spk_aktual_detail a 
		INNER JOIN tr_spk_aktual b ON b.id_spk_aktual = a.id_spk_aktual  WHERE a.id_spk_aktual = '".$id2."' ")->result();	
			// echo "<pre>";
		// print_r($tr_spk);
		// echo "<pre>";
		// exit;
		}
		else{
		$tr_spk = $this->db->get_where('tr_spk_produksi_sheet', array('id_spkproduksi'=>$id))->result();
		
		$dt_spk =$this->db->query("SELECT a.*, b.name_customer as name_customer FROM dt_spk_produksi_sheet as a LEFT JOIN master_customers as b on a.idcustomer = b.id_customer where a.id_spkproduksi = '".$id."'")->result();
		}
		
		$penawaran = $this->Inventory_4_model->get_data('tr_penawaran');
		$stock = $this->Inventory_4_model->get_data('stock_material');
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan','deleted',$deleted);
		$material = $this->Inventory_4_model->get_data_category3();
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang','deleted',$deleted);
		
		 // print_r($dt_spk);
		 // exit;
		
		$data = [
			'tr_spk'	=> $tr_spk,
			'dt_spk'	=> $dt_spk,
			'penawaran' => $penawaran,
			'stock' => $stock,
			'karyawan' => $karyawan,
			'material' => $material,
			'mata_uang' => $mata_uang,
		];
        $this->template->set('results', $data);
        $this->template->title('Aktual Produksi');
		if($id =="View"){
        $this->template->render('EditHeader2');
		}
		if($id =="Edit"){
        $this->template->render('EditHeader3');
		}
		else{
		$this->template->render('EditHeader');
		}

    }
	
	
	public function SaveEditHeader()
    { 
        $this->auth->restrict($this->addPermission);
		$post = $this->input->post();
		
		// print_r($post);
		// exit;
		
		$nomor = $post['nomor'];
		
		$this->db->trans_begin();
		$id_spk_aktual			= $post['id_spk_aktual'];
		$id_spkproduksi			= $post['id_spk_produksi'];
		
		$no_surat_produksi		= $post['no_surat'];
		$id_category3		    = $post['id_material'];
		$id_stock				= $post['id_stock'];
		
		// print_r($nomor);
		// exit;
		
		//TAMBAHAN
		$ngin_lebar1	= $post['ngin_lebar'];
		$ngin_berat1	= $post['ngin_berat'];
		$ngin_ket1		= $post['ngin_ket'];
		$ngin_upload1	= $_FILES['ngin_upload'];
		
		
		$ngin_lebar		= json_encode($post['ngin_lebar']);
		$ngin_berat		= json_encode($post['ngin_berat']);
		$ngin_ket		= json_encode($post['ngin_ket']);
		$ngin_upload	= $_FILES['ngin_upload'];
		
		
		

		$ngek_lebar1	= $post['ngek_lebar'];
		$ngek_berat1	= $post['ngek_berat'];
		$ngek_ket1		= $post['ngek_ket'];
		$ngek_upload1	= $_FILES['ngek_upload'];
		
		
		$ngek_lebar		= json_encode($post['ngek_lebar']);
		$ngek_berat		= json_encode($post['ngek_berat']);
		$ngek_ket		= json_encode($post['ngek_ket']);
		$ngek_upload	= $_FILES['ngek_upload'];
		

		$countfiles 	= count($_FILES['ngin_upload']['name']);
		for($i=0;$i<$countfiles;$i++){
			$nama_url = "";
			if(!empty($_FILES["ngin_upload"]["tmp_name"][$i])){
				$target_dir     = "assets/files/aktual/";
				$target_dir_u   = $_SERVER['DOCUMENT_ROOT']."/metalsindo_dev/assets/files/aktual/";
				$name_file      = $i.date('Ymdhis');
				$target_file    = $target_dir . basename($_FILES["ngin_upload"]["name"][$i]);
				
				$imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
				$nama_upload    = $target_dir_u.$name_file.".".$imageFileType;
				
				// if($imageFileType == 'pdf' OR $imageFileType == 'jpg' OR $imageFileType == 'png' OR $imageFileType == 'jpeg'){
					$nama_url    	= $target_dir.$name_file.".".$imageFileType;
					$terupload = move_uploaded_file($_FILES["ngin_upload"]["tmp_name"][$i], $nama_upload);
				// }
			}
			$ArrInsert[] = $nama_url;
		}
		$upload_in =  json_encode($ArrInsert);

		//EXTERNAL
		$countfiles2 	= count($_FILES['ngek_upload']['name']);
		for($i=0;$i<$countfiles2;$i++){
			$nama_url = "";
			if(!empty($_FILES["ngek_upload"]["tmp_name"][$i])){
				$target_dir     = "assets/files/aktual/";
				$target_dir_u   = $_SERVER['DOCUMENT_ROOT']."/metalsindo_dev/assets/files/aktual/";
				$name_file      = $i.date('Ymdhis');
				$target_file    = $target_dir . basename($_FILES["ngek_upload"]["name"][$i]);
				
				$imageFileType  = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
				$nama_upload    = $target_dir_u.$name_file.".".$imageFileType;
				
				// if($imageFileType == 'pdf' OR $imageFileType == 'jpg' OR $imageFileType == 'png' OR $imageFileType == 'jpeg'){
					$nama_url    	= $target_dir.$name_file.".".$imageFileType;
					$terupload = move_uploaded_file($_FILES["ngek_upload"]["tmp_name"][$i], $nama_upload);
				// }
			}
			$ArrInsert2[] = $nama_url;
		}
		$upload_ek =  json_encode($ArrInsert2);

		// exit;

		$this->db->where('id_spkproduksi',$id_spk_aktual);
		$this->db->delete('stock_material_temp');
		
		$inventory =$this->db->query("SELECT * FROM ms_inventory_category3 WHERE id_category3 = '".$id_category3."' ")->result();
		$idbentuk		=  $inventory[0]->id_bentuk;
		// $stock = $this->db->query("SELECT * FROM stock_material WHERE id_stock = '".$id_stock."' ")->result();
		// $jmlstock		=  $stock[0]->qty;
		$data = [
							'id_spk_aktual'			=> $post['id_spk_aktual'],
							'id_spkproduksi'		=> $post['id_spk_produksi'],
							'no_surat_produksi'		=> $post['no_surat'],
							'tgl_produksi'			=> date('Y-m-d'),
							'id_stock'				=> $post['id_stock'],
							'date_production'		=> date('Y-m-d', strtotime($post['date_production'])),
							'lotno'					=> $post['lotno'],
							'id_material'			=> $post['id_material'],
							'nama_material'			=> $post['nama_material'],
							'weight'				=> $post['weight1'],
							'thickness'				=> $post['thickness'],
							'density'				=> $post['density'], 
							'panjang'				=> $post['panjang'],
							'width'					=> $post['width'],
							'lpegangan'				=> $post['lpegangan'],
							'qcoil'					=> $post['qcoil'],
							'jpisau'				=> $post['jpisau'],
							'terpakai'				=> $post['terpakai'],
							'lsisa_planing'			=> $post['lsisa_planing'],
							'lsisa_aktual'			=> $post['lsisa_aktual'],
							'bsisa_planing'			=> $post['bsisa_planing'],
							'bsisa_aktual'			=> $post['bsisa_aktual'],
							'lscrap_planing'		=> $post['lscrap_planing'],
							'lscrap_aktual'			=> $post['lscrap_aktual'],
							'bscrap_planing'		=> $post['bscrap_planing'],
							'bscrap_aktual'			=> $post['bscrap_aktual'],
							'created_on'			=> date('Y-m-d H:i:s'),
							'created_by'			=> $this->auth->user_id(),
							'difference'			=> $post['difference'],
							'differencepercent'		=> $post['differencepercent'],
							'balance'				=> $post['balance'],
							
							'in_lebar'			=> $ngin_lebar,
							'in_berat'			=> $ngin_berat,
							'in_ket'			=> $ngin_ket,
							'in_upload'			=> $upload_in,
							'ek_lebar'			=> $ngek_lebar,
							'ek_berat'			=> $ngek_berat,
							'ek_ket'			=> $ngek_ket,
							'ek_upload'			=> $upload_ek,
							
							'weight_actualpl'		=> str_replace(',','',$post['weight_actualpl']),
							'kg_sisa'				=> str_replace(',','',$post['kg_sisa']),
							'kg_sisa_actual'		=> str_replace(',','',$post['kg_sisa_actual']),
							'ng_packinglist1'		=> str_replace(',','',$post['ng_packinglist1']),
							'actualweightproses'	=> str_replace(',','',$post['actualweightproses']),
							'lebar_ng_in'			=> $ngin_lebar1[0],
							'berat_ng_in'			=> $ngin_berat1[0],
							'lebar_ng_ek'			=> $ngek_lebar1[0],
							'berat_ng_ek'			=> $ngek_berat1[0],
							
							'widthproduksi'		    => str_replace(',','',$post['widthproduksi']),
							'weightproduksi'		=> str_replace(',','',$post['weightproduksi']),
							'widthmothercoil'		=> str_replace(',','',$post['widthmothercoil']),
							'weightproses'		    => str_replace(',','',$post['weightproses']),
							'widthsisa'		    	=> str_replace(',','',$post['widthsisa']),
							'weightsisa'			=> str_replace(',','',$post['weightsisa']),
							'bpackinglist'			=> str_replace(',','',$post['bpackinglist']),
							'baktualmother'			=> str_replace(',','',$post['baktualmother']),
							'ng_packinglist'		=> str_replace(',','',$post['ng_packinglist']),
							'baktualweight_proses'			=> str_replace(',','',$post['baktualweight_proses']),
							'sisa_aktualweight_proses'		=> str_replace(',','',$post['sisa_aktualweight_proses']),
							'lfinishgood'		    		=> str_replace(',','',$post['lfinishgood']),
							'bfinishgood'		    		=> str_replace(',','',$post['bfinishgood']),
							'tweightslitting'				=> str_replace(',','',$post['tweightslitting']),
							'aktual'		        		=> str_replace(',','',$post['aktual']),
							'bfinishgood2'		        	=> str_replace(',','',$post['bfinishgood2']),
							'bfinishgood3'		        	=> str_replace(',','',$post['bfinishgood3']),
							'bpackinglistselisih'		        => str_replace(',','',$post['bpackinglistselisih']),
							'baktualmotherselisih'		        => str_replace(',','',$post['baktualmotherselisih']),
							'bpackinglistselisihpersen'		    => str_replace(',','',$post['bpackinglistselisihpersen']),
							'baktualmotherselisihpersen'	    => str_replace(',','',$post['baktualmotherselisihpersen'])

							];
			
			
			$this->db->where('id_spk_aktual',$id_spk_aktual);
			$this->db->delete('tr_spk_aktual_sheet');
		
			$this->db->insert('tr_spk_aktual_sheet',$data);
			
			
			// $this->db->where('id_spk_aktual',$id_spk_aktual);
			// $this->db->delete('dt_ng_in');
			
			// $this->db->insert_batch('dt_ng_in',$ArrNGIN);
			
			// $this->db->where('id_spk_aktual',$id_spk_aktual);
			// $this->db->delete('dt_ng_ek');
			
			// $this->db->insert_batch('dt_ng_ek',$ArrNGEK);
				// $ubahstatus = [
				// 			'status_approve'		=> '3'
                //             ];
				$ubahstatus = [
								'approve'		=> '1',								
							];
                 $this->db->where('id',$nomor)->update("dt_spk_aktual_detail_sheet",$ubahstatus); 
			  
			   // $this->db->where('id_spkproduksi',$id_spkproduksi)->update("tr_spk_produksi",$ubahstatus);
			  //Stock
			// 	$oldstock = [
			// 				'qty'					=> '0',
			// 				'weight'				=> '0',
			// 				'totalweight'			=> '0',
			// 				'aktif'					=> 'N',
			// 				'id_gudang'				=> '4',
            //                 ];
            //   $this->db->where('id_stock',$id_stock)->update("stock_material",$oldstock);
			
				$ngin = [
							'id_spkproduksi'		=> $id_spk_aktual,
							'id_category3'			=> $post['id_material'],
							'nama_material'			=> $post['nama_material'],
							'width'					=> $ngin_lebar1[0],
							'lotno'					=> $post['lotno'],
							'qty'					=> '1',
							'id_bentuk'				=> $idbentuk,
							'length'				=> $post['panjang'],
							'thickness'				=> $post['thickness'], 
							'weight'				=> $ngin_berat1[0],
							'totalweight'			=> $ngin_berat1[0],
							'aktif'					=> 'Y',
							'id_gudang'				=> '4',
							'created_on'			=> date('Y-m-d H:i:s'),
							'created_by'			=> $this->auth->user_id(),
							'sisa_spk'				=> $ngin_berat1[0],
                            ];
               $this->db->insert('stock_material_temp',$ngin);
			   
			   
			   
			   $ncr = [
							'id_spkproduksi'		=> $id_spk_aktual,
							'id_category3'			=> $post['id_material'],
							'nama_material'			=> $post['nama_material'],
							'width'					=> $ngek_lebar1[0],
							'lotno'					=> $post['lotno'],
							'qty'					=> '1',
							'id_bentuk'				=> $idbentuk,
							'length'				=> $post['panjang'],
							'thickness'				=> $post['thickness'],
							'weight'				=> $ngek_berat1[0],
							'totalweight'			=> $ngek_berat1[0],
							'aktif'					=> 'Y',
							'id_gudang'				=> '6',
							'created_on'			=> date('Y-m-d H:i:s'),
							'created_by'			=> $this->auth->user_id(),
							'sisa_spk'				=> $ngek_berat1[0]
                            ];
               $this->db->insert('stock_material_temp',$ncr);
			   
			   
				$scrap = [
							'id_spkproduksi'		=> $id_spk_aktual,
							'id_category3'			=> $post['id_material'],
							'nama_material'			=> $post['nama_material'],
							'width'					=> $post['lscrap_aktual'],
							'lotno'					=> $post['lotno'],
							'qty'					=> '1',
							'id_bentuk'				=> $idbentuk,
							'length'				=> $post['panjang'],
							'thickness'				=> $post['thickness'],
							'weight'				=> ($post['bscrap_aktual'] != 0 AND $post['lscrap_aktual'] != 0)?$post['bscrap_aktual']/$post['lscrap_aktual']:0,
							'totalweight'			=> $post['bscrap_aktual'],
							'aktif'					=> 'Y',
							'id_gudang'				=> '4',
							'created_on'			=> date('Y-m-d H:i:s'),
							'created_by'			=> $this->auth->user_id(),
							'sisa_spk'				=> $post['bscrap_aktual']
                            ];
               $this->db->insert('stock_material_temp',$scrap);
			   
			   
			   $lotnew = $post['lotno'];
			   $l       = '.A';
			   $lotbaru = $lotnew.$l;
			   
			   $sisa = [
							'id_spkproduksi'			=>$id_spk_aktual,
							'id_category3'			=> $post['id_material'],
							'nama_material'			=> $post['nama_material'],
							'width'					=> $post['lsisa_aktual'],
							'lotno'					=> $lotbaru,
							'qty'					=> '1',
							'id_bentuk'				=> $idbentuk,
							'length'				=> $post['panjang'],
							'thickness'				=> $post['thickness'],
							'weight'				=> $post['bsisa_aktual'],
							'totalweight'			=> $post['bsisa_aktual'],
							'aktif'					=> 'Y',
							'id_gudang'				=> '1',
							'created_on'			=> date('Y-m-d H:i:s'),
							'created_by'			=> $this->auth->user_id(),
						    'sisa_spk'				=> $post['bsisa_aktual']
                            ];
               $this->db->insert('stock_material_temp',$sisa);
			   
			   
			   $lost = [
							'id_spkproduksi'		=> $id_spk_aktual,
							'id_category3'			=> $post['id_material'],
							'nama_material'			=> $post['nama_material'],
							'width'					=> $post['width'],
							'lotno'					=> $post['lotno'],
							'qty'					=> '1',
							'id_bentuk'				=> $idbentuk,
							'length'				=> $post['panjang'],
							'thickness'				=> $post['thickness'],
							'weight'				=> $post['difference'],
							'totalweight'			=> $post['difference'],
							'aktif'					=> 'Y',
							'id_gudang'				=> '6',
							'created_on'			=> date('Y-m-d H:i:s'),
							'created_by'			=> $this->auth->user_id(),
							'sisa_spk'				=> $post['difference']
                            ];
               $this->db->insert('stock_material_temp',$lost);
			   
			   $pl = [
							'id_spkproduksi'		=> $id_spk_aktual,
							'id_category3'			=> $post['id_material'],
							'nama_material'			=> $post['nama_material'],
							'width'					=> $post['width'],
							'lotno'					=> $post['lotno'],
							'qty'					=> '1',
							'id_bentuk'				=> $idbentuk,
							'length'				=> $post['panjang'],
							'thickness'				=> $post['thickness'],
							'weight'				=> $post['ng_packinglist1'],
							'totalweight'			=> $post['ng_packinglist1'],
							'aktif'					=> 'Y',
							'id_gudang'				=> '6',
							'created_on'			=> date('Y-m-d H:i:s'),
							'created_by'			=> $this->auth->user_id(),
							'sisa_spk'				=> $post['ng_packinglist1']
                            ];
               //$this->db->insert('stock_material_temp',$pl);
			   
			   
		$numb1 =0;
		$ArrDetail = [];
		$ArrInsertStock = [];
		foreach($_POST['dt'] as $used){
			$numb1++;        
			$dt =  array(
						'id_spk_aktual'			=> $id_spk_aktual,
						'id_spkproduksi'		=> $post['id_spk_produksi'],
						'no_surat'				=> $used[nosurat],
						'id_dt_spk_aktual'		=> $id_spk_aktual.'-'.$numb1,
						'idcustomer'			=> $used[idcustomer],
						'namacustomer'			=> $used[namacustomer],
						'nmmaterial'		    => $used[nmmaterial],
						'thickness'		    	=> $used[thickness],
						'weight'		        => str_replace(',','',$post['tweightslitting']),
						'qtycoil'		        => $used[qtycoil]
						// 'qtyaktual'		        => $used[qtyaktual],
						// 'width'					=> $used[width],
						// 'totalwidth'		        => $used[totalwidth],
						// 'totalaktual'		    => $used[totalaktual],
						// 'id_dt_spkproduksi'		=> $used[id_dt_spkproduksi],
						// 'delivery'				=> $used[delivery],
						// 'lot_slitting'			=> $used['lot_slitting']
						);
						
			$this->db->where('id_spk_aktual',$id_spk_aktual);
			$this->db->delete('dt_spk_aktual_sheet');
			
			$this->db->insert('dt_spk_aktual_sheet',$dt);
			
			
			
			
			
			foreach ($used[qtyaktual] as $key => $value) {
				$ArrDetail[$key.$numb1]['id_spk_aktual'] = $id_spk_aktual;
				$ArrDetail[$key.$numb1]['id_dt_spkaktualdetail'] =$nomor;
				$ArrDetail[$key.$numb1]['id_spkproduksi'] = $id_spkproduksi;
				$ArrDetail[$key.$numb1]['no_surat'] =$used[nosurat];
				$ArrDetail[$key.$numb1]['id_dt_spk_aktual'] = $id_spk_aktual.'-'.$numb1;
				$ArrDetail[$key.$numb1]['idcustomer'] =  $used[idcustomer];
				$ArrDetail[$key.$numb1]['namacustomer'] =  $used[namacustomer];
				$ArrDetail[$key.$numb1]['nmmaterial'] =  $used[nmmaterial];
				$ArrDetail[$key.$numb1]['thickness'] =  $used[thickness];
				$ArrDetail[$key.$numb1]['density'] =  $used[density];
				$ArrDetail[$key.$numb1]['qtycoil'] =  $used[qtycoil];
				$ArrDetail[$key.$numb1]['qtysheet'] =  $used[qtysheet];
				$ArrDetail[$key.$numb1]['total_sheet'] =  $used[qtysheet];
				$ArrDetail[$key.$numb1]['id_tr_spk_produksi'] =  $used[id_tr_spk_produksi];
			}
			foreach ($used[weight] as $key => $value) {
				$ArrDetail[$key.$numb1]['weight'] = $value;
				$ArrDetail[$key.$numb1]['width_sheet'] = $value;
			}
			foreach ($used[qtyaktual] as $key => $value) {
				$ArrDetail[$key.$numb1]['qtyaktual'] = $value;
			}
			foreach ($used[width] as $key => $value) {
				$ArrDetail[$key.$numb1]['width'] = $value;
			}
			foreach ($used[totalwidth] as $key => $value) {
				$ArrDetail[$key.$numb1]['totalwidth'] = $value;
			}
			foreach ($used[qtywidth] as $key => $value) {
				$ArrDetail[$key.$numb1]['qtysatuan'] = str_replace(',','',$value);
			}
			foreach ($used[totalaktual] as $key => $value) {
				$ArrDetail[$key.$numb1]['totalaktual'] = str_replace(',','',$value);
			}
			foreach ($used[id_dt_spkproduksi] as $key => $value) {
				$ArrDetail[$key.$numb1]['id_dt_spkproduksi'] = $value;
			}
			foreach ($used[delivery] as $key => $value) { 
				$ArrDetail[$key.$numb1]['delivery'] = $value;
			}
			foreach ($used[lot_slitting] as $key => $value) {
				$ArrDetail[$key.$numb1]['lot_slitting'] = $value;
			}
			foreach ($used[keterangan] as $key => $value) {
				$ArrDetail[$key.$numb1]['keterangan'] = $value;
			}
			foreach ($used[length] as $key => $value) {
				$ArrDetail[$key.$numb1]['length_sheet'] = $value;
			}
			foreach ($used[widthperkg] as $key => $value) {
				$ArrDetail[$key.$numb1]['qty_persheet'] = $value;
			}
			

			//baru insert  
			foreach ($used[qtyaktual] as $key => $value) {
				$ArrInsertStock[$key.$numb1]['no_surat'] =$used[nosurat];
				$ArrInsertStock[$key.$numb1]['id_spkproduksi'] = $id_spk_aktual;
				$ArrInsertStock[$key.$numb1]['id_category3'] = $post['id_material'];
				$ArrInsertStock[$key.$numb1]['nama_material'] = $used[nmmaterial];
				// $ArrInsertStock[$key.$numb1]['lotno'] = $post['lotno'];
				$ArrInsertStock[$key.$numb1]['id_bentuk'] =  $idbentuk;
				
				$ArrInsertStock[$key.$numb1]['thickness'] =  $post['thickness'];
				$ArrInsertStock[$key.$numb1]['aktif'] =  'Y';
				$ArrInsertStock[$key.$numb1]['id_gudang'] =  '3';
				$ArrInsertStock[$key.$numb1]['created_on'] =  date('Y-m-d H:i:s');
				$ArrInsertStock[$key.$numb1]['created_by'] =  $this->auth->user_id();
			}
			foreach ($used[length] as $key => $value) {
				$ArrInsertStock[$key.$numb1]['length'] =  $value;
			}
			foreach ($used[lot_slitting] as $key => $value) {
				$ArrInsertStock[$key.$numb1]['lotno'] = $value;
				$ArrInsertStock[$key.$numb1]['lot_slitting'] = $value;
			}
			foreach ($used[totalaktual] as $key => $value) {
				$ArrInsertStock[$key.$numb1]['totalweight'] = str_replace(',','',$value);
			}
			foreach ($used[qtyaktual] as $key => $value) {
				$ArrInsertStock[$key.$numb1]['qty'] = $value; 
			}
			foreach ($used[weight] as $key => $value) {
				$ArrInsertStock[$key.$numb1]['width'] = $value; 
			}
			foreach ($used[totalaktual] as $key => $value) {
				$ArrInsertStock[$key.$numb1]['weight'] = str_replace(',','',$value) / $used[qtyaktual][$key];
				$ArrInsertStock[$key.$numb1]['sisa_spk'] = str_replace(',','',$value) / $used[qtyaktual][$key];
			}
			
		}
	
		

		// print_r($ArrDetail);
		// print_r($ArrInsertStock);
		 // exit;
		
		$this->db->where('id_spk_aktual',$id_spk_aktual);
		$this->db->delete('dt_spk_aktual_detail_sheet');
		
		$this->db->insert_batch('dt_spk_aktual_detail_sheet',$ArrDetail); 
		$this->db->insert_batch('stock_material_temp',$ArrInsertStock);

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
	
	public function EditHeadernew()
    {
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		
		$aktual = $this->db->query("SELECT a.* FROM tr_spk_aktual_sheet a where a.id_spk_aktual = '".$id."'")->num_rows();
		
		// print_r($id);
		// exit;
		
		if($id =='View' || $id=='Edit'){
		$id2 = 	$this->uri->segment(4);		
		$tr_spk = $this->Inventory_4_model->get_data('tr_spk_aktual_sheet','id_spk_aktual',$id2);
		$dt_spk =$this->db->query("SELECT a.* FROM dt_spk_aktual_detail_sheet as a  where a.id_spk_aktual = '".$id2."' GROUP BY a.id_tr_spk_produksi ")->result();
		
			// echo "<pre>";
		// print_r($dt_spk);
		// echo "<pre>";
		// exit;
		}
		else{		
		$tr_spk = $this->Inventory_4_model->get_data('dt_tr_spk_produksi','id_tr_spk_produksi',$id);
		
		$dt_spk =$this->db->query("SELECT a.*, b.name_customer as name_customer FROM dt_spk_produksi as a LEFT JOIN master_customers as b on a.idcustomer = b.id_customer where a.id_tr_spk_produksi = '".$id."' AND a.checked = '1' ")->result();
		}
		
		$penawaran = $this->Inventory_4_model->get_data('tr_penawaran');
		$stock = $this->Inventory_4_model->get_data('stock_material');
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan','deleted',$deleted);
		$material = $this->Inventory_4_model->get_data_category3();
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang','deleted',$deleted);
		
		 // print_r($dt_spk);
		 // exit;
		
		$data = [
			'tr_spk'	=> $tr_spk,
			'dt_spk'	=> $dt_spk,
			'penawaran' => $penawaran, 
			'stock' => $stock,
			'karyawan' => $karyawan,
			'material' => $material,
			'mata_uang' => $mata_uang,
		];
        $this->template->set('results', $data);
        $this->template->title('Aktual Produksi');
		if($id =='View'){
        $this->template->render('EditHeader_new2');
		}
		else if($id =='Edit'){
        $this->template->render('EditHeader_new3');
		}
		else if($id !='View' || $id !='Edit'){ 
		$this->template->render('EditHeader_new');
		}

    }
	
	public function TambahLHP()
    {
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$aktif = 'active';
		$deleted = '0';
		$tr_spk = $this->Inventory_4_model->get_data('tr_spk_produksi_sheet','id_spkproduksi',$id);
		$dt_spk =$this->db->query("SELECT a.*, b.name_customer as name_customer FROM dt_spk_produksi_sheet as a LEFT JOIN master_customers as b on a.idcustomer = b.id_customer where a.id_tr_spk_produksi = '".$id."'")->result();
		$penawaran = $this->Inventory_4_model->get_data('tr_penawaran');
		$stock = $this->Inventory_4_model->get_data('stock_material');
		$karyawan = $this->Inventory_4_model->get_data('ms_karyawan','deleted',$deleted);
		$material = $this->Inventory_4_model->get_data_category3();
		$mata_uang = $this->Inventory_4_model->get_data('mata_uang','deleted',$deleted);

		$detailLHP = $this->db->get_where('dt_spk_produksi_lph', array('id_spk_aktual'=>$id))->result_array();

		$data = [
			'tr_spk'	=> $tr_spk,
			'dt_spk'	=> $dt_spk,
			'penawaran' => $penawaran,
			'stock' => $stock,
			'karyawan' => $karyawan,
			'material' => $material,
			'mata_uang' => $mata_uang,
			'detail_aktual' => $detailLHP,
		];
        $this->template->set('results', $data);
        $this->template->title('Input LHP');
        $this->template->render('TambahLHP');

    }
	
			public function FormLHP()
    {
		$id = $this->uri->segment(3);
		$this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$data =$this->db->query("SELECT * FROM tr_spk_produksi WHERE id_spkproduksi = '".$id."' ")->result();
        $this->template->set('results', $data);
        $this->template->title('Input LHP');
        $this->template->render('FormLHP');

    }
	
	public function SaveLHP(){
		$Arr_Kembali	= array();
		$data			= $this->input->post();
		
		// print_r($data);
		// exit;
		$code 		= $data['id_spk_aktual'];
		$code2 		= $data['id_tr_spkproduksi'];
		$session 	= $this->session->userdata('app_session');
		$Detail 	= $data['dt'];
		
		$getID_Lot = $this->db->select('id_stock')->get_where('tr_spk_produksi_sheet', array('id_spkproduksi' => $code))->result();
		$ID_LOT = (!empty($getID_Lot[0]->id_stock))?$getID_Lot[0]->id_stock:NULL;
		
		$ArrSPKPro = [];
		
		if($ID_LOT != NULL){
			$getID_LotArray = $this->db->select('id_spkproduksi, id_tr_spk_produksi')->get_where('tr_spk_produksi_sheet', array('id_stock' => $ID_LOT))->result_array();
			// print_r($getID_LotArray);
			$ArrDetail2	= array();
			$urut = 0;
			foreach($getID_LotArray AS $valY => $valYx){
				
				$valY++;
				foreach($Detail AS $val2 => $valx2){
					$urut++;
				
					$UNIQ = $urut.$valY;
					$ArrDetail2[$UNIQ]['id_spk_aktual']		= $valYx['id_spkproduksi'];
					$ArrDetail2[$UNIQ]['namaproses'] 		= $valx2['namaproses'];
					$ArrDetail2[$UNIQ]['start'] 			= date('H:i', strtotime($valx2['start']));
					$ArrDetail2[$UNIQ]['finish'] 			= date('H:i', strtotime($valx2['finish']));
					$ArrDetail2[$UNIQ]['total'] 			= $valx2['total'];
					$ArrDetail2[$UNIQ]['id_proses'] 		  = $valx2['id_proses'];
					$ArrDetail2[$UNIQ]['keterangan'] 		  = $valx2['keterangan_'];
					
					$ArrSPKPro[] = $valYx['id_spkproduksi'];
					
					$ArrSPKPro1[] = $valYx['id_spkproduksi'];
				}
			}
		}
		
		$ubahstatus = [
			'input2'		=> '1'
		];
		
		$ArrSPKPro2 = array_unique($ArrSPKPro);
		
		$ArrSPKPro3 = array_unique($ArrSPKPro1);
		
		// print_r($ArrSPKPro2);
		// print_r($ArrDetail2);
		// exit;
		$this->db->trans_start();
			if(!empty($ArrSPKPro2)){
				$this->db->where_in('id_spk_aktual', $ArrSPKPro2);
				$this->db->delete('dt_spk_produksi_lph');
				
				$this->db->where_in('id_spkproduksi', $ArrSPKPro2);
				$this->db->update("tr_spk_produksi_sheet", $ubahstatus);
				
				// $this->db->where_in('id_spkproduksi', $ArrSPKPro3);
				// $this->db->update("dt_tr_spk_produksi", $ubahstatus);
			}
			
			if(!empty($ArrDetail2)){
				$this->db->insert_batch('dt_spk_produksi_lph', $ArrDetail2);
			}
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Save gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Save berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		echo json_encode($Arr_Data);
	}
	
	public function addHeaderproses($id=null,$view=null){
		$this->auth->restrict($this->viewPermission);
		$id   = $this->uri->segment(3);
        $session = $this->session->userdata('app_session');
		$this->template->page_icon('fa fa-pencil');
		$header = $this->Inventory_4_model->CariheaderSPK($id);
		
		$detail = $this->Inventory_4_model->CaridetailSPK($id);
		
		// print_r($detail);
		// exit;
		
		$data = [
			'detail' => $detail,
			'header' => $header
		];
		
	    $this->template->set('results', $data);
        $this->template->title('SPK Produksi');
        $this->template->render('AddHeader_proses');

    }
	
	public function printSPKProduksi(){
		ob_clean();
		ob_start();
        $this->auth->restrict($this->managePermission);

		$id = $this->uri->segment(3);
		$data['header']  = $this->db->get_where('tr_spk_produksi_sheet',array('id_spkproduksi'=>$id))->result();
		$data['detail'] = $this->db->get_where('dt_spk_produksi_sheet',array('id_spkproduksi'=>$id))->result_array();

		$data['material'] = function($id_material){
			$data_mat = $this->db->get_where('ms_inventory_category3',array('id_category3'=>$id_material))->result();
			$data_mat2 = $this->db->get_where('ms_inventory_category2',array('id_category2'=>$data_mat[0]->id_category2))->result();
			//return strtoupper(strtolower($data_mat2[0]->nama.'--'.$data_mat[0]->nama.'-'.$data_mat[0]->hardness));
			
			return strtoupper(strtolower($data_mat[0]->nama));
		};
		$data['stock'] = function($id_stock){
			$data_mat = $this->db->get_where('stock_material',array('id_stock'=>$id_stock))->result();
			return strtoupper(strtolower($data_mat[0]->lotno));
		};

		$data['no_suratx'] = function($id_material){
			$surat_val = $id_material;
			if($id_material <> 'nonspk'){
				$nosurat = $this->db->query("SELECT b.no_surat as no_surat FROM dt_spkmarketing as a INNER JOIN tr_spk_marketing as b ON a.id_spkmarketing = b.id_spkmarketing WHERE a.id_dt_spkmarketing='$id_material' ")->result();
				$surat_val = $nosurat[0]->no_surat;
			}

			return strtoupper($surat_val);
		};

										

		$this->template->title('Data');
		$this->load->view('printSPKProduksi',$data);

		$html = ob_get_contents();
		require_once('./assets/html2pdf/html2pdf/html2pdf.class.php');
		$html2pdf = new HTML2PDF('P','A4','en',true,'UTF-8',array(0, 0, 0, 0));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->WriteHTML($html);
		ob_end_clean();
		$html2pdf->Output('Spk Produksi.pdf', 'I');
	}
	
}