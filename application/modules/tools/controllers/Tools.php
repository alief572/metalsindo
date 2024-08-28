 <?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Tools extends Admin_Controller {
    //Permission
    protected $viewPermission = "Tools.View";
    protected $addPermission = "Tools.Add";
    protected $managePermission = "Tools.Manage";
    protected $deletePermission = "Tools.Delete";
    public function __construct() {
        parent::__construct();
        $this->load->library(array('Mpdf', 'upload', 'Image_lib'));
        $this->load->model('Tools/Tools_model');
        $this->template->title('Manage Data Plan Bayar');
        $this->template->page_icon('fa fa-building-o');
        date_default_timezone_set('Asia/Bangkok');
    }
 
 
	public function back_planning() {
        $this->auth->restrict($this->viewPermission);
        $this->template->page_icon('fa fa-list');
        $data = '0';
        $this->template->set('results', $data);
        $this->template->title('Indeks Of Detail Spk');
        $this->template->render('index_dtspkmarketing.php'); 
    }
	
	
	public function getDataJSONdtspkmarketing() {
        $requestData = $_REQUEST;
		$statusdata = array();
		//$statusdata['status'] = $requestData['status'];
		//$statusdata['status_payment'] = $requestData['status_payment'];
        $fetch = $this->queryDataJSONVoucher($statusdata, $requestData['search']['value'], $requestData['order'][0]['column'], $requestData['order'][0]['dir'], $requestData['start'], $requestData['length']);
        $totalData = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query = $fetch['query'];
        $data = array();
        $urut1 = 1;
        $urut2 = 0;
        foreach ($query->result_array() as $row) {
            $total_data = $totalData;
            $start_dari = $requestData['start'];
            $asc_desc = $requestData['order'][0]['dir'];
            if ($asc_desc == 'asc') {
                $nomor = $urut1 + $start_dari;
            }
            if ($asc_desc == 'desc') {
                $nomor = ($total_data - $start_dari) - $urut2;
            }
           
           
            $nestedData = array();
            $detail = "";
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='left'>" . $row['no_surat']. "</div>";
            
            /* if ($this->auth->restrict($this->managePermission)){
				if($row['status_jurnal']!=1){
					$nestedData[] = "
					  <a class='create_bayar btn btn-xs btn-primary' href='javascript:void(0)' title='Input Actual' data-id='" . $row['id'] . "' data-id_quotation='" . $row['no_invoice'] . "'><span class='fa fa-plus'></span> Input Actual</a>
					  ";
				}
				else{
					$nestedData[] = "
					  <a class='create_bayar btn btn-xs btn-success'><span></span>Selesai</a>
					  ";
				}
					
				// }else{
					// $nestedData[] = "";
				// }
            //}*/
            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }
        $json_data = array("draw" => intval($requestData['draw']), "recordsTotal" => intval($totalData), "recordsFiltered" => intval($totalFiltered), "data" => $data);
        echo json_encode($json_data);
    } 
	
    public function queryDataJSONVoucher($type, $like_value = NULL, $column_order = '', $column_dir = NULL, $limit_start = NULL, $limit_length = NULL) {
//       
          
            $sql = "SELECT a.*, c.name_customer as name_customer, b.no_surat as no_surat, c.id_customer  
			FROM dt_spkmarketing a			
			inner join tr_spk_marketing b ON b.id_spkmarketing=a.id_spkmarketing
			inner join master_customers c ON c.id_customer=b.id_customer
			
			WHERE
			
			a.deal='1' and b.status_approve='1' and a.status_lanjutan!='3'
			
			AND			
						
			(
			b.no_surat LIKE '%" . $this->db->escape_like_str($like_value) . "%'
			OR
			c.name_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
			
			) 
			";
          
		   
        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();
        $columns_order_by = array(0 => 'id', 1 => 'no_surat', 2 => 'name_customer'); 
        if($column_order!='') $sql.= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
        $sql.= " LIMIT " . $limit_start . " ," . $limit_length . " ";
        $data['query'] = $this->db->query($sql);
        return $data;
    }
	
	
}