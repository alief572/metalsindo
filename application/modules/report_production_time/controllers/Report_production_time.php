<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_production_time extends Admin_Controller
{
    protected $viewPermission='Report_Production_Time.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_production_time/Report_production_time_model','report_model');
        $this->template->title('Report Production Time');
        $this->template->page_icon('fa fa-clock-o');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->set(array('headers'=>Report_production_time_model::headers()));
        $this->template->render('index');
    }

    private function input($method)
    {
        $input=$this->input->$method(null,false);
        return is_array($input)?$input:array();
    }

    public function get_data()
    {
        $this->auth->restrict($this->viewPermission);
        $input=$this->input('post');
        try {
            $filters=$this->report_model->normalize_filters($input);
            $start=isset($input['start']) && is_scalar($input['start'])?max(0,(int)$input['start']):0;
            $length=isset($input['length']) && is_scalar($input['length'])?max(1,min(100,(int)$input['length'])):25;
            $all=$this->report_model->normalize_filters(array());
            $total=$this->report_model->count_groups($all);
            $result=array('recordsTotal'=>$total,'recordsFiltered'=>$filters===$all?$total:$this->report_model->count_groups($filters),
                'data'=>$this->report_model->get_groups($filters,$start,$length));
        } catch (InvalidArgumentException $e) {
            $result=array('recordsTotal'=>0,'recordsFiltered'=>0,'data'=>array(),'error'=>$e->getMessage());
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function export_excel()
    {
        $this->auth->restrict($this->viewPermission);
        try { $filters=$this->report_model->normalize_filters($this->input('get')); }
        catch (InvalidArgumentException $e) { show_error(htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8'),400); return; }
        set_time_limit(0);
        $this->load->library('PHPExcel');
        require_once dirname(__DIR__).'/libraries/Report_production_time_excel.php';
        $exporter=new Report_production_time_excel();
        $book=$exporter->build($this->report_model->get_groups($filters));
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Report_Production_Time_'.date('Ymd_His').'.xlsx"');
        header('Cache-Control: max-age=0');
        $exporter->save($book,'php://output');
        $book->disconnectWorksheets();
    }
}
