<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Trasaction Purchase Request
 */

$status=array();
class Purchase_request extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Purchase_Request.View';
    protected $addPermission  	= 'Purchase_Request.Add';
    protected $managePermission = 'Purchase_Request.Manage';
    protected $deletePermission = 'Purchase_Request.Delete';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('Purchase_request/Purchase_request_model','All/All_model'));
        $this->template->title('Purchase Request');
        $this->template->page_icon('fa fa-cubes');
        date_default_timezone_set('Asia/Bangkok');
		$this->status=array("0"=>"Baru","1"=>"Proses","2"=>"Selesai");
    }

	// list
    public function index() {
		$data = $this->Purchase_request_model->GetListPurchaseRequest();
		$inventory_type=$this->All_model->GetInventoryTypeCombo();
        $this->template->set('inventory_type', $inventory_type);
        $this->template->set('results', $data);
        $this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Purchase Request');
        $this->template->render('purchase_request_list');
    }

	// create
	public function create($id_type){
		$inventory_type = $this->All_model->GetInventoryTypeCombo(array('id_type'=>$id_type));
		$data_material = $this->All_model->GetMaterialStockList(array('id_type'=>$id_type));
        $this->template->set('inventory_type', $inventory_type);
        $this->template->set('data_material', $data_material);
        $this->template->set('status', $this->status);
        $this->template->render('purchase_request_form');
	}

	// edit
	public function edit($id){
		$data = $this->Purchase_request_model->GetDataPurchaseRequest($id);
		$data_material	= $this->Purchase_request_model->GetDataPurchaseRequestDetail($data->pr_no);
		$inventory_type=$this->All_model->GetInventoryTypeCombo(array('id_type'=>$data->id_type));
        $this->template->set('inventory_type', $inventory_type);
        $this->template->set('data_material', $data_material);
        $this->template->set('status', $this->status);
        $this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
        $this->template->render('purchase_request_form');
	}

		// view
	public function view($id){
		$data = $this->Purchase_request_model->GetDataPurchaseRequest($id);
		$data_material	= $this->Purchase_request_model->GetDataPurchaseRequestDetail($data->pr_no);
		$inventory_type=$this->All_model->GetInventoryTypeCombo(array('id_type'=>$data->id_type));
        $this->template->set('inventory_type', $inventory_type);
        $this->template->set('data_material', $data_material);
        $this->template->set('status', $this->status);
        $this->template->set('data', $data);
        $this->template->set('views', 'view');
		$this->template->page_icon('fa fa-list');
        $this->template->render('purchase_request_form');
	}
	// approve
	public function approve($id=''){
		$result=false;
        if($id!="") {
			$data = array(
						array(
							'id'=>$id,
							'status'=>1,
						)
					);
			$result = $this->Purchase_request_model->update_batch($data,'id');
			$keterangan     = "SUKSES, Approve data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
                'save' => $result, 'id'=>$id
                );
        echo json_encode($param);
	}
	// save
	public function save(){
        $id             = $this->input->post("id");
		$pr_date  		= $this->input->post("pr_date");
        $id_type	    = $this->input->post("id_type");
        $pr_info		= $this->input->post("pr_info");
        $detail_id		= $this->input->post("detail_id");
        $pr_no			= $this->input->post("pr_no");
		$this->db->trans_begin();
        if($id!="") {
			$data = array(
						array(
							'id'=>$id,
							'pr_date'=>$pr_date,
							'id_type'=>$id_type,
							'pr_info'=>$pr_info,
							'status'=>0,
						)
					);
			$result = $this->Purchase_request_model->update_batch($data,'id');
			$this->All_model->dataDelete('tr_purchase_request_detail',array('doc_no'=>$pr_no));
			if(!empty($detail_id)){
				foreach ($detail_id as $keys){
					$material_id		= $this->input->post("id_material_".$keys);
					$material_qty		= $this->input->post("material_qty_".$keys);
					$material_unit		= $this->input->post("material_unit_".$keys);
					$material_stock		= $this->input->post("material_stock_".$keys);
					$data_detail =  array(
								'doc_no'=>$pr_no,
								'material_id'=>$material_id,
								'material_qty'=>$material_qty,
								'material_unit'=>$material_unit,
								'material_stock'=>$material_stock,
								'created_by'=> $this->auth->user_id(),
								'created_on'=>date("Y-m-d h:i:s")
							);
					$this->All_model->dataSave('tr_purchase_request_detail',$data_detail);
				}
			}
			$keterangan     = "SUKSES, Edit data ".$id;
			$status         = 1; $nm_hak_akses   = $this->managePermission; $kode_universal = $id; $jumlah = 1;
			$sql            = $this->db->last_query();
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
			simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        } else {
			$pr_no=$this->All_model->GetAutoGenerate('format_pr');
            $data =  array(
						'pr_no'=>$pr_no,
						'pr_date'=>$pr_date,
						'id_type'=>$id_type,
						'pr_info'=>$pr_info,
						'status'=>0,
					);
            $id = $this->Purchase_request_model->insert($data);
			foreach ($detail_id as $keys){
				$material_id		= $this->input->post("id_material_".$keys);
				$material_qty		= $this->input->post("material_qty_".$keys);
				$material_unit		= $this->input->post("material_unit_".$keys);
				$material_stock		= $this->input->post("material_stock_".$keys);
				$data_detail =  array(
							'doc_no'=>$pr_no,
							'material_id'=>$material_id,
							'material_qty'=>$material_qty,
							'material_unit'=>$material_unit,
							'material_stock'=>$material_stock,
							'created_by'=> $this->auth->user_id(),
							'created_on'=>date("Y-m-d h:i:s")
						);
				$this->All_model->dataSave('tr_purchase_request_detail',$data_detail);
			}
            if(is_numeric($id)) {
                $keterangan     = "SUKSES, tambah data ".$id;
                $status         = 1;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result         = TRUE;
            } else {
                $keterangan     = "GAGAL, tambah data".$id;
                $status         = 0;
                $nm_hak_akses   = $this->addPermission;
                $kode_universal = 'NewData';
                $jumlah         = 1;
                $sql            = $this->db->last_query();
                $result = FALSE;
            }
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
            simpan_aktifitas($nm_hak_akses, $kode_universal, $keterangan, $jumlah, $sql, $status);
        }
        $param = array(
                'save' => $result, 'id'=>$id
                );
        echo json_encode($param);
	}

	// delete
	public function delete($id){
		$data = $this->Purchase_request_model->GetDataPurchaseRequest($id);
		$this->db->trans_begin();
        $this->All_model->dataDelete('tr_purchase_request_detail',array('doc_no'=>$data->pr_no));
        $result=$this->Purchase_request_model->delete($id);
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
        $param = array( 'delete' => $result );
        echo json_encode($param);
	}

	public function add_material($id){
		$data = $this->Purchase_request_model->GetDataPurchaseRequest($id);
		if($data!==false){
			$data_material	= $this->Purchase_request_model->GetDataPurchaseRequestDetail($data->pr_no);
			$data_material=$this->All_model->GetMaterialStockList(array("a.id_type"=>$data->id_type,"(c.id_material,c.nama_satuan) not in (select material_id,material_unit from tr_purchase_request_detail where doc_no='".$data->pr_no."')"=>null));
			if($data_material!==false) {
				$idx=0;
				foreach($data_material as $record){
					$idx++;
					$idd='new_'.$idx?>
					<tr>
						<td><input type="checkbox" name="detail_id[]" id="raw_id_<?=$idd?>" value="<?=$idd;?>" checked>
						<input type="hidden" name="id_material_<?=$idd;?>" id="id_material_<?=$idd;?>" value="<?=$record->id_material;?>">
						<td><?= $record->nama ?></td>
						<td><?= $record->spec3 ?></td>
						<td><?= $record->spec2 ?></td>
						<td><?= $record->spec13 ?></td>
						<td><input type="text" class="form-control divide" readonly tabindex="-1" name="material_stock_<?=$idd;?>" id="material_stock_<?=$idd;?>" value="<?=(($record->stock!='')?$record->stock:'0');?>"></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_unit_<?=$idd;?>" id="material_unit_<?=$idd;?>" value="<?=$record->satuan;?>"></td>
						<td><?php echo number_format($record->spec13-$record->stock) ?></td>
						<td><input type="text" class="form-control divide" name="material_qty_<?=$idd;?>" id="material_qty_<?=$idd;?>" value="<?=($record->spec13-$record->stock);?>"></td>
					</tr>
					<?php
				}
			}
		}else{
			die();
		}
	}
}
