<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Trasaction Purchase Order
 */

$status=array();
$tipe=array();
class Purchase_order extends Admin_Controller
{
    //Permission
    protected $viewPermission 	= 'Purchase_Order.View';
    protected $addPermission  	= 'Purchase_Order.Add';
    protected $managePermission = 'Purchase_Order.Manage';
    protected $deletePermission = 'Purchase_Order.Delete';
    public function __construct() {
        parent::__construct();
        $this->load->model(array('Purchase_order/Purchase_order_model','All/All_model'));
        $this->template->title('Purchase Order');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
		$this->status=array("0"=>"Baru","1"=>"Proses","2"=>"Selesai");
		$this->tipe=array("CASH"=>"CASH","PO"=>"PO");
    }

	// list
    public function index() {
		$data = $this->Purchase_order_model->GetListPurchaseOrder();
        $this->template->set('results', $data);
        $this->template->set('status', $this->status);
		$this->template->page_icon('fa fa-list');
		$this->template->title('Purchase Order');
        $this->template->render('purchase_order_list');
    }

	// create
	public function create(){
		$inventory_type=$this->All_model->GetInventoryTypeCombo();
		$supplier=$this->All_model->GetSupplierCombo();
		$pr_list=$this->Purchase_order_model->GetPrCombo(array('status'=>1));
        $this->template->set('pr_list', $pr_list);
        $this->template->set('supplier', $supplier);
        $this->template->set('tipe', $this->tipe);
        $this->template->set('status', $this->status);
        $this->template->render('purchase_order_form');
	}

