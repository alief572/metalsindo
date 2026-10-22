<?php
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH',__DIR__);
class Admin_Controller {}
function log_message($level,$message) {}
require dirname(dirname(__DIR__)).'/application/modules/adjustmentstock/controllers/Adjustmentstock.php';
function controller_check($ok,$label) { if (!$ok) throw new RuntimeException($label); }
class AdjustmentAuthStub {
    public $allowed=true,$permission;
    public function restrict($permission) { $this->permission=$permission; if (!$this->allowed) throw new RuntimeException('denied'); }
    public function user_id() { return 'USER'; }
}
class AdjustmentInputStub {
    public $verb='post',$values=array();
    public function method() { return $this->verb; }
    public function post() { return $this->values; }
    public function get($key) { return isset($this->values[$key]) ? $this->values[$key] : null; }
}
class AdjustmentOutputStub {
    public $status=200,$body;
    public function set_status_header($status) { $this->status=$status; return $this; }
    public function set_content_type($type) { return $this; }
    public function set_output($body) { $this->body=$body; return $this; }
}
class AdjustmentConfigStub { public function item($key) { return $key==='csrf_protection'; } }
class AdjustmentSecurityStub {
    public function get_csrf_token_name() { return 'csrf_test'; }
    public function get_csrf_hash() { return 'rotated'; }
}
class AdjustmentModelStub {
    public $calls=0,$error=null;
    public function save($input,$user) { $this->calls++; controller_check($user==='USER','Authenticated user forwarded'); if ($this->error) throw $this->error; return 'A261000001'; }
    public function options($id) { $this->calls++; return array('lots'=>array(),'warehouses'=>array(),'material'=>array('id_category3'=>$id)); }
}
$reflection=new ReflectionClass('Adjustmentstock');
$controller=$reflection->newInstanceWithoutConstructor();
$controller->auth=new AdjustmentAuthStub(); $controller->input=new AdjustmentInputStub();
$controller->output=new AdjustmentOutputStub(); $controller->config=new AdjustmentConfigStub();
$controller->security=new AdjustmentSecurityStub(); $controller->Adjustment_balance_model=new AdjustmentModelStub();
$controller->input->verb='get'; $controller->SavePlus();
controller_check($controller->output->status===405 && $controller->Adjustment_balance_model->calls===0,'GET save cannot mutate');
$controller->input->verb='post'; $controller->SavePlus(); $response=json_decode($controller->output->body,true);
controller_check($response['status']===1 && $response['code']==='A261000001' && $response['csrf']['hash']==='rotated','Compatible success JSON and rotated CSRF');
controller_check($controller->auth->permission==='Adjustment.Add','Save Add permission');
$controller->Adjustment_balance_model->error=new InvalidArgumentException('Saldo tidak cukup'); $controller->SavePlus();
$response=json_decode($controller->output->body,true);
controller_check($response['status']===0 && $response['pesan']==='Saldo tidak cukup' && isset($response['csrf']),'Validation JSON permits retry with fresh CSRF');
$controller->Adjustment_balance_model->error=new RuntimeException('internal database details'); $controller->SavePlus();
controller_check(strpos($controller->output->body,'internal database details')===false,'Internal errors must not leak');
$controller->input->values=array('id_material'=>'MAT'); $controller->form_options(); $response=json_decode($controller->output->body,true);
controller_check($response['status']===1 && $response['material']['id_category3']==='MAT','Options JSON');
$controller->input->values=array('id_material'=>array('MAT')); $before=$controller->Adjustment_balance_model->calls;
$controller->form_options(); $response=json_decode($controller->output->body,true);
controller_check($response['status']===0 && $controller->Adjustment_balance_model->calls===$before,'Options array injection rejected');
$controller->auth->allowed=false;
foreach (array('SavePlus'=>'Adjustment.Add','form_options'=>'Adjustment.Add','history_datatable'=>'Adjustment.View','adjust_datatable'=>'Adjustment.View') as $method=>$permission) {
    try { $controller->$method(); throw new LogicException('Missing permission guard'); }
    catch (RuntimeException $e) { controller_check($e->getMessage()==='denied' && $controller->auth->permission===$permission,'Permission required before model/DB access'); }
}
try { $controller->AdjustNow('MAT'); throw new LogicException('Missing modal guard'); }
catch (RuntimeException $e) { controller_check($e->getMessage()==='denied' && $controller->auth->permission==='Adjustment.Add','Modal Add permission'); }
echo "PASS adjustment controller: permission guards, POST-only save, JSON, validation, error handling and CSRF renewal\n";
