<?php
require __DIR__.'/regression.php';
$source=file_get_contents(dirname(dirname(__DIR__)).'/application/modules/report_lhp_slitting/models/Report_lhp_slitting_model.php');
$mutated=str_replace('class Report_lhp_slitting_model','class Mutant_lhp_slitting_model',$source);
$mutated=str_replace('AND h.id_dt_spkaktualdetail = d.id_dt_spkaktualdetail','OR h.id_dt_spkaktualdetail = d.id_dt_spkaktualdetail',$mutated,$replacements);
check($replacements===1,'Exactly one join condition mutated');
eval(substr($mutated,5));
$mutant=new Mutant_lhp_slitting_model($db);
$caught=false;
try { check($mutant->count_rows($all)===4,'One row per matching detail'); }
catch (RuntimeException $e) { $caught=true; }
check($caught,'Regression must detect weakened LHP join');
echo "PASS mutation: weakening matching-LHP join triggers the existing row-count assertion; source files unchanged\n";
