<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
$esc = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$wrap = function ($value, $length) use ($esc) {
    return nl2br($esc(wordwrap((string) $value, $length, "\n", true)));
};
$number = function ($value, $decimals = 2) {
    return $value === null || $value === '' ? '' : number_format((float) $value, $decimals);
};
$date = function ($value, $format) {
    $value = substr((string) $value, 0, 10);
    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value) || substr($value, 0, 4) === '0000') return '';
    $parsed = DateTime::createFromFormat('!Y-m-d', $value, new DateTimeZone('Asia/Jakarta'));
    return $parsed && $parsed->format('Y-m-d') === $value ? $parsed->format($format) : '';
};
?>
<style type="text/css">
<?= file_get_contents(FCPATH . 'assets/css/retur_pmb_cn_print.css') ?>
</style>
<page class="sj-print">
    <table class="sj-company" cellspacing="0">
        <tr>
            <td style="width: 9%;"><img src="<?= $esc(FCPATH . 'assets/images/logo_metalsindo.jpeg') ?>" width="60" height="30" alt="MP"></td>
            <td style="width: 80%;"><b>PT METALSINDO PACIFIC</b></td>
            <td style="width: 11%; text-align: right;"><img src="<?= $esc(FCPATH . 'assets/img/logo_iso.jpg') ?>" width="60" height="30" alt="ISO"></td>
        </tr>
    </table>
    <table class="sj-title" cellspacing="0"><tr><td style="width: 100%;">SURAT JALAN</td></tr></table>
    <p class="sj-number"><b>NO : <?= $esc($header->no_surat) ?></b></p>
    <table class="sj-address" cellspacing="0">
        <tr>
            <td class="sj-sender" style="width: 50%;">
                <b>Address<br>
                Jl. Jababeka XIV, Blok J no. 10 H<br>
                Cikarang Industrial Estate, Bekasi 17530<br>
                Phone: (62-21) 89831726734<br>
                Fax: (62-21) 89831866<br>
                NPWP: 21.098.204.7-414.000</b>
            </td>
            <td style="width: 50%;"><b>To :</b><br>
                <table class="sj-recipient" cellspacing="0"><tr><td style="width: 100%;"><b><?= $esc(isset($retur_header->nm_supplier) ? $retur_header->nm_supplier : '') ?><br>
                    Address :<br><?= nl2br($esc(isset($supplier->address_office) ? $supplier->address_office : '')) ?></b></td></tr></table>
                <b>REFF :
                <?php foreach ($po as $i => $item_po) : ?>
                    <?= $i > 0 ? '<br>' : '' ?><u><?= $esc($item_po->no_surat) ?></u><?= $date($item_po->tanggal, 'd-M-Y') !== '' ? ' / ' . $date($item_po->tanggal, 'd-M-Y') : '' ?>
                <?php endforeach; ?>
                </b>
            </td>
        </tr>
    </table>
    <p class="sj-instruction"><b>Harap barang-barang tersebut dibawah ini supaya diterima dengan baik sesuai dengan surat pesanan.</b></p>
    <table class="sj-detail" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 12%;">Tgl Incoming</th>
                <th style="width: 17%;">Lot Number</th>
                <th style="width: 27%;">Nama Material</th>
                <th style="width: 8%;">Width</th>
                <th style="width: 12%;">Qty Order</th>
                <th style="width: 12%;">Qty Rec</th>
                <th style="width: 12%;">Qty Retur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detail as $item) : ?>
                <tr>
                    <td class="sj-center"><?= $date($item->print_date, 'd/m/Y') ?></td>
                    <td class="sj-center"><?= $wrap(isset($item->lotno) ? $item->lotno : '', 16) ?></td>
                    <td><b><?= $wrap(isset($item->nama_material) ? $item->nama_material : '', 36) ?></b></td>
                    <td class="sj-right"><?= $number(isset($item->width) ? $item->width : null) ?></td>
                    <td class="sj-right"><?= $number(isset($item->qty_order) ? $item->qty_order : null) ?></td>
                    <td class="sj-right"><?= $number($item->print_receive, $item->print_unit === 'Sheet' ? 0 : 2) ?> <?= $item->print_receive !== null ? $item->print_unit : '' ?></td>
                    <td class="sj-right"><b><?= $number($item->print_retur, $item->print_unit === 'Sheet' ? 0 : 2) ?> <?= $item->print_retur !== null ? $item->print_unit : '' ?></b></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($detail)) : ?>
                <tr><td colspan="7">Belum ada rincian material untuk transaksi retur ini.</td></tr>
            <?php endif; ?>
            <tr class="sj-totals">
                <td colspan="5" class="sj-right"><b>Total Qty</b></td>
                <td class="sj-right"><b><?= $number($total_receive) ?></b></td>
                <td class="sj-right"><b><?= $number($total_retur) ?></b></td>
            </tr>
        </tbody>
    </table>
    <nobreak>
        <br><br>
        <table class="sj-signatures" cellspacing="0">
            <tr>
                <td style="width: 33%;"></td><td style="width: 33%;"></td>
                <td style="width: 34%;"><b>Cikarang,<?= $date($retur_header->tgl_retur, 'd-m-Y') ?></b></td>
            </tr>
            <tr>
                <td><b>YANG MENERIMA<br><i>(RECEIVED BY)</i></b></td>
                <td><b>DIPERIKSA<br><i>(CHECKED BY)</i></b></td>
                <td><b>HORMAT KAMI<br><i>(YOURS FAITHFULLY)</i></b></td>
            </tr>
            <tr><td class="sj-sign-space"></td><td></td><td></td></tr>
            <tr>
                <td><b>STEMPEL/NAMA JELAS</b></td><td><b>POS JAGA</b></td><td><b>GUDANG JADI</b></td>
            </tr>
        </table>
    </nobreak>
</page>
