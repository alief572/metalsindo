<?php
/*
 * Detail Roll (B2000001) — view murni, tidak ada form.
 * Kolom "Maker" dibaca dari negara (brand/maker), bukan dari maker (nama supplier lama).
 * Material Ekuivalen: ditampilkan read-only.
 */
$r   = $results['row'];
$dim = $results['dim'];
$num = function ($x) {
	return $x === '' || $x === null ? '-' : number_format((float) $x, 0, '.', ',');
};
?>
<div class="box box-solid">
	<div class="box-body">
		<div class="row">
			<div class="col-md-6">
				<dl class="dl-horizontal">
					<dt>Id Material</dt>
					<dd><?= html_escape($r->id_category3) ?></dd>
					<dt>Type Inventory</dt>
					<dd><?= html_escape($r->nama_type) ?></dd>
					<dt>Ferous / Non Ferous</dt>
					<dd><?= html_escape($r->nama_category1) ?></dd>
					<dt>Type Alloy</dt>
					<dd><?= html_escape($r->nama_category2) ?></dd>
					<dt>Spesifikasi</dt>
					<dd><?= html_escape($r->spek) ?></dd>
					<dt>Maker</dt>
					<dd><?= html_escape($r->negara !== '' && $r->negara !== null ? $r->negara : '-') ?></dd>
					<dt>Density</dt>
					<dd><?= html_escape($r->density) ?></dd>
					<dt>Hardness</dt>
					<dd><?= html_escape($r->hardness !== '' && $r->hardness !== null ? $r->hardness : '-') ?></dd>
					<dt>Nama Material</dt>
					<dd><?= html_escape($r->nama) ?></dd>
					<dt>Komposisi</dt>
					<dd>
						<?php if ($results['komposisi']) : ?>
							<?php foreach ($results['komposisi'] as $k) : ?>
								<span class="label label-default" style="margin:1px 2px;display:inline-block"><?= html_escape($k->name_compotition) ?>: <?= html_escape($k->nilai_compotition !== '' && $k->nilai_compotition !== null ? $k->nilai_compotition : '-') ?></span>
							<?php endforeach; ?>
						<?php else : ?>
							-
						<?php endif; ?>
					</dd>
				</dl>
			</div>
			<div class="col-md-6">
				<dl class="dl-horizontal">
					<dt>Bentuk Material</dt>
					<dd>ROLL</dd>
					<dt>Surface</dt>
					<dd><?= html_escape(isset($results['surface']) ? $results['surface'] : '-') ?></dd>
					<?php foreach ($dim as $k => $v) : ?>
						<dt><?= html_escape($k) ?></dt>
						<dd><?= html_escape($v !== '' && $v !== null ? $v : '-') ?></dd>
					<?php endforeach; ?>
					<dt>Monthly Forecast</dt>
					<dd><?= $num($r->mountly_forecast) ?></dd>
					<dt>Safety Stock</dt>
					<dd><?= $num($r->safety_stock) ?></dd>
					<dt>Order Point</dt>
					<dd><?= $num($r->order_point) ?></dd>
					<dt>Maximum Stock</dt>
					<dd><?= $num($r->maksimum) ?></dd>
					<dt>Status</dt>
					<dd><?= $r->aktif === 'aktif' ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Non Aktif</span>' ?></dd>
				</dl>
			</div>
		</div>

		<h4 class="page-header">Supplier</h4>
		<?php if ($results['sup']) : ?>
			<table class="table table-bordered table-condensed">
				<thead>
					<tr>
						<th>Nama Supplier</th>
						<th>Lead Time (hari)</th>
						<th>Minimum Order</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($results['sup'] as $s) : ?>
						<tr>
							<td><?= html_escape($s->name_suplier) ?></td>
							<td><?= html_escape($s->lead !== '' && $s->lead !== null ? $s->lead : '-') ?></td>
							<td><?= html_escape($s->minimum !== '' && $s->minimum !== null ? $s->minimum : '-') ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<em>-</em>
		<?php endif; ?>

		<h4 class="page-header">Material Ekuivalen</h4>
		<?= $results['eq_html'] ?>
	</div>
</div>
