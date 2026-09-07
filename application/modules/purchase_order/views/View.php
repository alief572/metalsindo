<?php
$tanggal = date('Y-m-d');
$head = isset($results['head'][0]) ? $results['head'][0] : (object) array();
$detail = isset($results['detail']) ? $results['detail'] : array();
$comp = isset($results['comp']) ? $results['comp'] : array();
$tipe_sheet = isset($results['tipe_sheet']) ? $results['tipe_sheet'] : 0;

$curr = !empty($head->matauang) ? strtoupper($head->matauang) : 'IDR';
?>

<style type="text/css">
	.po-view-wrapper {
		background: #ffffff;
		padding: 24px;
		color: #1e293b;
		font-family: inherit;
	}
	.po-view-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding-bottom: 20px;
		border-bottom: 2px solid #e2e8f0;
		margin-bottom: 20px;
	}
	.po-view-title h3 {
		margin: 0;
		font-size: 20px;
		font-weight: 700;
		color: #205072;
	}
	.po-view-title span {
		font-size: 13px;
		color: #64748b;
	}
	.po-info-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
		gap: 16px;
		margin-bottom: 20px;
	}
	.po-info-box {
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px;
	}
	.po-info-box h4 {
		margin: 0 0 12px 0;
		font-size: 13px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		color: #205072;
		border-bottom: 1px solid #cbd5e1;
		padding-bottom: 6px;
	}
	.po-info-item {
		display: flex;
		justify-content: space-between;
		font-size: 12px;
		margin-bottom: 8px;
		line-height: 1.4;
	}
	.po-info-item:last-child {
		margin-bottom: 0;
	}
	.po-info-label {
		color: #64748b;
		font-weight: 500;
		width: 40%;
	}
	.po-info-val {
		color: #0f172a;
		font-weight: 600;
		width: 60%;
		text-align: right;
		word-break: break-word;
	}
	.po-table-wrapper {
		margin-top: 20px;
		margin-bottom: 20px;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		overflow: hidden;
	}
	.po-table-wrapper table {
		margin-bottom: 0 !important;
	}
	.po-table-wrapper thead th {
		background: #205072 !important;
		color: #ffffff !important;
		font-size: 11px !important;
		font-weight: 600 !important;
		text-transform: uppercase !important;
		padding: 10px 8px !important;
		border: none !important;
		vertical-align: middle !important;
	}
	.po-table-wrapper tbody td {
		padding: 9px 8px !important;
		font-size: 12px !important;
		vertical-align: middle !important;
		border-color: #f1f5f9 !important;
	}
	.po-summary-section {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 20px;
		margin-top: 15px;
		flex-wrap: wrap;
	}
	.po-notes-card {
		flex: 1;
		min-width: 280px;
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px;
	}
	.po-notes-card h5 {
		margin: 0 0 8px 0;
		font-size: 12px;
		font-weight: 700;
		color: #334155;
		text-transform: uppercase;
	}
	.po-notes-card p {
		margin: 0;
		font-size: 13px;
		color: #475569;
		font-style: italic;
	}
	.po-totals-card {
		width: 340px;
		background: #ffffff;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		padding: 16px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.03);
	}
	.po-total-row {
		display: flex;
		justify-content: space-between;
		font-size: 13px;
		padding: 6px 0;
		color: #475569;
	}
	.po-total-row.grand-total {
		border-top: 2px dashed #cbd5e1;
		margin-top: 8px;
		padding-top: 12px;
		font-size: 15px;
		font-weight: 700;
		color: #205072;
	}
	.po-view-footer {
		display: flex;
		justify-content: flex-end;
		gap: 10px;
		padding-top: 20px;
		border-top: 1px solid #e2e8f0;
		margin-top: 25px;
	}
	.kurs-badge {
		background: #eff6ff;
		border: 1px solid #bfdbfe;
		border-radius: 6px;
		padding: 10px 14px;
		text-align: center;
		flex: 1;
	}
	.kurs-badge span {
		font-size: 11px;
		color: #1e40af;
		display: block;
		font-weight: 600;
		text-transform: uppercase;
	}
	.kurs-badge strong {
		font-size: 14px;
		color: #1e3a8a;
	}
</style>

