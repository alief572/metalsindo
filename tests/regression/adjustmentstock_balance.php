<?php
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH', __DIR__);
class CI_Model { public $db; }
require dirname(dirname(__DIR__)).'/application/modules/adjustmentstock/models/Adjustment_balance_model.php';
function adj_assert($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function adj_reject($call, $label) {
    try { $call(); } catch (InvalidArgumentException $e) { return; }
    throw new RuntimeException('Expected rejection: '.$label);
}
$model = new Adjustment_balance_model();
$lot = array('qty'=>10,'totalweight'=>1000,'weight'=>100,'sisa_spk'=>90,'booking'=>0,'delivery_book'=>0);
$r = $model->balance($lot, 0, -25);
adj_assert($r['qty']==10 && $r['totalweight']==975 && $r['sisa_spk']==87.5, 'Weight only preserves qty and gross/operational difference');
$r = $model->balance($lot, -2, -100);
adj_assert($r['qty']==8 && $r['totalweight']==900 && $r['sisa_spk']==100, 'Independent qty/weight');
$r = $model->balance($lot, -5, 0);
adj_assert($r['qty']==5 && $r['totalweight']==1000 && $r['sisa_spk']==180, 'Qty only preserves total weight');
$empty = $lot; $empty['sisa_spk']=100;
$r = $model->balance($empty, -10, -1000);
adj_assert($r['qty']==0 && $r['aktif']==='N', 'Full depletion');
adj_reject(function() use ($model,$lot) { $model->balance($lot, -10, -100); }, 'qty zero with residual weight');
adj_reject(function() use ($model,$lot) { $model->balance($lot, 0, -901); }, 'operational stock below zero');
adj_reject(function() use ($model,$lot) { $model->balance($lot, -7, 0); }, 'unrepresentable unit precision');
$missing=$lot; $missing['sisa_spk']=null;
adj_reject(function() use ($model,$missing) { $model->balance($missing, 0, 25); }, 'NULL balance');
$booked=$lot; $booked['booking']=2;
$r=$model->balance($booked, 10, 1000);
adj_assert($r['booking']==1, 'PLUS preserves total booking kg');
foreach (array(array('total_qty'=>'0','total_jumlah_berat'=>'25.00'),array('total_qty'=>'2','total_jumlah_berat'=>'0')) as $input) $model->amounts($input);
foreach (array(array('total_qty'=>'0','total_jumlah_berat'=>'0'),array('total_qty'=>'1.5','total_jumlah_berat'=>'10'),array('total_qty'=>'-1','total_jumlah_berat'=>'10'),array('total_qty'=>array(1),'total_jumlah_berat'=>'10'),array('total_qty'=>'1','total_jumlah_berat'=>'1.001')) as $input) {
    adj_reject(function() use ($model,$input) { $model->amounts($input); }, 'invalid amount');
}
echo "PASS adjustment balance: independent quantities, operational/gross, booking, empty lot and validation\n";
