<?php
// SELECT-only smoke check. The fixture suite uses another connection; this connection sees real tables.
require __DIR__.'/regression.php';
$realDatabase=new LhpDatabase();
$realModel=new Report_lhp_slitting_model($realDatabase);
$start=microtime(true);
$expected=(int)$realDatabase->query('SELECT COUNT(*) AS n FROM dt_spk_aktual_detail_fg d INNER JOIN tr_spk_aktual_fg h
    ON d.id_spk_aktual=h.id_spk_aktual AND d.id_dt_spkaktualdetail=h.id_dt_spkaktualdetail')->row_array()['n'];
$count=$realModel->count_rows($all);
$page=$realModel->get_rows($all,0,25);
check($count===$expected && count($page)===min(25,$count),'Real-data count and page');
$export=$realModel->get_rows($all,0,null);
check(count($export)===$count && $page===array_slice($export,0,25),'Real-data unbounded/page agreement');
$ids=array_column($export,'detail_id');
check(count(array_unique($ids))===count($ids),'No duplicate real detail IDs');
if ($page) {
    $row=$page[0];
    $detail=$realDatabase->query('SELECT * FROM dt_spk_aktual_detail_fg WHERE id=?',array($row['detail_id']))->row_array();
    $header=$realDatabase->query('SELECT * FROM tr_spk_aktual_fg WHERE id_spk_aktual=?',array($detail['id_spk_aktual']))->row_array();
    $mapped=$realModel->format_row($row);
    check($mapped[0]===$header['no_surat_produksi'] && $mapped[7]===$detail['lot_slitting']
        && $mapped[8]==(float)$detail['totalaktual'] && $mapped[9]==(float)$header['bfinishgood'],'Real sample source agreement');
}
echo 'PASS existing data: '.$count.' unique details, page/export/source agreement; SELECT checks '.round((microtime(true)-$start)*1000).' ms'.PHP_EOL;
