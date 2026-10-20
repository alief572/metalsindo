<?php
$escape = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
?>
<link rel="stylesheet" href="<?= base_url('assets/css/adjustmentstock.css') ?>">
<div class="adjustmentstock-wrapper">
<form id="adjustmentstock-form" method="post" data-material="<?= $escape($material['id_category3']) ?>" data-url="<?= $escape(site_url('adjustmentstock')) ?>">
    <input type="hidden" name="id_material" value="<?= $escape($material['id_category3']) ?>">
    <?php if ($this->config->item('csrf_protection')): ?>
    <input type="hidden" name="<?= $escape($this->security->get_csrf_token_name()) ?>" value="<?= $escape($this->security->get_csrf_hash()) ?>">
    <?php endif; ?>
    <header class="adjustmentstock-material">
        <h3><?= $escape($material['nama']) ?></h3>
        <p><?= $escape($material['id_category3']) ?> <span aria-hidden="true">&middot;</span> <?= $escape($material['nm_bentuk']) ?></p>
    </header>
    <div class="adjustmentstock-message" role="status" aria-live="polite"></div>
    <fieldset class="adjustmentstock-section">
    <legend>Jenis dan lokasi stok</legend>
    <p class="adjustmentstock-section-help" id="adj-type-help">Pilih jenis adjustment, lalu tentukan lot material.</p>
    <div class="adjustmentstock-grid">
        <div class="form-group"><label for="adj-type">Jenis adjustment</label>
            <select class="form-control" name="adjustment" id="adj-type" required>
                <option value="">Pilih jenis adjustment</option><option value="PLUS">PLUS — Tambah stok</option><option value="MINUS">MINUS — Kurangi stok</option><option value="MUTASI">MUTASI — Pindah gudang</option>
            </select>
        </div>
        <div class="form-group" data-section="plus"><label for="adj-plus-lot">Pilihan lot PLUS</label>
            <select class="form-control" name="plus_lot" id="adj-plus-lot"><option value="existing">Lot existing</option><option value="new">Lot baru</option></select>
        </div>
        <div class="form-group" data-section="source"><label for="adj-source">Lot material asal</label>
            <select class="form-control" name="id_stock" id="adj-source"><option value="">Pilih lot</option></select>
        </div>
        <div class="form-group" data-section="new"><label for="adj-lotno">Lot number baru</label><input class="form-control" name="lotno" id="adj-lotno" maxlength="50"></div>
        <div class="form-group" data-section="new"><label for="adj-warehouse">Gudang lot baru</label><select class="form-control" name="id_gudang" id="adj-warehouse"><option value="">Pilih gudang</option></select></div>
        <div class="form-group" data-section="new"><label for="adj-length">Length (mm)</label><input type="number" class="form-control" name="length" id="adj-length" step="0.01" min="0" value="0"></div>
        <div class="form-group" data-section="new"><label for="adj-width">Width (mm)</label><input type="number" class="form-control" name="width" id="adj-width" step="0.01" min="0" value="0"></div>
        <div class="form-group" data-section="transfer"><label for="adj-destination">Gudang tujuan</label><select class="form-control" name="id_gudang_baru" id="adj-destination"><option value="">Pilih gudang tujuan</option></select></div>
        <div class="form-group" data-section="transfer"><label for="adj-target">Lot tujuan</label><select class="form-control" name="id_stock_tujuan" id="adj-target"><option value="">Buat lot tujuan baru</option></select><p class="help-block">Qty 0 wajib memilih lot tujuan existing.</p></div>
    </div>
    </fieldset>
    <fieldset class="adjustmentstock-section">
    <legend>Nilai adjustment</legend>
    <p class="adjustmentstock-section-help">Masukkan jumlah perubahan. Qty dan berat dapat disesuaikan secara terpisah.</p>
    <div class="adjustmentstock-grid adjustmentstock-amounts">
        <div class="form-group"><label for="adj-qty">Qty adjustment</label><input type="number" class="form-control" id="adj-qty" name="total_qty" value="0" min="0" max="2147483647" step="1" required aria-describedby="adj-qty-help"><p id="adj-qty-help" class="help-block">Isi Qty 0 untuk adjustment berat saja.</p></div>
        <div class="form-group"><label for="adj-kg">Total berat adjustment (kg)</label><input type="number" class="form-control" id="adj-kg" name="total_jumlah_berat" min="0" max="999999.99" step="0.01" required></div>
        <div class="form-group"><label for="adj-unit">Berat/Qty adjustment (kg)</label><input type="text" class="form-control" id="adj-unit" readonly><p class="help-block">Dihitung dari berat adjustment / Qty. Kosong jika Qty 0.</p></div>
    </div>
    <div class="form-group adjustmentstock-note"><label for="adj-note">Alasan / Keterangan</label><textarea class="form-control" id="adj-note" name="note" rows="2" maxlength="200" required placeholder="Jelaskan alasan koreksi atau pemindahan stok"></textarea></div>
    </fieldset>
    <section class="adjustmentstock-summary" aria-label="Perbandingan saldo stok" aria-live="polite">
        <h4>Ringkasan saldo</h4>
        <p data-balance-hint>Pilih jenis adjustment dan lot untuk membandingkan saldo.</p>
        <div class="adjustmentstock-summary-scroll" role="region" aria-label="Tabel saldo sebelum dan sesudah adjustment" tabindex="0">
        <table class="adjustmentstock-balance-table">
            <thead><tr><th scope="col">Saldo stok</th><th scope="col">Qty</th><th scope="col">Berat total <span>(kg)</span></th><th scope="col">Berat operasional <span>(kg)</span></th></tr></thead>
            <tbody>
                <tr data-balance-row="source"><th scope="row">Saldo awal <small data-location="source"></small></th><td data-value="source-qty">—</td><td data-value="source-gross">—</td><td data-value="source-operational">—</td></tr>
                <tr data-balance-row="after"><th scope="row">Saldo setelah adjustment</th><td data-value="after-qty">—</td><td data-value="after-gross">—</td><td data-value="after-operational">—</td></tr>
                <tr data-balance-row="target" hidden><th scope="row">Saldo tujuan <small data-location="target"></small></th><td data-value="target-qty">—</td><td data-value="target-gross">—</td><td data-value="target-operational">—</td></tr>
            </tbody>
        </table>
        </div>
        <p class="adjustmentstock-preview-warning" role="status" hidden></p>
    </section>
    <footer class="adjustmentstock-actions">
        <p>Periksa saldo akhir sebelum menyimpan.</p>
        <div><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-success" disabled>Simpan adjustment</button></div>
    </footer>
</form>
</div>
<script src="<?= base_url('assets/js/adjustmentstock.js') ?>"></script>
<script>window.AdjustmentStock.init(jQuery, document.getElementById('adjustmentstock-form'));</script>
