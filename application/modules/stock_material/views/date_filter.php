<?php
defined('BASEPATH') or exit('No direct script access allowed');
$date_today=new DateTime('now',new DateTimeZone('Asia/Jakarta'));
$date_today=$date_today->format('Y-m-d');
$date_prefix=htmlspecialchars($date_prefix,ENT_QUOTES,'UTF-8');
?>
<div class="stock-form-group stock-date-group">
    <label class="stock-label" for="<?= $date_prefix ?>-stock-date">Tanggal Stok</label>
    <input class="stock-input" type="date" id="<?= $date_prefix ?>-stock-date" value="<?= $date_today ?>" max="<?= $date_today ?>" required>
</div>
<p class="stock-date-error" id="<?= $date_prefix ?>-date-error" role="alert" hidden></p>