	// edit
	public function edit($id){
		$data = $this->Purchase_order_model->GetDataPurchaseOrder($id);
		$supplier=$this->All_model->GetSupplierCombo();
		$pr_list=$this->Purchase_order_model->GetPrCombo(array('pr_no'=>$data->pr_no));
		$data_material	= $this->Purchase_order_model->GetDataPurchaseOrderDetail($data->po_no);
        $this->template->set('pr_list', $pr_list);
        $this->template->set('tipe', $this->tipe);
        $this->template->set('supplier', $supplier);
        $this->template->set('data_material', $data_material);
        $this->template->set('status', $this->status);
        $this->template->set('data', $data);
		$this->template->page_icon('fa fa-list');
        $this->template->render('purchase_order_form');
	}
	// view
	public function view($id){
		$data = $this->Purchase_order_model->GetDataPurchaseOrder($id);
		$supplier=$this->All_model->GetSupplierCombo();
		$pr_list=$this->Purchase_order_model->GetPrCombo(array('pr_no'=>$data->pr_no));
		$data_material	= $this->Purchase_order_model->GetDataPurchaseOrderDetail($data->po_no);
        $this->template->set('pr_list', $pr_list);
        $this->template->set('tipe', $this->tipe);
        $this->template->set('supplier', $supplier);
        $this->template->set('data_material', $data_material);
        $this->template->set('status', $this->status);
        $this->template->set('data', $data);
        $this->template->set('views', 'view');
		$this->template->page_icon('fa fa-list');
        $this->template->render('purchase_order_form');
	}
	// approve
	public function approve($id=''){
		$result=false;
        if($id!="") {
			$this->db->trans_begin();
			$data = array(
						array(
							'id'=>$id,
							'status'=>1,
						)
					);
			$result = $this->Purchase_order_model->update_batch($data,'id');

		//start cek apakah sudah komplit purchase request (order sudah semua)
			$data = $this->Purchase_order_model->GetDataPurchaseOrder($id);
			if($data!==false){
				$data_material=$this->Purchase_order_model->GetListPrMaterial($data->pr_no);
				if($data_material===false) {
					$this->All_model->dataUpdate("tr_purchase_request",array('status'=>2),array('pr_no'=>$data->pr_no));
				}
			}
		// end cek
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
			} else {
				$this->db->trans_commit();
			}
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
		$po_date  		= $this->input->post("po_date");
        $id_supplier	= $this->input->post("id_supplier");
        $po_info		= $this->input->post("po_info");
        $pr_no			= $this->input->post("pr_no");
        $id_payment		= $this->input->post("id_payment");
        $pic			= $this->input->post("pic");
        $detail_id		= $this->input->post("detail_id");
		$this->db->trans_begin();
        if($id!="") {
			$po_no			= $this->input->post("po_no");
			$data = array(
						array(
							'id'=>$id,
							'pr_no'=>$pr_no,
							'po_date'=>$po_date,
							'id_supplier'=>$id_supplier,
							'po_info'=>$po_info,
							'id_payment'=>$id_payment,
							'pic'=>$pic,
							'modified_by'=> $this->auth->user_id(),
							'modified_on'=>date("Y-m-d h:i:s")
						)
					);
			$result = $this->Purchase_order_model->update_batch($data,'id');

			$this->All_model->dataUpdate("tr_purchase_request_detail",array('material_order'=>0),array(" id in (select id_pr from tr_purchase_order_detail where doc_no='".$po_no."')"=>null));
			$this->All_model->dataDelete('tr_purchase_order_detail',array('doc_no'=>$po_no));
			if(!empty($detail_id)){
				foreach ($detail_id as $keys){
					$material_id		= $this->input->post("material_id_".$keys);
					$material_qty		= $this->input->post("material_qty_".$keys);
					$material_unit		= $this->input->post("material_unit_".$keys);
					$material_price		= $this->input->post("material_price_".$keys);
					$material_pr		= $this->input->post("material_pr_".$keys);
					$material_request	= $this->input->post("material_request_".$keys);
					$id_pr		= $this->input->post("id_pr_detail_".$keys);
					$data_detail =  array(
								'doc_no'=>$po_no,
								'id_pr'=>$id_pr,
								'material_id'=>$material_id,
								'material_qty'=>$material_qty,
								'material_unit'=>$material_unit,
								'material_price'=>$material_price,
								'material_pr'=>$material_pr,
								'material_request'=>$material_request,
								'created_by'=> $this->auth->user_id(),
								'created_on'=>date("Y-m-d h:i:s")
							);
					$this->All_model->dataSave('tr_purchase_order_detail',$data_detail);
					$this->All_model->dataUpdate("tr_purchase_request_detail",array('material_order'=>$material_qty),array('id'=>$id_pr));
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
			$doc_no=$this->All_model->GetAutoGenerate('format_po');
            $data =  array(
						'po_no'=>$doc_no,
						'pr_no'=>$pr_no,
						'po_date'=>$po_date,
						'id_supplier'=>$id_supplier,
						'po_info'=>$po_info,
						'id_payment'=>$id_payment,
						'status'=>0,
						'pic'=>$pic,
					);
            $id = $this->Purchase_order_model->insert($data);
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
		$data = $this->Purchase_order_model->GetDataPurchaseOrder($id);
		$this->db->trans_begin();
        $this->All_model->dataDelete('tr_purchase_order_detail',array('doc_no'=>$data->po_no));
        $result=$this->Purchase_order_model->delete($id);
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
        $param = array( 'delete' => $result );
        echo json_encode($param);
	}

	public function add_material($id){
		$data = $this->Purchase_order_model->GetDataPurchaseOrder($id);
		if($data!==false){
			$data_material=$this->Purchase_order_model->GetListPrMaterial($data->pr_no);
			if($data_material!==false) {
				$idx=5000;
				foreach($data_material as $record){
					$idx++;?>
					<tr>
						<td><input type="checkbox" name="detail_id[]" id="raw_id_<?=$idx?>" value="<?=$idx;?>" checked>
						<input type="hidden" name="material_id_<?=$idx;?>" id="material_id_<?=$idx;?>" value="<?=$record->material_id;?>">
						<input type="hidden" name="id_pr_detail_<?=$idx;?>" id="id_pr_detail_<?=$idx;?>" value="<?=$record->id;?>">
						<td><?= $record->nama ?></td>
						<td><input type="text" class="form-control divide" name="material_request_<?=$idx;?>" id="material_request_<?=$idx;?>" value="<?=$record->material_qty;?>" readonly tabindex="-1"></td>
						<td><input type="text" class="form-control divide" name="material_qty_<?=$idx;?>" id="material_qty_<?=$idx;?>" value="<?=$record->material_qty;?>" onchange="cektotal('<?=$idx;?>')"></td>
						<td><input type="text" class="form-control" readonly tabindex="-1" name="material_unit_<?=$idx;?>" id="material_unit_<?=$idx;?>" value="<?=$record->material_unit;?>"></td>
						<td><input type="text" class="form-control divide" readonly tabindex="-1" name="material_pr_<?=$idx;?>" id="material_pr_<?=$idx;?>" value="<?=$record->element_cost;?>"></td>
						<td><input type="text" class="form-control divide" name="material_price_<?=$idx;?>" id="material_price_<?=$idx;?>" value="0" onchange="cektotal('<?=$idx;?>')"></td>
						<td><input type="text" class="form-control divide" name="material_total_<?=$idx;?>" id="material_total_<?=$idx;?>" value="0" readonly  tabindex="-1"></td>
					</tr>
					<?php
				}
			}
		}else{
			die();
		}
	}

}