<div class="po-view-wrapper">
	<!-- 1. Header Ringkasan & Tombol Cetak -->
	<div class="po-view-header">
		<div class="po-view-title">
			<h3><?= !empty($head->no_surat) ? $head->no_surat : $head->no_po ?></h3>
			<span>Kode Internal: <strong><?= $head->no_po ?></strong> &bull; Tgl: <?= date('d M Y', strtotime($head->tanggal)) ?></span>
		</div>
		<div>
			<?php
			if ($head->status == '1') {
				echo '<span class="label-status label-waiting" style="font-size: 12px; padding: 6px 14px;"><i class="fa fa-clock-o"></i> Menunggu Approval</span>';
			} elseif ($head->status == '2') {
				echo '<span class="label-status label-approved" style="font-size: 12px; padding: 6px 14px;"><i class="fa fa-check-circle"></i> Approved</span>';
			} else {
				echo '<span class="label-status label-closed" style="font-size: 12px; padding: 6px 14px;"><i class="fa fa-archive"></i> Closed</span>';
			}
			?>
		</div>
	</div>

	<!-- 2. Grid Informasi Utama (3 Kolom) -->
	<div class="po-info-grid">
		<!-- Box 1: Supplier Info -->
		<div class="po-info-box">
			<h4><i class="fa fa-building-o" style="margin-right: 6px;"></i>Data Supplier</h4>
			<div class="po-info-item">
				<span class="po-info-label">Supplier</span>
				<span class="po-info-val" style="color: #205072;"><?= strtoupper($head->suplier) ?></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">Kategori</span>
				<span class="po-info-val"><?= !empty($head->loi) ? $head->loi : '-' ?></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">Mata Uang</span>
				<span class="po-info-val"><strong><?= $curr ?></strong></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">Metode Harga</span>
				<span class="po-info-val"><?= !empty($head->cif) ? $head->cif : '-' ?></span>
			</div>
		</div>

		<!-- Box 2: Dokumen & Referensi -->
		<div class="po-info-box">
			<h4><i class="fa fa-file-text-o" style="margin-right: 6px;"></i>Referensi Dokumen</h4>
			<div class="po-info-item">
				<span class="po-info-label">Nomor PO</span>
				<span class="po-info-val"><?= $head->no_surat ?></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">Tanggal PO</span>
				<span class="po-info-val"><?= date('d-M-Y', strtotime($head->tanggal)) ?></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">Nomor PR</span>
				<span class="po-info-val" style="color: #0284c7;"><?= !empty($head->no_pr) ? $head->no_pr : '-' ?></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">Term Pembayaran</span>
				<span class="po-info-val"><?= !empty($head->term) ? $head->term : '-' ?></span>
			</div>
		</div>

		<!-- Box 3: Pengiriman & Logistik -->
		<div class="po-info-box">
			<h4><i class="fa fa-truck" style="margin-right: 6px;"></i>Pengiriman & Logistik</h4>
			<div class="po-info-item">
				<span class="po-info-label">Expect Date</span>
				<span class="po-info-val"><?= !empty($head->expect_tanggal) ? date('d-M-Y', strtotime($head->expect_tanggal)) : '-' ?></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">Delivery Date</span>
				<span class="po-info-val"><?= !empty($head->delivery_date) ? date('d-M-Y', strtotime($head->delivery_date)) : '-' ?></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">PIC Penerima</span>
				<span class="po-info-val"><?= !empty($head->receiving_person) ? $head->receiving_person : '-' ?></span>
			</div>
			<div class="po-info-item">
				<span class="po-info-label">Tipe Material</span>
				<span class="po-info-val"><?= ($tipe_sheet == 1) ? '<span class="badge bg-purple">Sheet</span>' : '<span class="badge bg-blue">Coil / Slitting</span>' ?></span>
			</div>
		</div>
	</div>

	<!-- 3. Nilai Kurs (Jika Import) -->
	<?php if (!empty($head->loi) && strtolower($head->loi) == 'import'): ?>
		<?php
		$hariini = date('Y-m-d');
		$sepuluh_hari = mktime(0, 0, 0, date('n'), date('j') - 10, date('Y'));
		$tendays = date("Y-m-d", $sepuluh_hari);
		$blnnow = date('m');
		$yearnow = date('Y');
		if ($blnnow != '1') {
			$blnkmrn = $blnnow - 1;
			$yearkemaren = $yearnow;
		} else {
			$blnkmrn = "12";
			$yearkemaren = $yearnow - 1;
		}
		$kurs = $this->db->query("SELECT * FROM mata_uang WHERE kode = 'IDR'")->row();
		$kurs10hari = $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE tanggal_ubah BETWEEN '$tendays' AND '$hariini' AND kode_kurs='IDR'")->row();
		$kurs30hari = $this->db->query("SELECT AVG(nominal) as nominal FROM perubahan_kurs WHERE MONTH(tanggal_ubah) = '$blnkmrn' AND YEAR(tanggal_ubah) = '$yearkemaren' AND kode_kurs='IDR'")->row();

		$nomkurs = isset($kurs->kurs) ? $kurs->kurs : 0;
		$nomkurs10 = isset($kurs10hari->nominal) ? $kurs10hari->nominal : 0;
		$nomkurs30 = isset($kurs30hari->nominal) ? $kurs30hari->nominal : 0;
		?>
		<div style="display: flex; gap: 12px; margin-bottom: 20px;">
			<div class="kurs-badge">
				<span>Kurs On The Spot</span>
				<strong>Rp <?= number_format($nomkurs, 2) ?></strong>
			</div>
			<div class="kurs-badge">
				<span>Kurs Rata-Rata 10 Hari</span>
				<strong>Rp <?= number_format($nomkurs10, 2) ?></strong>
			</div>
			<div class="kurs-badge">
				<span>Kurs Rata-Rata 30 Hari</span>
				<strong>Rp <?= number_format($nomkurs30, 2) ?></strong>
			</div>
		</div>
	<?php endif; ?>

	<!-- 4. Tabel Detail Material PO -->
	<div class="po-table-wrapper">
		<table class="table table-striped table-hover" width="100%">
			<thead>
				<tr>
					<th width="35" class="text-center">#</th>
					<th>Item Material</th>
					<th>Deskripsi</th>
					<?php if ($tipe_sheet == 1): ?>
						<th class="text-right">Width (mm)</th>
						<th class="text-right">Length (mm)</th>
						<th class="text-right">Qty (Sheet)</th>
						<th class="text-right">Total Berat (Kg)</th>
					<?php else: ?>
						<th class="text-right">Width (mm)</th>
						<th class="text-right">Total Length</th>
						<th class="text-right">Total Weight (Kg)</th>
					<?php endif; ?>
					<th class="text-right">Harga Satuan</th>
					<th class="text-center" width="70">Diskon %</th>
					<th class="text-center" width="70">Pajak %</th>
					<th class="text-right" width="120">Jumlah Harga</th>
					<th>Catatan</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if (!empty($detail)):
					$no = 0;
					foreach ($detail as $item):
						$no++;
				?>
						<tr>
							<td class="text-center"><?= $no ?></td>
							<td><strong><?= $item->nama ?></strong></td>
							<td><?= !empty($item->description) ? $item->description : '-' ?></td>
							<?php if ($tipe_sheet == 1): ?>
								<td class="text-right"><?= number_format($item->width, 2) ?></td>
								<td class="text-right"><?= number_format($item->panjang, 2) ?></td>
								<td class="text-right"><?= number_format($item->qty, 0) ?></td>
								<td class="text-right"><?= number_format($item->totalwidth, 2) ?></td>
							<?php else: ?>
								<td class="text-right"><?= number_format($item->width, 2) ?></td>
								<td class="text-right"><?= number_format($item->panjang, 2) ?></td>
								<td class="text-right"><?= number_format($item->totalwidth, 2) ?></td>
							<?php endif; ?>
							<td class="text-right"><?= number_format($item->hargasatuan, 2) ?></td>
							<td class="text-center"><?= number_format($item->diskon, 0) ?>%</td>
							<td class="text-center"><?= number_format($item->pajak, 0) ?>%</td>
							<td class="text-right"><strong><?= number_format($item->jumlahharga, 2) ?></strong></td>
							<td><?= !empty($item->note) ? $item->note : '-' ?></td>
						</tr>
					<?php
					endforeach;
				else:
					?>
					<tr>
						<td colspan="11" class="text-center" style="padding: 20px; color: #94a3b8;">Tidak ada data item material pada Purchase Order ini.</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<!-- 5. Catatan & Ringkasan Keuangan -->
	<div class="po-summary-section">
		<div class="po-notes-card">
			<h5><i class="fa fa-sticky-note-o" style="margin-right: 5px;"></i>Catatan PO</h5>
			<p><?= !empty($head->note) ? nl2br(htmlspecialchars($head->note)) : 'Tidak ada catatan khusus untuk Purchase Order ini.' ?></p>
		</div>

		<div class="po-totals-card">
			<div class="po-total-row">
				<span>Sub Total:</span>
				<span><?= $curr ?> <?= number_format($head->hargatotal, 2) ?></span>
			</div>
			<div class="po-total-row">
				<span>Total Diskon:</span>
				<span style="color: #dc2626;">- <?= $curr ?> <?= number_format($head->diskontotal, 2) ?></span>
			</div>
			<div class="po-total-row">
				<span>Total Pajak / PPN:</span>
				<span><?= $curr ?> <?= number_format($head->taxtotal, 2) ?></span>
			</div>
			<div class="po-total-row grand-total">
				<span>Total Order:</span>
				<span><?= $curr ?> <?= number_format($head->subtotal, 2) ?></span>
			</div>
		</div>
	</div>

	<!-- 6. Footer Modal Aksi -->
	<div class="po-view-footer">
		<a class="btn btn-primary" href="<?= base_url('/purchase_order/PrintH2/' . $head->no_po) ?>" target="_blank" style="border-radius: 4px; font-weight: 600;">
			<i class="fa fa-print"></i>&nbsp; Cetak PO
		</a>
		<button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px;">
			<i class="fa fa-times"></i>&nbsp; Tutup
		</button>
	</div>
</div>