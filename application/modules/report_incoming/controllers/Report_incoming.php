<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_incoming extends Admin_Controller
{
    protected $viewPermission='Report_Incoming.View';

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model('report_incoming/Report_incoming_model','report_model');
        $this->template->title('Report Incoming');
        $this->template->page_icon('fa fa-file-text-o');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->set(array('headers'=>Report_incoming_model::headers()));
        $this->template->render('index');
    }

    private function ordering($input)
    {
        $order=isset($input['order'][0]) && is_array($input['order'][0]) ? $input['order'][0] : array();
        $column=isset($order['column']) && is_scalar($order['column']) ? (int)$order['column'] : 8;
        $direction=isset($order['dir']) && $order['dir']==='asc' ? 'asc' : 'desc';
        return array($column,$direction);
    }

    public function get_data()
    {
        $this->auth->restrict($this->viewPermission);
        $input=$this->input->post(null,false); if(!is_array($input)) $input=array();
        $draw=isset($input['draw']) && is_scalar($input['draw']) ? max(0,(int)$input['draw']) : 0;
        try {
            $filters=$this->report_model->normalize_filters($input);
            list($column,$direction)=$this->ordering($input);
            $start=isset($input['start']) && is_scalar($input['start']) ? max(0,(int)$input['start']) : 0;
            $length=isset($input['length']) && is_scalar($input['length']) ? (int)$input['length'] : 25;
            $data=array(); $number=$start;
            foreach($this->report_model->get_rows($filters,$start,$length,$column,$direction) as $row) {
                $values=$this->report_model->format_row($row,++$number);
                foreach($values as $i=>&$value) {
                    if($value===null) $value='';
                    elseif(is_float($value)) $value=number_format($value,2,',','.');
                    elseif(in_array($i,array(3,8),true) && Report_incoming_model::valid_date($value))
                        $value=substr($value,8,2).'-'.substr($value,5,2).'-'.substr($value,0,4);
                    elseif(!is_int($value)) $value=htmlspecialchars($value,ENT_QUOTES,'UTF-8');
                }
                unset($value); $data[]=$values;
            }
            $all=$this->report_model->normalize_filters(array());
            $total=$this->report_model->count_rows($all);
            $filtered=$filters===$all ? $total : $this->report_model->count_rows($filters);
            $result=array('draw'=>$draw,'recordsTotal'=>$total,'recordsFiltered'=>$filtered,'data'=>$data);
        } catch(InvalidArgumentException $e) {
            $result=array('draw'=>$draw,'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>array(),'error'=>$e->getMessage());
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function export_excel()
    {
        $this->auth->restrict($this->viewPermission);
        $input=$this->input->get(null,false); if(!is_array($input)) $input=array();
        try { $filters=$this->report_model->normalize_filters($input); }
        catch(InvalidArgumentException $e) { show_error(htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8'),400); return; }
        list($column,$direction)=$this->ordering($input);
        // Loading this as a CI library constructs an unused workbook before disk caching is configured.
        require_once APPPATH.'libraries/PHPExcel.php';
        require_once dirname(__DIR__).'/libraries/Report_incoming_excel.php';
        $exporter=new Report_incoming_excel();
        $book=$exporter->build($this->report_model,$filters,$column,$direction);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Report_Incoming_'.date('Ymd_His').'.xlsx"');
        header('Cache-Control: max-age=0');
        try {
            $writer=PHPExcel_IOFactory::createWriter($book,'Excel2007');
            $writer->setUseDiskCaching(true,sys_get_temp_dir());
            $writer->save('php://output');
        } finally { $book->disconnectWorksheets(); }
    }
}
