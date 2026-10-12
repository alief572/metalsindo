<?php
require __DIR__.'/regression.php';
class Admin_Controller {}
class IncomingTestInput {
    public $values=array();
    public function post($key=null,$xss=false) { return $this->values; }
    public function get($key=null,$xss=false) { return $this->values; }
}
class IncomingTestAuth {
    public $allowed=true; public $permission;
    public function restrict($permission) {
        $this->permission=$permission;
        if(!$this->allowed) throw new RuntimeException('Access denied');
    }
}
class IncomingTestOutput {
    public $json;
    public function set_content_type($type) { return $this; }
    public function set_output($json) { $this->json=$json; return $this; }
}
class IncomingTestTemplate {
    public $data; public $view;
    public function set($data) { $this->data=$data; }
    public function render($view) { $this->view=$view; }
}
require APPPATH.'modules/report_incoming/controllers/Report_incoming.php';
$controller=(new ReflectionClass('Report_incoming'))->newInstanceWithoutConstructor();
$controller->auth=new IncomingTestAuth(); $controller->input=new IncomingTestInput();
$controller->output=new IncomingTestOutput(); $controller->template=new IncomingTestTemplate(); $controller->report_model=$model;
$controller->index();
incoming_check(count($controller->template->data['headers'])===19 && $controller->template->view==='index','Page contract');
$controller->input->values=array('draw'=>7,'start'=>1,'length'=>2); $controller->get_data();
$json=json_decode($controller->output->json,true);
incoming_check($json['draw']===7 && $json['recordsTotal']===7 && count($json['data'])===2 && $json['data'][0][0]===2,'Pagination numbering');
incoming_check($json['data'][0][5]==='&lt;script&gt;' && $json['data'][0][7]==='','XSS escaped and NULL blank');
$controller->input->values=array('search'=>'CPD0661B'); $controller->get_data(); $json=json_decode($controller->output->json,true);
incoming_check($json['recordsFiltered']===1 && $json['data'][0][8]==='23-09-2026' && $json['data'][0][11]==='-1,00','Sample rendering');
$controller->input->values=array('start_date'=>'bad','draw'=>array(2),'length'=>array(1)); $controller->get_data();
$json=json_decode($controller->output->json,true);
incoming_check(isset($json['error']) && $json['data']===array() && $json['draw']===0,'Invalid filters safe response');
$controller->auth->allowed=false;
foreach(array('index','get_data','export_excel') as $method) {
    $denied=false; try { $controller->$method(); } catch(RuntimeException $e) { $denied=$e->getMessage()==='Access denied'; }
    incoming_check($denied && $controller->auth->permission==='Report_Incoming.View','Access guard '.$method);
}
function base_url($path) { return '/metalsindo_2/'.$path; }
$headers=Report_incoming_model::headers(); ob_start(); require APPPATH.'modules/report_incoming/views/index.php'; $html=ob_get_clean();
incoming_check(substr_count($html,'scope="col"')===19 && strpos($html,'Belum terverifikasi')!==false,'Render 19 headers and note');
echo "PASS controller: permissions (3 endpoints), JSON, numbering, NULL/date/negative/XSS, invalid filters and view rendering\n";
