<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_spk_marketing extends Admin_Controller
{
    protected $viewPermission = 'Report_SPK_Marketing.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_spk_marketing/Report_spk_marketing_model', 'report_model');
        $this->template->title('Report SPK Marketing');
        $this->template->page_icon('fa fa-bar-chart');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->set(array('customers'=>$this->report_model->get_customers(), 'headers'=>Report_spk_marketing_model::headers()));
        $this->template->render('index');
    }

    private function ordering($input)
    {
        $order = isset($input['order'][0]) && is_array($input['order'][0]) ? $input['order'][0] : array();
        $column = isset($order['column']) && is_scalar($order['column']) ? (int) $order['column'] : 2;
        $direction = isset($order['dir']) && $order['dir'] === 'asc' ? 'asc' : 'desc';
        return array($column, $direction);
    }

    public function get_data()
    {
        $this->auth->restrict($this->viewPermission);
        $input = $this->input->post(null, false);
        if (!is_array($input)) $input = array();
        $draw = isset($input['draw']) && is_scalar($input['draw']) ? max(0, (int) $input['draw']) : 0;
        try {
            $filters = $this->report_model->normalize_filters($input);
            list($column, $direction) = $this->ordering($input);
            $start = isset($input['start']) && is_scalar($input['start']) ? max(0, (int) $input['start']) : 0;
            $length = isset($input['length']) && is_scalar($input['length']) ? (int) $input['length'] : 25;
            $rows = $this->report_model->get_rows($filters, $start, $length, $column, $direction);
            $data = array();
            foreach ($rows as $row) {
                $values = $this->report_model->format_row($row);
                foreach ($values as &$value) {
                    if (is_float($value)) $value = number_format($value, 2, ',', '.');
                    else $value = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
                }
                unset($value);
                $data[] = $values;
            }
            $unfiltered = $this->report_model->normalize_filters(array('status'=>'all'));
            $total = $this->report_model->count_rows($unfiltered);
            $filtered = $filters === $unfiltered ? $total : $this->report_model->count_rows($filters);
            $result = array('draw'=>$draw, 'recordsTotal'=>$total,
                'recordsFiltered'=>$filtered, 'data'=>$data);
        } catch (InvalidArgumentException $e) {
            $result = array('draw'=>$draw, 'recordsTotal'=>0, 'recordsFiltered'=>0, 'data'=>array(), 'error'=>$e->getMessage());
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function export_excel()
    {
        $this->auth->restrict($this->viewPermission);
        $input = $this->input->get(null, false);
        if (!is_array($input)) $input = array();
        try { $filters = $this->report_model->normalize_filters($input); }
        catch (InvalidArgumentException $e) { show_error(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'), 400); return; }
        list($column, $direction) = $this->ordering($input);
        $this->load->library('PHPExcel');
        require_once dirname(__DIR__).'/libraries/Report_spk_marketing_excel.php';
        $rows = array();
        foreach ($this->report_model->get_rows($filters, 0, null, $column, $direction) as $row) {
            $rows[] = $this->report_model->format_row($row);
        }
        $exporter = new Report_spk_marketing_excel();
        $book = $exporter->build(Report_spk_marketing_model::headers(), $rows);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Report_SPK_Marketing_'.date('Ymd_His').'.xlsx"');
        header('Cache-Control: max-age=0');
        PHPExcel_IOFactory::createWriter($book, 'Excel2007')->save('php://output');
        $book->disconnectWorksheets();
    }
}
