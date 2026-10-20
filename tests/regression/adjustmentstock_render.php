<?php
if (PHP_SAPI !== 'cli') exit;
function base_url($path) { return '/'.$path; }
function site_url($path) { return '/'.$path; }
function has_permission($permission) { return $GLOBALS['adjust_can_add'] && $permission==='Adjustment_Stock.Add'; }
class AdjustmentRenderConfig { public function item($key) { return $key==='csrf_protection'; } }
class AdjustmentRenderSecurity {
    public function get_csrf_token_name() { return 'csrf_test'; }
    public function get_csrf_hash() { return 'token'; }
}
class AdjustmentRenderUri { public function segment($n) { return 'adjustmentstock'; } }
class AdjustmentRenderView {
    public $config,$security,$uri;
    public function render($file) {
        $this->config=new AdjustmentRenderConfig(); $this->security=new AdjustmentRenderSecurity(); $this->uri=new AdjustmentRenderUri();
        $material=array('id_category3'=>'MAT','nama'=>'Material <script>alert(1)</script>','nm_bentuk'=>'ROLL');
        ob_start(); include dirname(dirname(__DIR__)).'/application/modules/adjustmentstock/views/'.$file.'.php'; return ob_get_clean();
    }
}
$view=new AdjustmentRenderView();
$form=$view->render('Adjust');
if (strpos($form,'<script>alert(1)</script>')!==false || strpos($form,'name="csrf_test" value="token"')===false) throw new RuntimeException('Escaping/CSRF missing');
if (!preg_match('/id="adj-kg"[^>]*step="0.01"/',$form) || !preg_match('/id="adj-qty"[^>]*step="1"/',$form)) throw new RuntimeException('Amount constraints missing');
if (strpos($form,'id="adj-kg" name="total_jumlah_berat" readonly')!==false) throw new RuntimeException('Weight must be editable');
$scripts=array(); $pages=array();
foreach (array(true,false) as $allow) {
    $GLOBALS['adjust_can_add']=$allow;
    $html=$view->render('index'); $pages[$allow ? 'add' : 'view']=$html;
    if ((strpos($html,'<th class="adjustmentstock-actions-column">Tindakan</th>')!==false)!==$allow) throw new RuntimeException('Action header must follow Add permission');
    if ((strpos($html,"{ data: 'action' }")!==false)!==$allow) throw new RuntimeException('Action column must follow Add permission');
    if (strpos($html,'Qty Adjustment')===false || strpos($html,'Total Berat (kg)')===false) throw new RuntimeException('Independent history columns missing');
    preg_match_all('/<script[^>]*>(.*?)<\/script>/s',$html,$matches);
    foreach ($matches[1] as $script) if (trim($script)!=='') $scripts[]=$script;
}
preg_match_all('/<script[^>]*>(.*?)<\/script>/s',$form,$matches);
foreach ($matches[1] as $script) if (trim($script)!=='') $scripts[]=$script;
$result=array('status'=>'PASS PHP view render: escaping, CSRF, input constraints, Add permission and history columns','scripts'=>$scripts);
if (isset($argv[1]) && $argv[1]==='--fixtures') { $result['pages']=$pages; $result['form']=$form; }
echo json_encode($result);
