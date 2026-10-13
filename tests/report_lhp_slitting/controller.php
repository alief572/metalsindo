<?php
require __DIR__.'/regression.php';
class Admin_Controller {}
class LhpInput {
    public $values = array();
    public function post($key=null,$xss=false) { return $this->values; }
    public function get($key=null,$xss=false) { return $this->values; }
}
class LhpAuth {
    public $allowed=true;
    public $permission;
    public function restrict($permission) {
        $this->permission=$permission;
        if (!$this->allowed) throw new RuntimeException('Access denied');
    }
}
class LhpOutput {
    public $json;
    public function set_content_type($type) { return $this; }
    public function set_output($json) { $this->json=$json; return $this; }
}
class LhpTemplate {
    public $data;
    public $view;
    public function set($data) { $this->data=$data; }
    public function render($view) { $this->view=$view; }
}
require dirname(dirname(__DIR__)).'/application/modules/report_lhp_slitting/controllers/Report_lhp_slitting.php';
$reflection=new ReflectionClass('Report_lhp_slitting');
$controller=$reflection->newInstanceWithoutConstructor();
$controller->auth=new LhpAuth(); $controller->input=new LhpInput(); $controller->output=new LhpOutput();
$controller->template=new LhpTemplate(); $controller->report_model=$model;
$controller->index();
check(count($controller->template->data['headers'])===19 && $controller->template->view==='index','Page headers');
$controller->input->values=array('draw'=>7,'length'=>2);
$controller->get_data(); $json=json_decode($controller->output->json,true);
check($json['draw']===7 && $json['recordsTotal']===4 && count($json['data'])===2,'DataTables response');
check($json['data'][0][2]==='&lt;script&gt;' && $json['data'][0][8]==='—','HTML escaping and NULL display');
check($json['data'][1][3]==='07-05-2026' && $json['data'][1][8]==='0,00','Date and zero formatting');
$controller->input->values=array('start_date'=>'2026-05-06','end_date'=>'2026-05-06');
$controller->get_data(); $json=json_decode($controller->output->json,true);
check($json['recordsFiltered']===2 && $json['data'][0][18]==='-10,00','Filtered count and negative display');
$controller->input->values=array('start_date'=>'invalid');
$controller->get_data(); $json=json_decode($controller->output->json,true);
check(isset($json['error']) && $json['data']===array(),'Invalid filters response');
$controller->auth->allowed=false;
foreach (array('index','get_data') as $method) {
    $denied=false; try { $controller->$method(); } catch (RuntimeException $e) { $denied=$e->getMessage()==='Access denied'; }
    check($denied && $controller->auth->permission==='Report_LHP_Slitting.View','Permission '.$method);
}
echo "PASS controller: page, JSON, formatting, invalid filters and access guards\n";
